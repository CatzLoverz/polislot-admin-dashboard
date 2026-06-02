"""
PoliSlot Headless WebSocket Parking Detector
============================================
Padanan WebSocket (Laravel Reverb) dari parking_detector_mqtt.py.

Fitur:
- Presence Channel via Reverb -> Deteksi online/offline instan.
- Headless (tanpa cv2.imshow).
- YOLOv8 pada mobil (class 2) dan motor (class 3) dalam multi-polygon.
- Post hitungan ke /api/iot/count dengan HMAC signature.
- Menerima command snapshot, update_config, connection_test, chat via WS.
- Kirim snapshot terenkripsi AES-256-CBC ke /api/iot/snapshot.
"""

import sys
import time
import json
import hmac
import hashlib
import base64
import asyncio
import os
import threading
import argparse

import cv2
import numpy as np
import requests
import websockets

from Crypto.Cipher import AES
from Crypto.Util.Padding import pad
from ultralytics import YOLO

# ============================================================
# PARSE ARGUMENTS
# ============================================================
parser = argparse.ArgumentParser(description="PoliSlot Headless WS Parking Detector")
parser.add_argument("mac_address", type=str, nargs="?", help="MAC Address of the device (e.g. 00:1A:2B:3C:4D:5E)")
parser.add_argument("--source", type=str, default="0", help="Video source: '0' for webcam, or RTSP/file path")
parser.add_argument("--weights", type=str, default="yolov8n.pt", help="YOLO model weight file")
parser.add_argument("--confidence", type=float, default=0.4, help="YOLO confidence threshold")
parser.add_argument("--classes", type=str, default="2,3", help="Comma-separated YOLO class filter (2=car, 3=motorcycle)")
parser.add_argument("--server", type=str, default="http://127.0.0.1:8080", help="Server Base URL (e.g. http://localhost:8000)")
parser.add_argument("--reverb-host", type=str, default="127.0.0.1", help="Reverb WebSocket Host")
parser.add_argument("--reverb-port", type=int, default=8080, help="Reverb WebSocket Port")
parser.add_argument("--reverb-scheme", type=str, default="ws", help="Reverb WebSocket Scheme (ws or wss)")
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

# Server API endpoints based on Server URL
SERVER_BASE_URL = args.server
API_WS_AUTH_URL  = f"{SERVER_BASE_URL}/api/iot/ws-auth"
API_SNAPSHOT_URL = f"{SERVER_BASE_URL}/api/iot/snapshot"
API_COUNT_URL    = f"{SERVER_BASE_URL}/api/iot/count"

# Reverb Configuration (matching Laravel .env)
REVERB_APP_KEY = "xcubvd4inm14ayepjhro"
REVERB_HOST    = args.reverb_host
REVERB_PORT    = args.reverb_port
REVERB_SCHEME  = args.reverb_scheme

SHARED_SECRET = args.secret
TARGET_CLASSES = [int(c.strip()) for c in args.classes.split(",") if c.strip().isdigit()]

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

def generate_auth_signature(mac_address, timestamp):
    key = get_aes_key()
    data = f"{mac_address}:{timestamp}"
    return hmac.new(key, data.encode('utf-8'), hashlib.sha256).hexdigest()

def generate_hmac_signature(payload_dict):
    key = get_aes_key()
    data_string = json.dumps(payload_dict, separators=(',', ':'))
    return hmac.new(key, data_string.encode('utf-8'), hashlib.sha256).hexdigest()

def encrypt_image_aes(image_bytes):
    key = get_aes_key()
    iv = os.urandom(16)
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_data = pad(image_bytes, AES.block_size)
    encrypted_bytes = cipher.encrypt(padded_data)
    return base64.b64encode(iv).decode('utf-8'), base64.b64encode(encrypted_bytes).decode('utf-8')

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
            frame = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(frame, "Camera offline", (180, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
        else:
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
# COMMAND HANDLER
# ============================================================
def handle_command(raw_data):
    global max_slots, detection_polygons, threshold_banyak, threshold_terbatas, stream
    try:
        payload = json.loads(raw_data) if isinstance(raw_data, str) else raw_data
        cmd_data = payload.get('data', payload)
        if isinstance(cmd_data, str):
            cmd_data = json.loads(cmd_data)

        action = cmd_data.get('action', payload.get('action', ''))
        print(f"\n[📥] Perintah WS diterima: {action}")

        # Signature verification
        received_signature = cmd_data.get('signature', '')
        if not received_signature:
            print("[⚠️] DITOLAK: Perintah tidak memiliki HMAC Signature.")
            return

        verify_data = {k: v for k, v in cmd_data.items() if k != 'signature'}
        calculated = generate_hmac_signature(verify_data)
        if not hmac.compare_digest(received_signature, calculated):
            print("[🚨] DITOLAK: Signature tidak valid!")
            return

        if action == 'update_config':
            with config_lock:
                max_slots = cmd_data.get('max_slots', max_slots)
                detection_polygons = cmd_data.get('detection_polygon', detection_polygons)
                threshold_banyak = cmd_data.get('threshold_banyak', threshold_banyak)
                threshold_terbatas = cmd_data.get('threshold_terbatas', threshold_terbatas)
            print(f"[⚙️] Config diupdate via WS: max_slots={max_slots}, polygons_count={len(detection_polygons)}, thresholds=({threshold_banyak}%, {threshold_terbatas}%)")

        elif action == 'snapshot':
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
                
                print("[📤] Mengirim gambar terenkripsi ke server via POST...")
                resp = requests.post(API_SNAPSHOT_URL, json=response_payload, timeout=15)
                if resp.status_code == 200:
                    print("[✅] Snapshot terenkripsi & ditandatangani berhasil dikirim.")
                else:
                    print(f"[-] Gagal mengirim snapshot: {resp.status_code} — {resp.text}")

        elif action == 'connection_test':
            print("[🏓] Connection test diterima dari server.")

        elif action == 'chat':
            username = cmd_data.get('username', 'Admin')
            message = cmd_data.get('message', '')
            print(f"[💬] Chat dari {username}: {message}")

    except Exception as e:
        print(f"[-] Error memproses command: {e}")

# ============================================================
# DETECTOR BACKGROUND THREAD (Runs YOLO & Sends Count)
# ============================================================
def detector_loop(model, confidence):
    global current_vehicle_count, stream
    print("[+] Detector thread started.")
    
    while True:
        start_time = time.time()
        ret, frame = stream.read()
        if ret and frame is not None:
            # Predict
            results = model.predict(frame, conf=confidence, classes=TARGET_CLASSES, verbose=False)
            vehicles_inside = 0
            
            with config_lock:
                polys = list(detection_polygons)
                
            if results and len(results) > 0:
                boxes = results[0].boxes
                for box in boxes:
                    x1, y1, x2, y2 = map(int, box.xyxy[0])
                    ref_point = (int((x1 + x2) / 2), y2)
                    
                    if len(polys) > 0:
                        if is_inside_any_polygon(ref_point, polys):
                            vehicles_inside += 1
            
            current_vehicle_count = vehicles_inside
            print(f"[🤖] Detection: {vehicles_inside} kendaraan di dalam zona deteksi")

            # Send count to server via API endpoint
            timestamp = int(time.time())
            count_payload = {
                "mac_address": MAC_ADDRESS,
                "timestamp": timestamp,
                "count": current_vehicle_count
            }
            count_payload["signature"] = generate_hmac_signature(count_payload)
            
            try:
                resp = requests.post(API_COUNT_URL, json=count_payload, timeout=5)
                if resp.status_code != 200:
                    print(f"[-] Gagal mengirim count: {resp.status_code} — {resp.text}")
            except Exception as e:
                print(f"[-] Error mengirim count: {e}")

        # Control rate to run every ~2 seconds
        elapsed = time.time() - start_time
        sleep_time = max(0.1, 2.0 - elapsed)
        time.sleep(sleep_time)

# ============================================================
# WEBSOCKET CONNECTION MANAGER
# ============================================================
async def websocket_client():
    clean_mac = MAC_ADDRESS.replace(':', '')
    channel_name = f"presence-iot.device.{clean_mac}"
    ws_uri = f"{REVERB_SCHEME}://{REVERB_HOST}:{REVERB_PORT}/app/{REVERB_APP_KEY}?protocol=7&client=python&version=1.0"

    reconnect_attempts = 0
    max_reconnects = 20

    while reconnect_attempts < max_reconnects:
        try:
            print(f"\n[🔗] Menghubungkan ke Reverb WebSocket ({ws_uri})...")
            async with websockets.connect(ws_uri, ping_interval=25, ping_timeout=10) as ws:
                reconnect_attempts = 0
                
                # 1. Wait for connection established
                raw = await ws.recv()
                msg = json.loads(raw)
                if msg.get('event') != 'pusher:connection_established':
                    print(f"[-] Unexpected event: {msg.get('event')}")
                    continue

                conn_data = json.loads(msg['data'])
                socket_id = conn_data['socket_id']
                print(f"[+] Terhubung ke Reverb! socket_id: {socket_id}")

                # 2. Auth via API Server
                timestamp = int(time.time())
                signature = generate_auth_signature(MAC_ADDRESS, timestamp)
                
                auth_resp = requests.post(API_WS_AUTH_URL, json={
                    'socket_id': socket_id,
                    'channel_name': channel_name,
                    'mac_address': MAC_ADDRESS,
                    'timestamp': timestamp,
                    'signature': signature,
                }, timeout=10)

                if auth_resp.status_code == 403:
                    print(f"[FATAL] Device '{MAC_ADDRESS}' TIDAK TERDAFTAR di server!")
                    return
                elif auth_resp.status_code != 200:
                    print(f"[-] Auth gagal ({auth_resp.status_code}): {auth_resp.text}")
                    break

                auth_data = auth_resp.json()
                print("[+] Auth WebSocket berhasil.")

                # 3. Subscribe to Presence Channel
                subscribe_msg = {
                    "event": "pusher:subscribe",
                    "data": {
                        "auth": auth_data['auth'],
                        "channel": channel_name,
                        "channel_data": auth_data['channel_data']
                    }
                }
                await ws.send(json.dumps(subscribe_msg))
                print(f"[📡] Subscribing ke channel {channel_name}...")

                # 4. Listen for incoming commands
                async for raw_msg in ws:
                    msg = json.loads(raw_msg)
                    event = msg.get('event', '')

                    if event == 'pusher:ping':
                        await ws.send(json.dumps({"event": "pusher:pong"}))
                    elif event == 'pusher_internal:subscription_succeeded':
                        print(f"[📡] Status: ONLINE (Subscribed ke {channel_name})")
                    elif event == 'command.sent':
                        handle_command(msg.get('data', '{}'))
                    elif event == 'pusher:error':
                        print(f"[-] Pusher error: {msg.get('data')}")

        except websockets.exceptions.ConnectionClosedError as e:
            print(f"[-] WS Koneksi terputus: {e}")
        except Exception as e:
            print(f"[-] WS Error: {e}")

        reconnect_attempts += 1
        print(f"[🔄] Reconnecting in 5s... ({reconnect_attempts}/{max_reconnects})")
        await asyncio.sleep(5)

# ============================================================
# MAIN
# ============================================================
def main():
    global stream
    
    print("[+] Loading YOLOv8 weights...")
    model = YOLO(args.weights)
    print("[+] YOLOv8 Model loaded.")

    # Initialize camera stream
    stream = CameraStream(args.source)

    # Start detector thread
    detector_thread = threading.Thread(
        target=detector_loop, 
        args=(model, args.confidence), 
        daemon=True
    )
    detector_thread.start()

    # Run Websocket Client
    try:
        asyncio.run(websocket_client())
    except KeyboardInterrupt:
        print("\n[!] Dihentikan oleh user.")
    finally:
        stream.stop()
        print("[+] Selesai.")

if __name__ == "__main__":
    main()
