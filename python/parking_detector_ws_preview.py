"""
PoliSlot Headless WebSocket Parking Detector (Dengan Preview Realtime)
=====================================================================
Klien WebSocket (Laravel Reverb) dengan visualisasi OpenCV Window.

Fitur:
- Presence Channel via Reverb -> Deteksi online/offline instan.
- OpenCV Window Preview -> Menampilkan feed kamera dengan overlay YOLOv8 + Polygon.
- YOLOv8 pada mobil (class 2) dan motor (class 3) dalam multi-polygon.
- Post hitungan ke /api/iot/count dengan HMAC signature (non-blocking).
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
from dotenv import load_dotenv

import cv2
import numpy as np
import requests
import websockets

from Crypto.Cipher import AES
from Crypto.Util.Padding import pad
from ultralytics import YOLO

# Pastikan memuat .env dari folder yang sama dengan script
env_path = os.path.join(os.path.dirname(__file__), '.env')
load_dotenv(env_path)

def get_env_or_fail(key):
    val = os.getenv(key)
    if val is None or val.strip() == "":
        print(f"[-] FATAL ERROR: Variabel '{key}' tidak ditemukan atau kosong di file .env!")
        sys.exit(1)
    return val

# ============================================================
# KONFIGURASI IOT DEVICE (PARKING DETECTOR - WEBSOCKET DENGAN PREVIEW)
# ============================================================
# Ambil MAC Address dari argumen terminal (argv[1]), atau fallback ke env variable MAC_ADDRESS
if len(sys.argv) > 1:
    MAC_ADDRESS = sys.argv[1]
else:
    MAC_ADDRESS = os.getenv("MAC_ADDRESS", "").strip()
    if not MAC_ADDRESS:
        print("[-] MAC Address tidak boleh kosong!")
        sys.exit(1)

# Pengaturan Server Base URL (API endpoint HTTP POST)
SERVER_BASE_URL = get_env_or_fail("SERVER_BASE_URL") # Contoh: http://localhost:8000 atau domain produksi

# Pengaturan Reverb WebSocket (Sesuai setelan Reverb di Laravel .env)
REVERB_HOST    = get_env_or_fail("REVERB_HOST")
REVERB_PORT    = int(get_env_or_fail("REVERB_PORT"))
REVERB_SCHEME  = get_env_or_fail("REVERB_SCHEME")  # Gunakan "ws" untuk lokal, "wss" untuk tunnel produksi

# Keamanan (Harus SAMA persis dengan IOT_API_SECRET di Laravel .env)
SHARED_SECRET = get_env_or_fail("SHARED_SECRET").strip().strip('"').strip("'")

# Konfigurasi AI & Deteksi YOLOv8
SOURCE = get_env_or_fail("CAMERA_SOURCE")            # Sumber video: "0" untuk webcam default, atau path video file / RTSP URL
YOLO_WEIGHTS = get_env_or_fail("YOLO_WEIGHTS")  # File weights model YOLOv8 (yolov8n.pt / custom model)
CONFIDENCE_THRESHOLD = float(get_env_or_fail("CONFIDENCE_THRESHOLD"))   # Ambang batas kepercayaan YOLOv8 (0.0 s.d 1.0)
ENABLE_DETECTION_LOG = os.getenv("ENABLE_DETECTION_LOG", "true").lower() == "true" # Tampilkan log deteksi di terminal
ENABLE_DEBUG_LOG = os.getenv("ENABLE_DEBUG_LOG", "false").lower() == "true" # Tampilkan log debug jaringan yang repetitif

# Parsing TARGET_CLASSES (contoh di .env: 2,3)
_target_classes_env = get_env_or_fail("TARGET_CLASSES")
TARGET_CLASSES = [int(cls.strip()) for cls in _target_classes_env.split(',') if cls.strip().isdigit()] # Filter index class YOLO: 2 = car (mobil), 3 = motorcycle (motor)

# ============================================================
# KONFIGURASI API ENDPOINT & VARIABEL IN-MEMORY
# ============================================================
API_WS_AUTH_URL  = f"{SERVER_BASE_URL}/api/iot/ws-auth"
API_SNAPSHOT_URL = f"{SERVER_BASE_URL}/api/iot/snapshot"
API_COUNT_URL    = f"{SERVER_BASE_URL}/api/iot/count"
API_CONFIG_URL   = f"{SERVER_BASE_URL}/api/iot/config"

REVERB_APP_KEY = get_env_or_fail("REVERB_APP_KEY")

config_lock = threading.Lock()
max_slots = 0
detection_polygons = []
threshold_banyak = 30.0
threshold_terbatas = 80.0
current_vehicle_count = 0
ws_connected = False
last_http_success_time = time.time()
ws_client = None
ws_loop = None

# File cache konfigurasi lokal
CONFIG_FILE = f"config_cache_{MAC_ADDRESS.replace(':', '')}.json"

def save_local_config():
    global max_slots, detection_polygons, threshold_banyak, threshold_terbatas
    try:
        with config_lock:
            config_data = {
                "max_slots": max_slots,
                "detection_polygon": detection_polygons,
                "threshold_banyak": threshold_banyak,
                "threshold_terbatas": threshold_terbatas
            }
        with open(CONFIG_FILE, "w") as f:
            json.dump(config_data, f, indent=4)
        print(f"[💾] Config disimpan secara lokal ke {CONFIG_FILE}")
    except Exception as e:
        print(f"[-] Gagal menyimpan config lokal: {e}")

def load_local_config():
    global max_slots, detection_polygons, threshold_banyak, threshold_terbatas
    if os.path.exists(CONFIG_FILE):
        try:
            with open(CONFIG_FILE, "r") as f:
                config_data = json.load(f)
            with config_lock:
                max_slots = config_data.get("max_slots", max_slots)
                detection_polygons = config_data.get("detection_polygon", detection_polygons)
                threshold_banyak = config_data.get("threshold_banyak", threshold_banyak)
                threshold_terbatas = config_data.get("threshold_terbatas", threshold_terbatas)
            print(f"[💾] Berhasil memuat config lokal: max_slots={max_slots}, polygons_count={len(detection_polygons)}, thresholds=({threshold_banyak}%, {threshold_terbatas}%)")
        except Exception as e:
            print(f"[-] Gagal memuat config lokal: {e}")

def fetch_remote_config():
    global max_slots, detection_polygons, threshold_banyak, threshold_terbatas
    try:
        timestamp = int(time.time())
        # Hitung signature menggunakan HMAC-SHA256: "mac_address:timestamp"
        data_to_sign = f"{MAC_ADDRESS}:{timestamp}"
        key32 = get_aes_key()
        signature = hmac.new(key32, data_to_sign.encode('utf-8'), hashlib.sha256).hexdigest()

        payload = {
            "mac_address": MAC_ADDRESS,
            "timestamp": timestamp,
            "signature": signature
        }
        
        print(f"[📥] Mencoba mengambil konfigurasi terbaru dari server: {API_CONFIG_URL} ...")
        resp = requests.post(API_CONFIG_URL, json=payload, timeout=10)
        
        if resp.status_code == 200:
            res_data = resp.json()
            if res_data.get("status") == "success":
                cfg = res_data.get("config", {})
                with config_lock:
                    max_slots = cfg.get("max_slots", max_slots)
                    detection_polygons = cfg.get("detection_polygon", detection_polygons)
                    threshold_banyak = cfg.get("threshold_banyak", threshold_banyak)
                    threshold_terbatas = cfg.get("threshold_terbatas", threshold_terbatas)
                print(f"[✅] Berhasil sinkronisasi config dari server: max_slots={max_slots}, polygons_count={len(detection_polygons)}")
                save_local_config()
            else:
                print(f"[⚠️] Gagal sinkronisasi config: {res_data.get('message')}")
        else:
            print(f"[⚠️] HTTP request gagal saat sinkronisasi config: status_code={resp.status_code}")
    except Exception as e:
        print(f"[-] Gagal sinkronisasi config via HTTP: {e}")


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
def is_bbox_in_polygon(bbox, polygon, iou_threshold=0.5):
    if len(polygon) < 3:
        return False
    x1, y1, x2, y2 = bbox
    w, h = x2 - x1, y2 - y1
    if w <= 0 or h <= 0:
        return False
    poly_shifted = np.array([[(p[0] - x1, p[1] - y1) for p in polygon]], dtype=np.int32)
    mask = np.zeros((h, w), dtype=np.uint8)
    cv2.fillPoly(mask, poly_shifted, 255)
    intersect_area = cv2.countNonZero(mask)
    return (intersect_area / float(w * h)) >= iou_threshold

def is_bbox_in_any_polygon(bbox, polygons, iou_threshold=0.5):
    for poly in polygons:
        if is_bbox_in_polygon(bbox, poly, iou_threshold):
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
            save_local_config()

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
                if "save_image" in cmd_data:
                    response_payload["save_image"] = cmd_data["save_image"]

                response_payload["signature"] = generate_hmac_signature(response_payload)
                
                print("[📤] Mengirim gambar terenkripsi ke server via POST...")
                resp = requests.post(API_SNAPSHOT_URL, json=response_payload, timeout=15)
                if resp.status_code == 200:
                    print("[✅] Snapshot terenkripsi & ditandatangani berhasil dikirim.")
                else:
                    print(f"[-] Gagal mengirim snapshot: {resp.status_code} — {resp.text}")

        elif action == 'connection_test':
            print("[🏓] Connection test diterima dari server.")


    except Exception as e:
        print(f"[-] Error memproses command: {e}")

# ============================================================
# WEBSOCKET CONNECTION MANAGER
# ============================================================
async def websocket_client():
    global ws_connected
    clean_mac = MAC_ADDRESS.replace(':', '')
    channel_name = f"presence-iot.device.{clean_mac}"
    ws_uri = f"{REVERB_SCHEME}://{REVERB_HOST}:{REVERB_PORT}/app/{REVERB_APP_KEY}?protocol=7&client=python&version=1.0"

    reconnect_attempts = 0

    while True:
        try:
            if ENABLE_DEBUG_LOG:
                print(f"\n[🔗] Menghubungkan ke Reverb WebSocket ({ws_uri})...")
            async with websockets.connect(ws_uri, ping_interval=25, ping_timeout=10) as ws:
                global ws_client
                ws_client = ws
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
                
                auth_payload = {
                    'socket_id': socket_id,
                    'channel_name': channel_name,
                    'mac_address': MAC_ADDRESS,
                    'timestamp': timestamp,
                    'signature': signature,
                }
                
                import functools
                loop = asyncio.get_running_loop()
                auth_resp = await loop.run_in_executor(None, functools.partial(requests.post, API_WS_AUTH_URL, json=auth_payload, timeout=10))

                if auth_resp.status_code == 403:
                    print(f"[FATAL] Device '{MAC_ADDRESS}' TIDAK TERDAFTAR di server!")
                    return
                elif auth_resp.status_code != 200:
                    print(f"[-] Auth gagal ({auth_resp.status_code}): {auth_resp.text}")
                    break

                auth_data = auth_resp.json()
                print("[+] Auth WebSocket berhasil.")

                # Sinkronisasi config terbaru dari server
                await loop.run_in_executor(None, fetch_remote_config)

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
                        ws_connected = True
                    elif event == 'command.sent':
                        threading.Thread(target=handle_command, args=(msg.get('data', '{}'),), daemon=True).start()
                    elif event == 'pusher:error':
                        print(f"[-] Pusher error: {msg.get('data')}")

        except websockets.exceptions.ConnectionClosedError as e:
            ws_connected = False
            if ENABLE_DEBUG_LOG:
                print(f"[-] WS Koneksi terputus: {e}")
        except Exception as e:
            ws_connected = False
            if ENABLE_DEBUG_LOG:
                print(f"[-] WS Error: {e}")

        reconnect_attempts += 1
        if ENABLE_DEBUG_LOG:
            print(f"[🔄] Reconnecting in 5s... (Attempt: {reconnect_attempts})")
        await asyncio.sleep(5)

# Async loop runner untuk thread latar belakang
def start_websocket_thread():
    global ws_loop
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    ws_loop = loop
    try:
        loop.run_until_complete(websocket_client())
    except Exception as e:
        print(f"[-] WS Client thread stopped: {e}")

# ============================================================
# MAIN
# ============================================================
def main():
    global stream, current_vehicle_count, max_slots, detection_polygons, last_http_success_time
    
    # Memuat konfigurasi dari cache lokal jika tersedia
    load_local_config()
    
    print("[+] Loading Model weights...")
    model = YOLO(YOLO_WEIGHTS)
    print(f"[+] {YOLO_WEIGHTS} loaded.")

    # Initialize camera stream
    stream = CameraStream(SOURCE)

    # Start WebSocket client thread (menggunakan event loop asyncio tersendiri)
    ws_thread = threading.Thread(target=start_websocket_thread, daemon=True)
    ws_thread.start()

    print("[+] Preview Window aktif. Tekan 'q' untuk keluar.")
    last_count_send_time = 0

    try:
        while True:
            ret, frame = stream.read()
            if ret and frame is not None:
                preview_frame = frame.copy()
                
                # 1. Ambil salinan polygon deteksi secara thread-safe
                with config_lock:
                    polys = list(detection_polygons)
                    current_max_slots = max_slots
                
                # 2. Gambar Polygon Deteksi di frame preview (warna Kuning)
                for poly in polys:
                    if len(poly) >= 3:
                        pts = np.array(poly, np.int32)
                        pts = pts.reshape((-1, 1, 2))
                        cv2.polylines(preview_frame, [pts], True, (0, 255, 255), 2)
                
                # 3. Jalankan YOLOv8 Prediksi pada frame asli
                predict_start = time.time()
                results = model.predict(frame, conf=CONFIDENCE_THRESHOLD, classes=TARGET_CLASSES, verbose=False)
                predict_ms = (time.time() - predict_start) * 1000.0

                vehicles_inside = 0
                box_count = 0
                speed_text = f"inference {predict_ms:.1f}ms"

                if results and len(results) > 0:
                    res0 = results[0]
                    boxes = res0.boxes
                    box_count = len(boxes)
                    if hasattr(res0, 'speed'):
                        try:
                            speed_val = res0.speed
                            if isinstance(speed_val, dict):
                                speed_text = ', '.join(f"{k}: {v:.1f}ms" for k, v in speed_val.items())
                            else:
                                speed_text = str(speed_val)
                        except Exception:
                            speed_text = f"inference {predict_ms:.1f}ms"
                    for box in boxes:
                        x1, y1, x2, y2 = map(int, box.xyxy[0])
                        conf = float(box.conf[0])
                        cls = int(box.cls[0])
                        bbox = (x1, y1, x2, y2)
                        
                        # Cek proporsi bounding box yang masuk polygon
                        is_inside = False
                        if len(polys) > 0:
                            if is_bbox_in_any_polygon(bbox, polys):
                                vehicles_inside += 1
                                is_inside = True
                        
                        # Gambar Bounding Box (Hijau jika di dalam polygon, Merah jika di luar)
                        color = (0, 255, 0) if is_inside else (0, 0, 255)
                        cv2.rectangle(preview_frame, (x1, y1), (x2, y2), color, 2)
                        # Gambar titik tengah box
                        center_point = (int((x1 + x2) / 2), int((y1 + y2) / 2))
                        cv2.circle(preview_frame, center_point, 5, color, -1)
                        
                        label = f"{model.names[cls]} {conf:.2f}"
                        cv2.putText(preview_frame, label, (x1, y1 - 8), cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 1)

                current_vehicle_count = vehicles_inside
                if ENABLE_DETECTION_LOG:
                    print(f"[🤖] Detection: {vehicles_inside} kendaraan di dalam zona deteksi | boxes={box_count} | {speed_text}")

                # 4. Tulis informasi visual pada window preview
                status_text = f"Count: {vehicles_inside} | Max Slots: {current_max_slots}"
                cv2.putText(preview_frame, status_text, (15, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (255, 255, 255), 2)
                
                # Hitung Occupancy Status untuk info tambahan
                if current_max_slots > 0:
                    occupancy = (vehicles_inside / current_max_slots) * 100
                    cv2.putText(preview_frame, f"Occupancy: {occupancy:.1f}%", (15, 60), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (200, 255, 200), 1)

                # Tampilkan Window Preview Realtime
                cv2.imshow("PoliSlot - WS Realtime Preview", preview_frame)

                # 5. Kirim data count ke server setiap 2 detik secara asinkron agar tidak memblokir render frame preview
                now = time.time()
                if now - last_count_send_time >= 2.0:
                    if ws_connected:
                        timestamp = int(now)
                        count_payload = {
                            "mac_address": MAC_ADDRESS,
                            "timestamp": timestamp,
                            "count": current_vehicle_count
                        }
                        count_payload["signature"] = generate_hmac_signature(count_payload)
                        
                        # Definisikan sub-thread pengiriman agar post request tidak mendatangkan delay frame
                        def send_count():
                            global last_http_success_time
                            try:
                                resp = requests.post(API_COUNT_URL, json=count_payload, timeout=5)
                                if resp.status_code == 200:
                                    last_http_success_time = time.time()
                                elif ENABLE_DEBUG_LOG:
                                    print(f"[-] Gagal mengirim count: {resp.status_code} — {resp.text}")
                            except Exception as e:
                                if ENABLE_DEBUG_LOG:
                                    print(f"[-] Error mengirim count: {e}")
                        
                        threading.Thread(target=send_count, daemon=True).start()
                    else:
                        if ENABLE_DEBUG_LOG:
                            print("[🤖] WS Terputus, pengiriman count dilewati.")
                    last_count_send_time = now

            # Watchdog pengecekan HTTP / TCP Half-Open untuk WS
            if ws_connected and (time.time() - last_http_success_time > 30):
                print("[-] Deteksi TCP Half-Open (Tidak ada response API sukses selama 30 detik). Mereset WS...")
                if ws_client and ws_loop:
                    try:
                        asyncio.run_coroutine_threadsafe(ws_client.close(), ws_loop)
                    except Exception:
                        pass
                last_http_success_time = time.time()

            # OpenCV Window refresh event loop (juga mendeteksi tombol 'q' untuk quit)
            if cv2.waitKey(30) & 0xFF == ord('q'):
                break

    except KeyboardInterrupt:
        print("\n[!] Dihentikan oleh user.")
    finally:
        stream.stop()
        cv2.destroyAllWindows()
        print("[+] Selesai.")

if __name__ == "__main__":
    main()
