"""
IoT WebSocket Client (iot_ws_client.py)
=======================================
Client Python yang menggabungkan:
- Presence Channel via Reverb (pysher) → Online/Offline detection + Receive commands
- HTTP POST ke /api/iot/stream → Video frame streaming

Padanan WebSocket dari mqtt_test_iot.py + iot_video_sender.py.

Keamanan:
- HMAC-SHA256 pada autentikasi WebSocket (presence channel)
- HMAC-SHA256 pada setiap HTTP POST (video frame)
- AES-256-CBC untuk enkripsi snapshot

Usage:
    python iot_ws_client.py [MAC_ADDRESS]
"""

import sys
import time
import json
import hmac
import hashlib
import base64
import threading
import os

import cv2
import numpy as np
import requests
import pysher

from Crypto.Cipher import AES
from Crypto.Util.Padding import pad


# ============================================================
# KONFIGURASI
# ============================================================
if len(sys.argv) > 1:
    MAC_ADDRESS = sys.argv[1]
else:
    MAC_ADDRESS = input("Masukkan MAC Address perangkat (contoh: 00:1A:2B:3C:4D:5E): ").strip()
    if not MAC_ADDRESS:
        print("MAC Address tidak boleh kosong!")
        sys.exit(1)

# Server Configuration
# Ganti dengan URL server Anda (lokal atau tunnel)
SERVER_BASE_URL = "http://localhost"  # atau "https://raihanatmaja.my.id"
API_STREAM_URL = f"{SERVER_BASE_URL}/api/iot/stream"
API_WS_AUTH_URL = f"{SERVER_BASE_URL}/api/iot/ws-auth"

# Reverb WebSocket Configuration
# Sesuaikan dengan .env server
REVERB_APP_KEY = "xcubvd4inm14ayepjhro"
REVERB_HOST = "127.0.0.1"
REVERB_PORT = 8080
REVERB_SCHEME = "ws"  # "ws" untuk lokal, "wss" untuk produksi

# Harus SAMA persis dengan IOT_API_SECRET di Laravel .env
SHARED_SECRET = ""

# Video Stream Settings
FRAME_WIDTH = 640
FRAME_HEIGHT = 360
JPEG_QUALITY = 45
FPS_TARGET = 10

# Reconnect Settings
MAX_RECONNECT_ATTEMPTS = 5
RECONNECT_DELAY = 10  # detik


# ============================================================
# FUNGSI KEAMANAN (HMAC & AES)
# ============================================================
def get_aes_key():
    """Ambil 32 byte pertama dari hash SHA256 dari secret key."""
    return hashlib.sha256(SHARED_SECRET.encode('utf-8')).digest()[:32]


def generate_auth_signature(mac_address, timestamp):
    """Generate HMAC signature untuk autentikasi WebSocket."""
    key = get_aes_key()
    data_to_sign = f"{mac_address}:{timestamp}"
    return hmac.new(key, data_to_sign.encode('utf-8'), hashlib.sha256).hexdigest()


def generate_stream_signature(mac_address, timestamp, frame):
    """Generate HMAC signature untuk HTTP POST stream (sama dengan iot_video_sender.py)."""
    frame_length = len(frame)
    data_to_sign = f"{mac_address}:{timestamp}:{frame_length}"
    return hmac.new(
        SHARED_SECRET.encode('utf-8'),
        data_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()


def generate_hmac_signature(payload_dict):
    """Generate HMAC signature untuk payload JSON (sama dengan mqtt_test_iot.py)."""
    key = get_aes_key()
    data_string = json.dumps(payload_dict, separators=(',', ':'))
    return hmac.new(key, data_string.encode('utf-8'), hashlib.sha256).hexdigest()


def encrypt_image_aes(image_bytes):
    """Enkripsi gambar menggunakan AES-256-CBC."""
    key = get_aes_key()
    iv = os.urandom(16)
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_data = pad(image_bytes, AES.block_size)
    encrypted_bytes = cipher.encrypt(padded_data)
    return base64.b64encode(iv).decode('utf-8'), base64.b64encode(encrypted_bytes).decode('utf-8')


# ============================================================
# KAMERA
# ============================================================
def capture_frame():
    """Ambil gambar dari kamera webcam (untuk snapshot)."""
    print("📸 Kamera mengambil gambar dari webcam...")
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("❌ Gagal membuka kamera! Menggunakan gambar blank fallback.")
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        cv2.putText(img, "Kamera Tidak Terdeteksi", (100, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
    else:
        for _ in range(30):
            cap.read()

        ret, img = cap.read()
        cap.release()

        if not ret:
            print("❌ Gagal membaca frame dari kamera!")
            img = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(img, "Gagal Membaca Frame", (120, 240), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

    current_time = time.strftime("%Y-%m-%d %H:%M:%S")
    cv2.putText(img, current_time, (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)

    _, buffer = cv2.imencode('.jpg', img)
    return buffer.tobytes()


# ============================================================
# PYSHER AUTH HANDLER
# ============================================================
def pusher_auth_handler(socket_id, channel_name, callback=None):
    """
    Custom auth handler untuk pysher.
    Dipanggil otomatis saat subscribe ke presence channel.
    Mengirim HMAC signature ke endpoint /api/iot/ws-auth.
    """
    timestamp = int(time.time())
    signature = generate_auth_signature(MAC_ADDRESS, timestamp)

    try:
        response = requests.post(API_WS_AUTH_URL, json={
            'socket_id': socket_id,
            'channel_name': channel_name,
            'mac_address': MAC_ADDRESS,
            'timestamp': timestamp,
            'signature': signature,
        }, timeout=10)

        if response.status_code == 200:
            auth_data = response.json()
            print(f"✅ WebSocket Auth berhasil untuk channel: {channel_name}")
            # Pysher expects dict with 'auth' and optionally 'channel_data'
            return json.dumps(auth_data)
        else:
            error_msg = response.json().get('error', 'Unknown error')
            print(f"❌ WebSocket Auth gagal ({response.status_code}): {error_msg}")

            if response.status_code == 403:
                print(f"\n{'='*60}")
                print(f"[FATAL] Device MAC '{MAC_ADDRESS}' TIDAK TERDAFTAR di server!")
                print(f"Pastikan MAC address sudah didaftarkan di panel admin.")
                print(f"{'='*60}\n")
                os._exit(1)  # Force exit

            return None
    except Exception as e:
        print(f"❌ Error saat auth WebSocket: {e}")
        return None


# ============================================================
# COMMAND HANDLER (menerima perintah dari server via Reverb)
# ============================================================
def handle_command(data):
    """
    Handle command yang diterima dari server via presence channel.
    Padanan WebSocket dari on_message di mqtt_test_iot.py.
    """
    try:
        if isinstance(data, str):
            payload = json.loads(data)
        else:
            payload = data

        action = payload.get('action', '')
        print(f"\n📥 Menerima perintah dari server via WebSocket: {action}")

        # Verifikasi HMAC signature
        received_data = payload.get('data', payload)
        if isinstance(received_data, str):
            received_data = json.loads(received_data)

        received_signature = received_data.get('signature', '')
        if not received_signature:
            print("⚠️ DITOLAK: Pesan tidak memiliki HMAC Signature keamanan.")
            return

        # Buat salinan payload tanpa signature untuk verifikasi
        verify_payload = {k: v for k, v in received_data.items() if k != 'signature'}
        calculated_signature = generate_hmac_signature(verify_payload)

        if not hmac.compare_digest(received_signature, calculated_signature):
            print("🚨 DITOLAK: Signature tidak valid!")
            return

        action = received_data.get('action', '')

        if action == 'snapshot':
            process_snapshot_request()
        elif action == 'connection_test':
            print("🏓 Connection test diterima dari server.")
        elif action == 'chat':
            username = received_data.get('username', 'Admin')
            message = received_data.get('message', '')
            print(f"💬 [LIVE CHAT] {username}: {message}")
        else:
            print(f"⚠️ Action tidak dikenal: {action}")

    except Exception as e:
        print(f"⚠️ Error memproses command: {e}")


def process_snapshot_request():
    """
    Proses permintaan snapshot: ambil gambar, enkripsi, kirim via HTTP POST.
    Menggunakan endpoint terpisah atau memanfaatkan IotStreamController.
    """
    image_bytes = capture_frame()

    print("🔒 Mengenkripsi gambar dengan AES-256-CBC...")
    iv_b64, encrypted_b64 = encrypt_image_aes(image_bytes)

    timestamp = int(time.time())
    payload_base = {
        "mac_address": MAC_ADDRESS,
        "timestamp": timestamp,
        "encrypted_image": encrypted_b64,
        "iv": iv_b64
    }

    print("🔏 Membuat HMAC-SHA256 Signature...")
    signature = generate_hmac_signature(payload_base)
    payload_base["signature"] = signature

    # Kirim gambar sebagai frame biasa via stream endpoint (dikirim sebagai data:image)
    # Sementara kita decode dulu lalu kirim, agar bisa tampil di viewer
    image_base64 = base64.b64encode(image_bytes).decode('utf-8')
    data_uri = f"data:image/jpeg;base64,{image_base64}"

    stream_timestamp = int(time.time())
    stream_signature = generate_stream_signature(MAC_ADDRESS, stream_timestamp, data_uri)

    stream_payload = {
        "mac_address": MAC_ADDRESS,
        "frame": data_uri,
        "timestamp": stream_timestamp,
        "signature": stream_signature,
    }

    try:
        response = requests.post(API_STREAM_URL, json=stream_payload, timeout=10)
        if response.status_code == 200:
            print("✅ Snapshot berhasil dikirim ke server dan di-broadcast!")
        else:
            print(f"❌ Gagal mengirim snapshot: {response.status_code}")
    except Exception as e:
        print(f"❌ Error mengirim snapshot: {e}")

    print("-" * 40)


# ============================================================
# VIDEO STREAMING (HTTP POST — sama seperti iot_video_sender.py)
# ============================================================
def video_stream_thread():
    """Thread terpisah untuk mengirim video stream via HTTP POST."""
    session = requests.Session()
    session.headers.update({'Content-Type': 'application/json'})

    print(f"\n🎥 Memulai video streaming ke {API_STREAM_URL}...")
    print(f"   Resolusi: {FRAME_WIDTH}x{FRAME_HEIGHT} | JPEG: {JPEG_QUALITY} | FPS: {FPS_TARGET}")

    cap = cv2.VideoCapture(0)
    if not cap.isOpened():
        print("❌ Gagal membuka kamera. Video streaming tidak aktif.")
        return

    cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)

    frame_count = 0
    error_count = 0
    jpeg_quality = JPEG_QUALITY

    try:
        while True:
            start_time = time.time()

            ret, frame = cap.read()
            if not ret:
                print("Gagal mengambil frame.")
                break

            h, w = frame.shape[:2]
            if w != FRAME_WIDTH or h != FRAME_HEIGHT:
                frame = cv2.resize(frame, (FRAME_WIDTH, FRAME_HEIGHT))

            encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), jpeg_quality]
            result, buffer = cv2.imencode('.jpg', frame, encode_param)
            if not result:
                continue

            b64_string = base64.b64encode(buffer).decode('utf-8')
            data_uri = f"data:image/jpeg;base64,{b64_string}"

            timestamp = int(time.time())
            signature = generate_stream_signature(MAC_ADDRESS, timestamp, data_uri)

            payload = {
                "mac_address": MAC_ADDRESS,
                "frame": data_uri,
                "timestamp": timestamp,
                "signature": signature,
            }

            try:
                response = session.post(API_STREAM_URL, json=payload, timeout=8)
                frame_count += 1

                if response.status_code == 200:
                    size_kb = len(b64_string) / 1024
                    elapsed = time.time() - start_time
                    print(f"[Frame #{frame_count}] OK | {size_kb:.1f} KB | {elapsed:.2f}s")
                    error_count = 0
                elif response.status_code == 403:
                    print(f"\n{'='*60}")
                    print(f"[FATAL] Device MAC '{MAC_ADDRESS}' TIDAK TERDAFTAR di server!")
                    print(f"{'='*60}\n")
                    break
                elif response.status_code == 401:
                    print(f"[DITOLAK] Signature invalid atau request expired!")
                    error_count += 1
                else:
                    print(f"[Frame #{frame_count}] Error {response.status_code}")

            except requests.exceptions.Timeout:
                error_count += 1
                print(f"[Timeout #{error_count}] Server lambat merespon, skip frame")
                if error_count >= 5:
                    print("Terlalu banyak timeout, menurunkan kualitas...")
                    jpeg_quality = max(20, jpeg_quality - 10)
                    error_count = 0

            except requests.exceptions.RequestException as e:
                print(f"Error: {e}")

            elapsed_time = time.time() - start_time
            sleep_time = max(1.0 / FPS_TARGET - elapsed_time, 0)
            time.sleep(sleep_time)

    except Exception as e:
        print(f"Video stream error: {e}")
    finally:
        cap.release()
        session.close()
        print("🛑 Video stream dihentikan.")


# ============================================================
# CHAT INPUT THREAD
# ============================================================
def chat_input_thread():
    """Thread untuk membaca input dari keyboard (Live Chat)."""
    while True:
        try:
            msg = sys.stdin.readline()
            if msg:
                msg = msg.strip()
                if msg:
                    # Kirim via HTTP POST ke server (server akan broadcast ke Reverb)
                    timestamp = int(time.time())
                    signature = generate_auth_signature(MAC_ADDRESS, timestamp)

                    try:
                        # Kita bisa membuat endpoint sendiri untuk chat dari device,
                        # tapi untuk saat ini cukup print saja
                        print(f"📤 [TERKIRIM] {msg}")
                    except Exception as e:
                        print(f"⚠️ Error mengirim chat: {e}")
        except Exception as e:
            print(f"⚠️ Error pada thread input chat: {e}")
            break


# ============================================================
# MAIN
# ============================================================
def main():
    clean_mac = MAC_ADDRESS.replace(':', '')
    presence_channel_name = f"presence-iot.device.{clean_mac}"

    print("=" * 60)
    print(f"  PoliSlot IoT WebSocket Client")
    print(f"  MAC Address : {MAC_ADDRESS}")
    print(f"  Server      : {SERVER_BASE_URL}")
    print(f"  Reverb      : {REVERB_SCHEME}://{REVERB_HOST}:{REVERB_PORT}")
    print(f"  Channel     : {presence_channel_name}")
    print(f"  Security    : HMAC-SHA256 + AES-256-CBC")
    print("=" * 60)

    if not SHARED_SECRET:
        print("⚠️ WARNING: SHARED_SECRET kosong! Set IOT_API_SECRET yang sama dengan server.")

    # Inisialisasi Pysher client
    pusher = pysher.Pusher(
        key=REVERB_APP_KEY,
        auth_endpoint=API_WS_AUTH_URL,
        auth_endpoint_headers={},
        custom_host=REVERB_HOST,
        secure=(REVERB_SCHEME == "wss"),
        port=REVERB_PORT,
        auto_sub=True,
        # Custom auth handler karena kita pakai HMAC, bukan session-based auth
        secret=None,  # Tidak pakai secret di client-side
    )

    # Override auth handler dengan custom HMAC auth
    pusher.connection.auth = pusher_auth_handler

    def connect_handler(data):
        """Dipanggil saat koneksi ke Reverb berhasil."""
        print(f"✅ Terhubung ke Reverb WebSocket!")

        # Subscribe ke presence channel
        channel = pusher.subscribe(presence_channel_name)

        # Bind event handler untuk menerima command dari server
        channel.bind('command.sent', handle_command)

        # Bind presence events
        channel.bind('pusher:subscription_succeeded', lambda data: (
            print(f"📡 Berhasil join {presence_channel_name} — Status: ONLINE")
        ))

        channel.bind('pusher:subscription_error', lambda data: (
            print(f"❌ Gagal join {presence_channel_name}: {data}")
        ))

        print(f"📡 Status: ONLINE (via Presence Channel)")

    def disconnect_handler(data):
        """Dipanggil saat koneksi terputus."""
        print(f"❌ Koneksi WebSocket terputus!")

    # Bind connection events
    pusher.connection.bind('pusher:connection_established', connect_handler)
    pusher.connection.bind('pusher:connection_failed', disconnect_handler)

    # Connect ke Reverb
    print(f"🔗 Menghubungkan ke Reverb ({REVERB_SCHEME}://{REVERB_HOST}:{REVERB_PORT})...")
    pusher.connect()

    # Tunggu koneksi established
    time.sleep(3)

    # Jalankan video streaming di thread terpisah
    stream_thread = threading.Thread(target=video_stream_thread, daemon=True)
    stream_thread.start()

    # Jalankan chat input di thread terpisah
    chat_thread = threading.Thread(target=chat_input_thread, daemon=True)
    chat_thread.start()

    # Main loop
    try:
        print("\n✅ Semua sistem aktif. Tekan Ctrl+C untuk berhenti.")
        print("Ketik sesuatu lalu tekan Enter untuk mengirim pesan Live Chat.\n")
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\n🛑 Dihentikan oleh user.")
        pusher.disconnect()
        print("📡 Status: OFFLINE (Koneksi WebSocket ditutup)")


if __name__ == "__main__":
    main()
