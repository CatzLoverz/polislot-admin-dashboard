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

# ==========================================
# KONFIGURASI IOT DEVICE
# ==========================================
MAC_ADDRESS = "TEST-MAC-01"
BROKER = "127.0.0.1"
PORT = 9001  # WebSockets
TOPIC_COMMAND = f"polislot/device/{MAC_ADDRESS}/command"
TOPIC_SNAPSHOT = f"polislot/device/{MAC_ADDRESS}/snapshot"

# Harus SAMA persis dengan IOT_API_SECRET di Laravel .env
# Catatan: Untuk AES-256, panjang karakter bebas karena di-hash lagi ke 32 byte menggunakan SHA256
SHARED_SECRET = "pOl1sL0t_ioT_s3creT_k3y_2026" 

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
# FUNGSI KAMERA (DUMMY)
# ==========================================
def capture_frame():
    print("📸 Kamera mengambil gambar...")
    img = np.zeros((480, 640, 3), dtype=np.uint8)
    # Tambahkan teks dengan timestamp agar terlihat real-time
    current_time = time.strftime("%Y-%m-%d %H:%M:%S")
    cv2.putText(img, "LIVE IOT SNAPSHOT", (120, 200), cv2.FONT_HERSHEY_SIMPLEX, 1.2, (0, 255, 0), 3)
    cv2.putText(img, current_time, (160, 260), cv2.FONT_HERSHEY_SIMPLEX, 1, (255, 255, 255), 2)
    
    _, buffer = cv2.imencode('.jpg', img)
    return buffer.tobytes()

# ==========================================
# MQTT CALLBACKS
# ==========================================
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print(f"✅ IoT [{MAC_ADDRESS}] Terhubung ke Broker (WS)")
        client.subscribe(TOPIC_COMMAND)
        print(f"👂 Mendengarkan perintah di: {TOPIC_COMMAND}")
    else:
        print(f"❌ Gagal terhubung, kode: {rc}")

def on_message(client, userdata, msg):
    try:
        payload = json.loads(msg.payload.decode('utf-8'))
        print(f"\n📥 Menerima perintah dari server: {payload}")
        
        if payload.get("action") == "snapshot":
            process_snapshot_request(client)
            
    except Exception as e:
        print(f"⚠️ Error memproses pesan: {e}")

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
client = mqtt.Client(transport="websockets")
client.on_connect = on_connect
client.on_message = on_message

print(f"Menghubungkan ke ws://{BROKER}:{PORT} ...")
client.connect(BROKER, PORT, 60)

# Loop selamanya, menunggu perintah dari server
try:
    client.loop_forever()
except KeyboardInterrupt:
    print("\n🛑 Dihentikan oleh user.")
    client.disconnect()
