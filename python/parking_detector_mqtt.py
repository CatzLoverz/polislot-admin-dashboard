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
from dotenv import load_dotenv
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
# KONFIGURASI IOT DEVICE (PARKING DETECTOR - MQTT)
# ============================================================
# Ambil MAC Address dari argumen terminal, atau minta input jika kosong
if len(sys.argv) > 1:
    MAC_ADDRESS = sys.argv[1]
else:
    MAC_ADDRESS = input("Masukkan MAC Address perangkat (contoh: 00:1A:2B:3C:4D:5E): ").strip()
    if not MAC_ADDRESS:
        print("[-] MAC Address tidak boleh kosong!")
        sys.exit(1)

# Pengaturan Koneksi MQTT Broker
BROKER = get_env_or_fail("MQTT_BROKER")
PORT = int(get_env_or_fail("MQTT_PORT"))  # Cloudflare Tunnel menggunakan port 443 (HTTPS/WSS). Gunakan 1883 untuk local TCP.

# MQTT Authentication (sesuai dengan config Mosquitto broker)
MQTT_USER = get_env_or_fail("MQTT_USER")
MQTT_PASSWORD = get_env_or_fail("MQTT_PASSWORD")

# Keamanan (Harus SAMA persis dengan IOT_API_SECRET di Laravel .env)
SHARED_SECRET = get_env_or_fail("SHARED_SECRET").strip().strip('"').strip("'")

# Konfigurasi AI & Deteksi YOLOv8
SOURCE = get_env_or_fail("CAMERA_SOURCE")            # Sumber video: "0" untuk webcam default, atau path video file / RTSP URL
YOLO_WEIGHTS = get_env_or_fail("YOLO_WEIGHTS")  # File weights model YOLOv8 (yolov8n.pt / custom model)
CONFIDENCE_THRESHOLD = float(get_env_or_fail("CONFIDENCE_THRESHOLD"))   # Ambang batas kepercayaan YOLOv8 (0.0 s.d 1.0)

# Parsing TARGET_CLASSES (contoh di .env: 2,3)
_target_classes_env = get_env_or_fail("TARGET_CLASSES")
TARGET_CLASSES = [int(cls.strip()) for cls in _target_classes_env.split(',') if cls.strip().isdigit()] # Filter index class YOLO: 2 = car (mobil), 3 = motorcycle (motor)

# ============================================================
# KONFIGURASI TOPIK MQTT & VARIABEL IN-MEMORY
# ============================================================
TOPIC_COMMAND = f"polislot/device/{MAC_ADDRESS}/command"
TOPIC_SNAPSHOT = f"polislot/device/{MAC_ADDRESS}/snapshot"
TOPIC_STATUS = f"polislot/device/{MAC_ADDRESS}/status"
TOPIC_COUNT = f"polislot/device/{MAC_ADDRESS}/count"
TOPIC_SERVER_STATUS = "polislot/server/status"

config_lock = threading.Lock()
max_slots = 0
detection_polygons = []
threshold_banyak = 30.0
threshold_terbatas = 80.0
current_vehicle_count = 0

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
    
    # Berikan jeda 1 detik agar broker selesai mendaftarkan subscription
    # sebelum status online dikirim (mencegah race condition push config)
    time.sleep(1.0)
    
    # Announce status ONLINE
    send_status_update(client, "online")

def send_status_update(client, status):
    payload = {"status": status, "mac_address": MAC_ADDRESS}
    payload["signature"] = generate_hmac_signature(payload)
    client.publish(TOPIC_STATUS, json.dumps(payload, separators=(',', ':')), qos=1, retain=True)
    print(f"[📡] Status presence terkirim: {status.upper()}")

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
            save_local_config()

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
                if "save_image" in payload:
                    response_payload["save_image"] = payload["save_image"]
                
                response_payload["signature"] = generate_hmac_signature(response_payload)
                client.publish(TOPIC_SNAPSHOT, json.dumps(response_payload), qos=1)
                print("[📤] Snapshot terenkripsi & ditandatangani berhasil dikirim.")

        elif action == "connection_test":
            print("[🏓] Connection test, mengirim status online...")
            send_status_update(client, "online")


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
    
    # Memuat konfigurasi dari cache lokal jika tersedia
    load_local_config()
    
    print("[+] Loading Model weights...")
    model = YOLO(YOLO_WEIGHTS)
    print(f"[+] {YOLO_WEIGHTS} loaded.")

    # Camera Stream initialization
    stream = CameraStream(SOURCE)

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
                # Run YOLO prediction
                results = model.predict(frame, conf=CONFIDENCE_THRESHOLD, classes=TARGET_CLASSES, verbose=False)
                
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
        # Graceful disconnect
        send_status_update(client, "offline")
        time.sleep(0.5)
        stream.stop()
        client.disconnect()
        print("[+] Selesai.")

if __name__ == "__main__":
    main()
