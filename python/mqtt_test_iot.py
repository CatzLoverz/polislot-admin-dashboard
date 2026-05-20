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
# KONFIGURASI IOT DEVICE
# ==========================================
# Ambil MAC Address dari argumen terminal, atau minta input jika kosong
if len(sys.argv) > 1:
    MAC_ADDRESS = sys.argv[1]
else:
    MAC_ADDRESS = input("Masukkan MAC Address perangkat (contoh: 00:1A:2B:3C:4D:5E): ").strip()
    if not MAC_ADDRESS:
        print("MAC Address tidak boleh kosong!")
        sys.exit(1)

BROKER = "mqtt.raihanatmaja.my.id"
PORT = 443  # Cloudflare Tunnel menggunakan port 443 (HTTPS/WSS)
TOPIC_COMMAND = f"polislot/device/{MAC_ADDRESS}/command"
TOPIC_SNAPSHOT = f"polislot/device/{MAC_ADDRESS}/snapshot"
TOPIC_STATUS = f"polislot/device/{MAC_ADDRESS}/status"
TOPIC_SERVER_STATUS = "polislot/server/status"

# MQTT Authentication (sesuai dengan config Mosquitto broker)
MQTT_USER = os.environ.get("MQTT_USER", "...")
MQTT_PASSWORD = os.environ.get("MQTT_PASSWORD", "...")

# Harus SAMA persis dengan IOT_API_SECRET di Laravel .env
# Catatan: Untuk AES-256, panjang karakter bebas karena di-hash lagi ke 32 byte menggunakan SHA256
SHARED_SECRET = "" 

# ==========================================
# FUNGSI KEAMANAN (AES & HMAC)
# ==========================================
def get_aes_key():
    # Ambil 32 byte pertama dari hash SHA256 dari secret key
    return hashlib.sha256(SHARED_SECRET.encode('utf-8')).digest()[:32]

def encrypt_image_aes(image_bytes):
    key = get_aes_key()
    iv = os.urandom(16) # Initialization Vector acak 16 byte
    cipher = AES.new(key, AES.MODE_CBC, iv)
    
    # Padding bytes gambar agar kelipatan 16 (Block size AES)
    padded_data = pad(image_bytes, AES.block_size)
    encrypted_bytes = cipher.encrypt(padded_data)
    
    # Kembalikan IV dan Ciphertext dalam bentuk Base64 string
    return base64.b64encode(iv).decode('utf-8'), base64.b64encode(encrypted_bytes).decode('utf-8')

def generate_hmac_signature(payload_dict):
    key = get_aes_key()
    # Ubah dictionary ke string JSON TANPA spasi agar sama dengan format server
    data_string = json.dumps(payload_dict, separators=(',', ':'))
    signature = hmac.new(key, data_string.encode('utf-8'), hashlib.sha256).hexdigest()
    return signature

# ==========================================
# FUNGSI KAMERA
# ==========================================
def capture_frame():
    print("📸 Kamera mengambil gambar dari webcam...")
    # Coba buka kamera default (0)
    cap = cv2.VideoCapture(0)
    
    if not cap.isOpened():
        print("❌ Gagal membuka kamera! Menggunakan gambar blank fallback.")
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        cv2.putText(img, "Kamera Tidak Terdeteksi", (100, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
    else:
        # Pemanasan sensor kamera (buang beberapa frame awal agar brightness/focus stabil)
        # Pada Raspberry Pi, butuh sekitar 20-30 frame agar Auto Exposure (AE) stabil
        for _ in range(30):
            cap.read()
            
        ret, img = cap.read()
        cap.release()
        
        if not ret:
            print("❌ Gagal membaca frame dari kamera!")
            img = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(img, "Gagal Membaca Frame", (120, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

    # Tambahkan timestamp pada gambar
    current_time = time.strftime("%Y-%m-%d %H:%M:%S")
    cv2.putText(img, current_time, (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
    
    _, buffer = cv2.imencode('.jpg', img)
    return buffer.tobytes()

# ==========================================
# MQTT CALLBACKS
# ==========================================
# Cek versi callback API untuk kompatibilitas paho-mqtt v2.x
try:
    CALLBACK_API_VERSION = mqtt.CallbackAPIVersion.VERSION2
except AttributeError:
    CALLBACK_API_VERSION = None

def on_connect_success(client):
    print(f"✅ IoT [{MAC_ADDRESS}] Terhubung ke Broker (WS)")
    
    # Subscribe ke perintah spesifik device ini
    client.subscribe(TOPIC_COMMAND)
    print(f"👂 Mendengarkan perintah di: {TOPIC_COMMAND}")
    
    # Subscribe ke status global dari Server Laravel
    client.subscribe(TOPIC_SERVER_STATUS)
    
    # Kirim status online dengan HMAC
    online_payload = {"status": "online", "mac_address": MAC_ADDRESS}
    online_payload["signature"] = generate_hmac_signature(online_payload)
    client.publish(TOPIC_STATUS, json.dumps(online_payload, separators=(',', ':')), qos=1, retain=True)
    print(f"📡 Status: ONLINE (Secured)")

if CALLBACK_API_VERSION is not None:
    def on_connect(client, userdata, flags, reason_code, properties):
        if reason_code == 0:
            on_connect_success(client)
        else:
            print(f"❌ Gagal terhubung, kode: {reason_code}")
else:
    def on_connect(client, userdata, flags, rc):
        if rc == 0:
            on_connect_success(client)
        else:
            print(f"❌ Gagal terhubung, kode: {rc}")

def on_message(client, userdata, msg):
    try:
        payload_str = msg.payload.decode('utf-8')
        payload = json.loads(payload_str)
        
        # EKSTRAK DAN VERIFIKASI SIGNATURE UNTUK SEMUA PESAN MASUK
        received_signature = payload.pop("signature", None)
        if not received_signature:
            print("⚠️ DITOLAK: Pesan tidak memiliki HMAC Signature keamanan.")
            return
            
        calculated_signature = generate_hmac_signature(payload)
        if not hmac.compare_digest(received_signature, calculated_signature):
            print("🚨 DITOLAK: Signature tidak valid! (Secret Key salah / Data diretas)")
            return

        # Jika pesan adalah status dari server
        if msg.topic == TOPIC_SERVER_STATUS:
            status = payload.get("status", "").upper()
            if status == "ONLINE":
                print(f"\n🌐 [SERVER LARAVEL]: {status} (Mengirim ulang status ONLINE...)")
                # Auto-respond: kirim ulang status online agar cache server terupdate
                # setelah server restart (cache direset ke offline saat boot)
                online_payload = {"status": "online", "mac_address": MAC_ADDRESS}
                online_payload["signature"] = generate_hmac_signature(online_payload)
                client.publish(TOPIC_STATUS, json.dumps(online_payload, separators=(',', ':')), qos=1, retain=True)
                print(f"📡 Status ONLINE dikirim ulang ke server.")
            else:
                print(f"\n⚠️ [SERVER LARAVEL]: {status} (Server sedang mati/restart!)")
            return

        # Selain itu, pasti payload command (snapshot/chat)
        print(f"\n📥 Menerima perintah AMAN dari server: {payload}")
        
        if payload.get("action") == "snapshot":
            process_snapshot_request(client)
        elif payload.get("action") == "connection_test":
            # Server meminta konfirmasi bahwa device masih hidup (setelah server restart)
            print("🏓 Connection test diterima dari server, mengirim ulang status ONLINE...")
            online_payload = {"status": "online", "mac_address": MAC_ADDRESS}
            online_payload["signature"] = generate_hmac_signature(online_payload)
            client.publish(TOPIC_STATUS, json.dumps(online_payload, separators=(',', ':')), qos=1, retain=True)
            print("📡 Status ONLINE dikirim sebagai respons connection test.")
        elif payload.get("action") == "chat":
            username = payload.get("username", "Admin")
            message = payload.get("message", "")
            print(f"💬 [LIVE CHAT] {username}: {message}")
            
    except Exception as e:
        print(f"⚠️ Error memproses pesan: {e}")

def chat_input_thread(client):
    """Thread terpisah untuk membaca input dari keyboard dan mengirimkannya via MQTT (Live Chat)"""
    import sys
    while True:
        try:
            msg = sys.stdin.readline()
            if msg:
                msg = msg.strip()
                if msg:
                    # Kirim pesan balasan ke Laravel (topic chat_reply)
                    payload = {
                        "username": "IoT Device",
                        "message": msg
                    }
                    payload["signature"] = generate_hmac_signature(payload)
                    client.publish(f"polislot/device/{MAC_ADDRESS}/chat_reply", json.dumps(payload, separators=(',', ':')), qos=1)
                    print("📤 [TERKIRIM AMAN] " + msg)
        except Exception as e:
            print(f"⚠️ Error pada thread input chat: {e}")
            break

def process_snapshot_request(client):
    # 1. Ambil gambar dari kamera
    image_bytes = capture_frame()
    
    # 2. Enkripsi gambar menggunakan AES-256-CBC
    print("🔒 Mengenkripsi gambar dengan AES-256-CBC...")
    iv_b64, encrypted_b64 = encrypt_image_aes(image_bytes)
    
    # 3. Siapkan payload dasar
    timestamp = int(time.time())
    payload_base = {
        "mac_address": MAC_ADDRESS,
        "timestamp": timestamp,
        "encrypted_image": encrypted_b64,
        "iv": iv_b64
    }
    
    # 4. Generate HMAC Signature dari payload dasar
    print("🔏 Membuat HMAC-SHA256 Signature...")
    signature = generate_hmac_signature(payload_base)
    
    # 5. Gabungkan signature ke dalam payload final
    payload_base["signature"] = signature
    
    # 6. Kirim ke Server
    print("📤 Mengirim gambar terenkripsi ke server...")
    client.publish(TOPIC_SNAPSHOT, json.dumps(payload_base), qos=1)
    print("✅ Berhasil dikirim!\n" + "-"*40)


# ==========================================
# MAIN LOOP
# ==========================================
# Kompatibilitas inisialisasi Client untuk paho-mqtt versi 1.x dan 2.x
if CALLBACK_API_VERSION is not None:
    client = mqtt.Client(CALLBACK_API_VERSION, transport="websockets")
else:
    client = mqtt.Client(transport="websockets")

# === KONFIGURASI LAST WILL AND TESTAMENT (LWT) AMAN ===
# Kita siapkan wasiat (LWT) dalam format JSON beserta signature-nya.
offline_payload = {"status": "offline", "mac_address": MAC_ADDRESS}
offline_payload["signature"] = generate_hmac_signature(offline_payload)
client.will_set(TOPIC_STATUS, payload=json.dumps(offline_payload, separators=(',', ':')), qos=1, retain=True)

# Wajib menambahkan TLS jika menggunakan Cloudflare (Port 443 / wss://)
if PORT == 443:
    import ssl
    client.tls_set(cert_reqs=ssl.CERT_NONE) # Memastikan enkripsi diaktifkan untuk koneksi WSS

# Set credentials for MQTT broker authentication
if MQTT_USER and MQTT_PASSWORD:
    client.username_pw_set(MQTT_USER, MQTT_PASSWORD)

client.on_connect = on_connect
client.on_message = on_message

print(f"Menghubungkan ke ws://{BROKER}:{PORT} ...")
client.connect(BROKER, PORT, 60)

# Jalankan thread untuk fitur Live Chat dari terminal
threading.Thread(target=chat_input_thread, args=(client,), daemon=True).start()

# Loop selamanya, menunggu perintah dari server
try:
    print("Ketik sesuatu lalu tekan Enter untuk mengirim pesan Live Chat ke Web Admin.")
    client.loop_forever()
except KeyboardInterrupt:
    print("\n🛑 Dihentikan oleh user.")
    # Karena kita melakukan disconnect secara "bersih" (graceful),
    # Broker TIDAK akan mengirimkan Last Will. Jadi kita harus kirim manual.
    offline_payload = {"status": "offline", "mac_address": MAC_ADDRESS}
    offline_payload["signature"] = generate_hmac_signature(offline_payload)
    client.publish(TOPIC_STATUS, json.dumps(offline_payload, separators=(',', ':')), qos=1, retain=True)
    
    # Beri waktu sedikit agar pesan offline terkirim sebelum koneksi diputus
    import time
    time.sleep(0.5) 
    
    client.disconnect()
