import paho.mqtt.client as mqtt
import json
import base64
import cv2
import numpy as np
import time
import hmac
import hashlib
from Crypto.Cipher import AES
from Crypto.Util.Padding import pad
import os
import sys
import threading
import argparse
from ultralytics import YOLO

# ============================================================
# PARSE ARGUMENTS
# ============================================================
parser = argparse.ArgumentParser(description="PoliSlot Headless MQTT Parking Detector")
parser.add_argument("mac_address", type=str, nargs="?", help="MAC Address of the device (e.g. 00:1A:2B:3C:4D:5E)")
parser.add_argument("--source", type=str, default="0", help="Video source: '0' for webcam, or RTSP/file path")
parser.add_argument("--weights", type=str, default="yolov8n.pt", help="YOLO model weight file")
parser.add_argument("--confidence", type=float, default=0.4, help="YOLO confidence threshold")
parser.add_argument("--classes", type=str, default="2,3", help="Comma-separated YOLO class filter (2=car, 3=motorcycle)")
parser.add_argument("--broker", type=str, default="mqtt.raihanatmaja.my.id", help="MQTT Broker address")
parser.add_argument("--port", type=int, default=443, help="MQTT Broker port (443 for WSS, 1883 for TCP)")
parser.add_argument("--secret", type=str, default="pOl1sL0t_ioT_s3creT_k3y_2026", help="Shared secret key for HMAC & AES")
args = parser.parse_args()

# Validate MAC Address
MAC_ADDRESS = args.mac_address
if not MAC_ADDRESS:
    MAC_ADDRESS = os.environ.get("MAC_ADDRESS")
if not MAC_ADDRESS:
    MAC_ADDRESS = input("Masukkan MAC Address perangkat (contoh: 00:1A:2B:3C:4D:5E): ").strip()
    if not MAC_ADDRESS:
        print("[-] MAC Address tidak boleh kosong!")
        sys.exit(1)

# Configuration & Connection Setup
BROKER = args.broker
PORT = args.port
SHARED_SECRET = args.secret
TARGET_CLASSES = [int(c.strip()) for c in args.classes.split(",") if c.strip().isdigit()]

TOPIC_COMMAND = f"polislot/device/{MAC_ADDRESS}/command"
TOPIC_SNAPSHOT = f"polislot/device/{MAC_ADDRESS}/snapshot"
TOPIC_STATUS = f"polislot/device/{MAC_ADDRESS}/status"
TOPIC_COUNT = f"polislot/device/{MAC_ADDRESS}/count"
TOPIC_SERVER_STATUS = "polislot/server/status"

# MQTT Authentication (retrieved from env if available)
MQTT_USER = os.environ.get("MQTT_USER", "polislot_user")
MQTT_PASSWORD = os.environ.get("MQTT_PASSWORD", "secure_password")

# In-Memory Settings
config_lock = threading.Lock()
max_slots = 0
detection_polygons = []
threshold_banyak = 30.0
threshold_terbatas = 80.0
current_vehicle_count = 0

# ============================================================
# SECURITY FUNCTIONS (AES & HMAC)
# ============================================================
def get_aes_key():
    return hashlib.sha256(SHARED_SECRET.encode('utf-8')).digest()[:32]

def encrypt_image_aes(image_bytes):
    key = get_aes_key()
    iv = os.urandom(16)
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_data = pad(image_bytes, AES.block_size)
    encrypted_bytes = cipher.encrypt(padded_data)
    return base64.b64encode(iv).decode('utf-8'), base64.b64encode(encrypted_bytes).decode('utf-8')

def generate_hmac_signature(payload_dict):
    key = get_aes_key()
    data_string = json.dumps(payload_dict, separators=(',', ':'))
    return hmac.new(key, data_string.encode('utf-8'), hashlib.sha256).hexdigest()

# ============================================================
# MULTI-POLYGON DETECTOR HELPER
# ============================================================
def is_inside_polygon(point, polygon):
    if len(polygon) < 3:
        return False
    polygon_np = np.array(polygon, dtype=np.int32)
    dist = cv2.pointPolygonTest(polygon_np, (float(point[0]), float(point[1])), False)
    return dist >= 0

def is_inside_any_polygon(point, polygons):
    for poly in polygons:
        if is_inside_polygon(point, poly):
            return True
    return False

# ============================================================
# CAMERA STREAM CLASS (Threaded for Real-Time Performance)
# ============================================================
class CameraStream:
    def __init__(self, source):
        # Handle string integer vs path/RTSP
        src = int(source) if source.isdigit() else source
        self.cap = cv2.VideoCapture(src)
        self.ret = False
        self.frame = None
        self.running = True
        self.lock = threading.Lock()
        self.thread = threading.Thread(target=self._update, daemon=True)
        self.thread.start()
        print(f"[INFO] Camera stream started on source: {source}")

    def _update(self):
        # Warmup camera sensor
        for _ in range(15):
            self.cap.read()
            time.sleep(0.01)

        while self.running:
            ret, frame = self.cap.read()
            if not ret:
                # If source is file, seek to start; otherwise retry
                self.cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                time.sleep(0.1)
                continue
            with self.lock:
                self.ret = ret
                self.frame = frame.copy()
            time.sleep(0.01)

    def read(self):
        with self.lock:
            return self.ret, self.frame

    def capture_jpeg(self):
        ret, frame = self.read()
        if not ret or frame is None:
            # Fallback blank frame
            frame = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(frame, "Camera offline", (180, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
        else:
            # Add timestamp overlay to screenshot
            timestamp_str = time.strftime("%Y-%m-%d %H:%M:%S")
            cv2.putText(frame, timestamp_str, (15, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)

        ret_encode, buffer = cv2.imencode('.jpg', frame)
        if ret_encode:
            return buffer.tobytes()
        return b''

    def stop(self):
        self.running = False
        self.cap.release()

# Global Stream Reference
stream = None

# ============================================================
# MQTT CALLBACKS
# ============================================================
try:
    CALLBACK_API_VERSION = mqtt.CallbackAPIVersion.VERSION2
except AttributeError:
    CALLBACK_API_VERSION = None

def on_connect_success(client):
    print(f"[+] Terhubung ke MQTT Broker ({BROKER}:{PORT})")
    
    # Subscribe topics
    client.subscribe(TOPIC_COMMAND)
    client.subscribe(TOPIC_SERVER_STATUS)
    print(f"[+] Subscribed ke command topic: {TOPIC_COMMAND}")
    
    # Announce status ONLINE
    send_status_update(client, "online")

def send_status_update(client, status):
    payload = {"status": status, "mac_address": MAC_ADDRESS}
    payload["signature"] = generate_hmac_signature(payload)
    client.publish(TOPIC_STATUS, json.dumps(payload, separators=(',', ':')), qos=1, retain=True)
    print(f"[📡] Status terkirim: {status.upper()}")

if CALLBACK_API_VERSION is not None:
    def on_connect(client, userdata, flags, reason_code, properties):
        if reason_code == 0:
            on_connect_success(client)
        else:
            print(f"[-] Gagal terhubung, reason: {reason_code}")
else:
    def on_connect(client, userdata, flags, rc):
        if rc == 0:
            on_connect_success(client)
        else:
            print(f"[-] Gagal terhubung, rc: {rc}")

def on_message(client, userdata, msg):
    global max_slots, detection_polygons, threshold_banyak, threshold_terbatas, stream
    try:
        payload_str = msg.payload.decode('utf-8')
        payload = json.loads(payload_str)
        
        # Verify signature
        received_signature = payload.pop("signature", None)
        if not received_signature:
            print("[⚠️] DITOLAK: Perintah tidak memiliki HMAC Signature.")
            return
            
        calculated_signature = generate_hmac_signature(payload)
        if not hmac.compare_digest(received_signature, calculated_signature):
            print("[🚨] DITOLAK: Signature tidak cocok!")
            return

        action = payload.get("action")
        print(f"\n[📥] Perintah terverifikasi: {action}")

        if action == "update_config":
            with config_lock:
                max_slots = payload.get("max_slots", max_slots)
                detection_polygons = payload.get("detection_polygon", detection_polygons)
                threshold_banyak = payload.get("threshold_banyak", threshold_banyak)
                threshold_terbatas = payload.get("threshold_terbatas", threshold_terbatas)
            print(f"[⚙️] Config diupdate: max_slots={max_slots}, polygons_count={len(detection_polygons)}, thresholds=({threshold_banyak}%, {threshold_terbatas}%)")

        elif action == "snapshot":
            print("[📸] Mengambil snapshot...")
            if stream:
                image_bytes = stream.capture_jpeg()
                iv_b64, encrypted_b64 = encrypt_image_aes(image_bytes)
                
                response_payload = {
                    "mac_address": MAC_ADDRESS,
                    "timestamp": int(time.time()),
                    "encrypted_image": encrypted_b64,
                    "iv": iv_b64,
                    "current_count": current_vehicle_count
                }
                response_payload["signature"] = generate_hmac_signature(response_payload)
                client.publish(TOPIC_SNAPSHOT, json.dumps(response_payload), qos=1)
                print("[📤] Snapshot terenkripsi & ditandatangani berhasil dikirim.")

        elif action == "connection_test":
            print("[🏓] Connection test, mengirim status online...")
            send_status_update(client, "online")

        elif action == "chat":
            username = payload.get("username", "Admin")
            message = payload.get("message", "")
            print(f"[💬] Chat dari {username}: {message}")

        elif msg.topic == TOPIC_SERVER_STATUS:
            server_status = payload.get("status", "").upper()
            if server_status == "ONLINE":
                print("[🌐] Server online, mengirim ulang status presence...")
                send_status_update(client, "online")

    except Exception as e:
        print(f"[-] Gagal memproses pesan: {e}")

# ============================================================
# MAIN AI LOOP
# ============================================================
def main():
    global stream, current_vehicle_count
    
    print("[+] Loading YOLOv8 model...")
    model = YOLO(args.weights)
    print("[+] Model loaded.")

    # Camera Stream initialization
    stream = CameraStream(args.source)

    # Initialize MQTT client
    if CALLBACK_API_VERSION is not None:
        client = mqtt.Client(CALLBACK_API_VERSION, transport="websockets")
    else:
        client = mqtt.Client(transport="websockets")

    # Set Last Will and Testament (LWT)
    offline_payload = {"status": "offline", "mac_address": MAC_ADDRESS}
    offline_payload["signature"] = generate_hmac_signature(offline_payload)
    client.will_set(TOPIC_STATUS, payload=json.dumps(offline_payload, separators=(',', ':')), qos=1, retain=True)

    # Setup TLS & Auth
    if PORT == 443:
        import ssl
        client.tls_set(cert_reqs=ssl.CERT_NONE)

    if MQTT_USER and MQTT_PASSWORD:
        client.username_pw_set(MQTT_USER, MQTT_PASSWORD)

    client.on_connect = on_connect
    client.on_message = on_message

    print(f"[+] Menghubungkan ke MQTT Broker ws://{BROKER}:{PORT} ...")
    client.connect(BROKER, PORT, 60)
    
    # Start MQTT Loop in a separate background thread
    mqtt_thread = threading.Thread(target=client.loop_forever, daemon=True)
    mqtt_thread.start()

    # Main detection loop (every 2 seconds)
    try:
        while True:
            start_time = time.time()
            
            ret, frame = stream.read()
            if ret and frame is not None:
                # Run YOLO prediction (classes filter: e.g. 2=car, 3=motorcycle)
                results = model.predict(frame, conf=args.confidence, classes=TARGET_CLASSES, verbose=False)
                
                vehicles_inside = 0
                
                with config_lock:
                    polys = list(detection_polygons)
                
                if results and len(results) > 0:
                    boxes = results[0].boxes
                    for box in boxes:
                        x1, y1, x2, y2 = map(int, box.xyxy[0])
                        # Bottom center (reference point of vehicle)
                        ref_point = (int((x1 + x2) / 2), y2)
                        
                        if len(polys) > 0:
                            if is_inside_any_polygon(ref_point, polys):
                                vehicles_inside += 1
                        else:
                            # If no polygon defined yet, count all detected vehicles as fallback or 0?
                            # The rule says: "perhitungan kendaraan terparkir ... di dalam bounding box"
                            # If there are no polygons defined yet, count is 0 inside the zones.
                            pass
                
                current_vehicle_count = vehicles_inside
                print(f"[🤖] Detection: {vehicles_inside} kendaraan di dalam zona deteksi")

                # Send count to server
                count_payload = {
                    "mac_address": MAC_ADDRESS,
                    "timestamp": int(time.time()),
                    "count": current_vehicle_count
                }
                count_payload["signature"] = generate_hmac_signature(count_payload)
                client.publish(TOPIC_COUNT, json.dumps(count_payload, separators=(',', ':')), qos=1)
                
            # Control execution rate to run every ~2 seconds
            elapsed = time.time() - start_time
            sleep_time = max(0.1, 2.0 - elapsed)
            time.sleep(sleep_time)

    except KeyboardInterrupt:
        print("\n[!] Dihentikan oleh user.")
    finally:
        # Graceful disconnect: send offline status manually since LWT only fires on ungraceful disconnects
        send_status_update(client, "offline")
        time.sleep(0.5)
        stream.stop()
        client.disconnect()
        print("[+] Selesai.")

if __name__ == "__main__":
    main()
