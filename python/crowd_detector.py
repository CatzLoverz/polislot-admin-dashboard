import cv2
import numpy as np
import json
import os
import argparse
from ultralytics import YOLO

# Global variables for interactive polygon drawing
polygon_points = []
selection_mode = True
dragging_point_idx = -1  # To allow dragging points if needed, but simple click & undo is usually best

def save_config(filepath, points, threshold):
    """Saves polygon points and detection threshold to a JSON file."""
    config = {
        "points": points,
        "threshold": threshold
    }
    try:
        with open(filepath, 'w') as f:
            json.dump(config, f, indent=4)
        print(f"[INFO] Configuration saved to {filepath}")
    except Exception as e:
        print(f"[ERROR] Failed to save configuration: {e}")

def load_config(filepath):
    """Loads polygon points and threshold from a JSON file."""
    if os.path.exists(filepath):
        try:
            with open(filepath, 'r') as f:
                config = json.load(f)
                points = [tuple(p) for p in config.get("points", [])]
                threshold = config.get("threshold", 5)
                print(f"[INFO] Configuration loaded from {filepath}")
                return points, threshold
        except Exception as e:
            print(f"[ERROR] Failed to load configuration: {e}")
    return [], 5

def mouse_callback(event, x, y, flags, param):
    """Callback function for mouse events to handle polygon drawing."""
    global polygon_points, selection_mode
    
    if not selection_mode:
        return
    
    # Left click: add point
    if event == cv2.EVENT_LBUTTONDOWN:
        polygon_points.append((x, y))
        print(f"[MOUSE] Added point: ({x}, {y})")
    
    # Right click: undo last point
    elif event == cv2.EVENT_RBUTTONDOWN:
        if polygon_points:
            removed = polygon_points.pop()
            print(f"[MOUSE] Removed point: {removed}")

def is_inside_polygon(point, polygon):
    """
    Checks if a point is inside the given polygon.
    Uses cv2.pointPolygonTest.
    """
    if len(polygon) < 3:
        return False
    # cv2.pointPolygonTest requires float32 numpy array
    polygon_np = np.array(polygon, dtype=np.int32)
    dist = cv2.pointPolygonTest(polygon_np, (float(point[0]), float(point[1])), False)
    return dist >= 0

def draw_hud(frame, total_detected, inside_zone, threshold, mode):
    """
    Draws a sleek, semi-transparent HUD panel with real-time statistics
    and custom color status indicators.
    """
    h, w, _ = frame.shape
    
    # HUD Panel dimensions (top-left aligned)
    hud_w, hud_h = 360, 210
    hud_x, hud_y = 20, 20
    
    # Draw translucent glassmorphism background
    hud_overlay = frame.copy()
    cv2.rectangle(hud_overlay, (hud_x, hud_y), (hud_x + hud_w, hud_y + hud_h), (20, 20, 20), -1)
    
    # Determine Status Color (Green for normal/safe, Red/Orange for crowded)
    is_crowded = inside_zone >= threshold
    status_color = (0, 0, 255) if is_crowded else (0, 255, 0) # BGR (Red or Green)
    status_text = "OVERCROWDED" if is_crowded else "NORMAL"
    
    # Draw Status indicator bar
    cv2.rectangle(hud_overlay, (hud_x, hud_y), (hud_x + hud_w, hud_y + 12), status_color, -1)
    
    # Apply overlay with transparency
    cv2.addWeighted(hud_overlay, 0.85, frame, 0.15, 0, frame)
    
    # HUD Border
    cv2.rectangle(frame, (hud_x, hud_y), (hud_x + hud_w, hud_y + hud_h), (80, 80, 80), 1, cv2.LINE_AA)
    
    # Text offsets
    font = cv2.FONT_HERSHEY_DUPLEX
    font_scale = 0.55
    text_color = (255, 255, 255)
    shadow_color = (0, 0, 0)
    
    def draw_text_with_shadow(img, text, pos, scale, color, thickness=1):
        x, y = pos
        cv2.putText(img, text, (x + 1, y + 1), font, scale, shadow_color, thickness, cv2.LINE_AA)
        cv2.putText(img, text, (x, y), font, scale, color, thickness, cv2.LINE_AA)

    # 1. Title
    draw_text_with_shadow(frame, "CROWD DETECTION MONITOR", (hud_x + 15, hud_y + 35), 0.6, (0, 220, 255), 1)
    
    # Separator
    cv2.line(frame, (hud_x + 15, hud_y + 45), (hud_x + hud_w - 15, hud_y + 45), (60, 60, 60), 1, cv2.LINE_AA)
    
    # 2. Mode State
    mode_str = "SELECTION MODE" if mode else "DETECTION ACTIVE"
    mode_color = (0, 255, 255) if mode else (0, 255, 0)
    draw_text_with_shadow(frame, f"System State: ", (hud_x + 15, hud_y + 70), font_scale, text_color)
    draw_text_with_shadow(frame, mode_str, (hud_x + 150, hud_y + 70), font_scale, mode_color, 1)
    
    # 3. Stats details
    draw_text_with_shadow(frame, f"Total Detected: {total_detected}", (hud_x + 15, hud_y + 100), font_scale, text_color)
    draw_text_with_shadow(frame, f"Inside Region: {inside_zone}", (hud_x + 15, hud_y + 125), font_scale, text_color)
    draw_text_with_shadow(frame, f"Limit Threshold: {threshold}", (hud_x + 15, hud_y + 150), font_scale, text_color)
    
    # 4. Status indicator text
    draw_text_with_shadow(frame, f"Status: ", (hud_x + 15, hud_y + 180), font_scale, text_color)
    draw_text_with_shadow(frame, status_text, (hud_x + 80, hud_y + 180), 0.65, status_color, 2)
    
    # Help instructions drawn at the bottom center of the frame
    bottom_overlay = frame.copy()
    cv2.rectangle(bottom_overlay, (0, h - 45), (w, h), (15, 15, 15), -1)
    cv2.addWeighted(bottom_overlay, 0.7, frame, 0.3, 0, frame)
    
    if mode:
        instructions = "Selection: [Left-Click] Add Point | [Right-Click] Undo | [c] Clear | [s] Save & Start"
    else:
        instructions = "Control: [r] Redraw Zone | [+/-] Threshold | [q] Quit Detection"
    
    cv2.putText(frame, instructions, (20, h - 18), cv2.FONT_HERSHEY_SIMPLEX, 0.45, (200, 200, 200), 1, cv2.LINE_AA)

def main():
    global polygon_points, selection_mode
    
    # Parse CLI Arguments
    parser = argparse.ArgumentParser(description="YOLOv8 Crowd Detection with Interactive Polygon Regions")
    parser.add_argument("--source", type=str, default="0", help="Video source: '0' for webcam, or path to video file")
    parser.add_argument("--weights", type=str, default="yolov8n.pt", help="YOLO model weight file")
    parser.add_argument("--threshold", type=int, default=5, help="Crowd threshold to trigger alert")
    parser.add_argument("--config", type=str, default="polygon_config.json", help="Path to save/load polygon coordinates")
    parser.add_argument("--confidence", type=float, default=0.4, help="YOLO confidence threshold")
    args = parser.parse_args()
    
    # Load configuration if it exists
    loaded_points, loaded_threshold = load_config(args.config)
    if loaded_points:
        polygon_points = loaded_points
        selection_mode = False  # Skip selection mode since we have a saved polygon
        print("[INFO] Saved polygon found. Starting directly in DETECTION mode.")
        print("[INFO] Press 'r' at any time to re-draw the polygon.")
    else:
        selection_mode = True
        print("[INFO] No valid saved polygon found. Starting in SELECTION mode.")
        print("[INFO] Define the region on the screen by left-clicking to add points.")

    threshold = loaded_threshold if loaded_threshold != 5 else args.threshold
    
    # Initialize YOLO Model
    print(f"[INFO] Loading YOLO weights from '{args.weights}'...")
    model = YOLO(args.weights)
    print("[INFO] Model loaded successfully.")
    
    # Open Video Capture
    source = args.source
    if source.isdigit():
        source = int(source)
    cap = cv2.VideoCapture(source)
    
    if not cap.isOpened():
        print(f"[ERROR] Could not open video source: {args.source}")
        return
        
    # Setup window and mouse handler
    window_name = "YOLOv8 Crowd Detector"
    cv2.namedWindow(window_name, cv2.WINDOW_NORMAL)
    cv2.setMouseCallback(window_name, mouse_callback)
    
    print("[INFO] Interface controls initialized.")
    
    while True:
        ret, frame = cap.read()
        if not ret:
            # If video ends, loop it or break
            if isinstance(source, int):
                # Webcam disconnect or error
                print("[ERROR] Failed to grab frame from webcam.")
                break
            else:
                # Video file finished, reset to beginning
                cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                continue
        
        # Dimensions check
        h, w, _ = frame.shape
        
        # Counts tracker
        total_people = 0
        people_in_zone = 0
        detections = []
        
        # Run YOLO detection only when NOT in selection mode
        if not selection_mode and len(polygon_points) >= 3:
            results = model.predict(frame, conf=args.confidence, classes=[0], verbose=False) # class 0 is 'person'
            
            if results and len(results) > 0:
                boxes = results[0].boxes
                for box in boxes:
                    total_people += 1
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    conf = float(box.conf[0])
                    
                    # Representation point: bottom-center of the bbox (the feet/standing point)
                    bottom_center = (int((x1 + x2) / 2), y2)
                    
                    inside = is_inside_polygon(bottom_center, polygon_points)
                    if inside:
                        people_in_zone += 1
                    
                    detections.append({
                        "bbox": (x1, y1, x2, y2),
                        "inside": inside,
                        "point": bottom_center,
                        "conf": conf
                    })
        
        # Visual Rendering: 1. Draw Bounding Boxes
        for det in detections:
            x1, y1, x2, y2 = det["bbox"]
            inside = det["inside"]
            conf = det["conf"]
            
            # Colors: Orange/Red if inside and crowded, Yellow if inside but not crowded, Grey if outside
            is_crowded = people_in_zone >= threshold
            if inside:
                color = (0, 0, 255) if is_crowded else (0, 165, 255)  # Red / Orange
                thickness = 2
            else:
                color = (180, 180, 180)  # Muted Grey for people outside the zone
                thickness = 1
                
            # Draw bbox
            cv2.rectangle(frame, (x1, y1), (x2, y2), color, thickness, cv2.LINE_AA)
            
            # Draw representation point (feet indicator dot)
            cv2.circle(frame, det["point"], 5, color, -1)
            
            # Label
            label = f"Person {conf:.2f}"
            cv2.putText(frame, label, (x1, max(15, y1 - 5)), cv2.FONT_HERSHEY_SIMPLEX, 0.4, color, 1, cv2.LINE_AA)
            
        # Visual Rendering: 2. Draw Polygon Zone
        if len(polygon_points) > 0:
            polygon_np = np.array(polygon_points, dtype=np.int32)
            
            if selection_mode:
                # In selection mode, draw outline in yellow and vertices
                cv2.polylines(frame, [polygon_np], False, (0, 255, 255), 2, cv2.LINE_AA)
                for pt in polygon_points:
                    cv2.circle(frame, pt, 5, (0, 0, 255), -1)
            else:
                # In detection mode, draw semi-transparent filled polygon
                is_crowded = people_in_zone >= threshold
                poly_color = (0, 0, 220) if is_crowded else (0, 180, 0) # Dark red / Dark green
                
                overlay = frame.copy()
                cv2.fillPoly(overlay, [polygon_np], poly_color)
                cv2.addWeighted(overlay, 0.25, frame, 0.75, 0, frame)
                
                # Draw outer polygon boundary line
                border_color = (0, 0, 255) if is_crowded else (0, 255, 0)
                cv2.polylines(frame, [polygon_np], True, border_color, 2, cv2.LINE_AA)
                
        # Visual Rendering: 3. Draw HUD
        draw_hud(frame, total_detected=total_people, inside_zone=people_in_zone, threshold=threshold, mode=selection_mode)
        
        # Display Frame
        cv2.imshow(window_name, frame)
        
        # Key handlers
        key = cv2.waitKey(1) & 0xFF
        
        # 1. Quit script
        if key == ord('q') or key == 27: # 'q' or ESC
            print("[INFO] Quitting crowd detector...")
            break
            
        # 2. Save polygon points & start detection
        elif key == ord('s') and selection_mode:
            if len(polygon_points) >= 3:
                selection_mode = False
                save_config(args.config, polygon_points, threshold)
                print("[INFO] Polygon saved. Detection mode activated.")
            else:
                print("[WARNING] Please define at least 3 points before saving.")
                
        # 3. Clear points
        elif key == ord('c') and selection_mode:
            polygon_points = []
            print("[INFO] Polygon points cleared.")
            
        # 4. Re-draw polygon (back to selection mode)
        elif key == ord('r') and not selection_mode:
            selection_mode = True
            print("[INFO] Redrawing polygon. Selection mode activated.")
            
        # 5. Increase threshold
        elif key == ord('+') or key == ord('='):
            threshold += 1
            print(f"[INFO] Limit threshold increased to {threshold}")
            if not selection_mode:
                save_config(args.config, polygon_points, threshold)
                
        # 6. Decrease threshold
        elif key == ord('-') or key == ord('_'):
            if threshold > 1:
                threshold -= 1
                print(f"[INFO] Limit threshold decreased to {threshold}")
                if not selection_mode:
                    save_config(args.config, polygon_points, threshold)
            else:
                print("[WARNING] Threshold cannot be less than 1.")
                
    # Cleanup
    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    main()
