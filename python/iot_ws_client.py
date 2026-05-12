"""
IoT WebSocket Client (iot_ws_client.py)
=======================================
Padanan WebSocket dari mqtt_test_iot.py.

Fitur:
- Presence Channel via Reverb → Instant Online/Offline detection
- Menerima perintah dari server (snapshot, chat, connection_test)
- Mengirim snapshot terenkripsi (AES-256-CBC) via HTTP POST
- Live Chat bidirectional (PoC)

Keamanan:
- HMAC-SHA256 pada autentikasi WebSocket
- HMAC-SHA256 pada setiap HTTP POST
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
import asyncio
import os

import cv2
import numpy as np
import requests
import websockets

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
SERVER_BASE_URL = "https://raihanatmaja.my.id" # Ganti sesuai tunnel Anda
API_WS_AUTH_URL  = f"{SERVER_BASE_URL}/api/iot/ws-auth"
API_SNAPSHOT_URL = f"{SERVER_BASE_URL}/api/iot/snapshot"
API_CHAT_URL     = f"{SERVER_BASE_URL}/api/iot/chat-reply"

# Reverb WebSocket — sesuaikan dengan .env server
REVERB_APP_KEY = "xcubvd4inm14ayepjhro"
REVERB_HOST    = "raihanatmaja.my.id" # HANYA domain, tanpa protocol
REVERB_PORT    = 443                  # 443 untuk WSS tunnel
REVERB_SCHEME  = "wss"                # wss untuk tunnel/produksi

# Shared secret — HARUS SAMA dengan IOT_API_SECRET di Laravel .env
SHARED_SECRET = ""

# Reconnect
MAX_RECONNECT_ATTEMPTS = 10
RECONNECT_DELAY = 5


# ============================================================
# FUNGSI KEAMANAN (HMAC & AES)
# ============================================================
def get_aes_key():
    return hashlib.sha256(SHARED_SECRET.encode('utf-8')).digest()[:32]

def generate_auth_signature(mac_address, timestamp):
    """HMAC untuk autentikasi WebSocket (mac:timestamp)."""
    key = get_aes_key()
    data = f"{mac_address}:{timestamp}"
    return hmac.new(key, data.encode('utf-8'), hashlib.sha256).hexdigest()

def generate_hmac_signature(payload_dict):
    """HMAC untuk payload JSON (format sama dengan mqtt_test_iot.py)."""
    key = get_aes_key()
    data_string = json.dumps(payload_dict, separators=(',', ':'))
    return hmac.new(key, data_string.encode('utf-8'), hashlib.sha256).hexdigest()

def encrypt_image_aes(image_bytes):
    """Enkripsi gambar dengan AES-256-CBC."""
    key = get_aes_key()
    iv = os.urandom(16)
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_data = pad(image_bytes, AES.block_size)
    encrypted_bytes = cipher.encrypt(padded_data)
    return base64.b64encode(iv).decode('utf-8'), base64.b64encode(encrypted_bytes).decode('utf-8')


# ============================================================
# KAMERA (untuk Snapshot)
# ============================================================
def capture_frame():
    print("📸 Kamera mengambil gambar dari webcam...")
    cap = cv2.VideoCapture(0)

    if not cap.isOpened():
        print("❌ Gagal membuka kamera! Menggunakan gambar blank fallback.")
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        cv2.putText(img, "Kamera Tidak Terdeteksi", (100, 240),
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
    else:
        # Pemanasan sensor kamera
        for _ in range(30):
            cap.read()
        ret, img = cap.read()
        cap.release()
        if not ret:
            print("❌ Gagal membaca frame dari kamera!")
            img = np.zeros((480, 640, 3), dtype=np.uint8)
            cv2.putText(img, "Gagal Membaca Frame", (120, 240),
                        cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

    current_time = time.strftime("%Y-%m-%d %H:%M:%S")
    cv2.putText(img, current_time, (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 255, 0), 2)
    _, buffer = cv2.imencode('.jpg', img)
    return buffer.tobytes()


# ============================================================
# COMMAND HANDLER
# ============================================================
def handle_command(raw_data):
    """Handle command yang diterima dari server via Reverb presence channel."""
    try:
        payload = json.loads(raw_data) if isinstance(raw_data, str) else raw_data

        # Data command ada di field 'data' dari event atau langsung di payload
        cmd_data = payload.get('data', payload)
        if isinstance(cmd_data, str):
            cmd_data = json.loads(cmd_data)

        action = cmd_data.get('action', payload.get('action', ''))
        print(f"\n📥 Menerima perintah dari server via WebSocket: {action}")

        # Verifikasi HMAC signature
        received_signature = cmd_data.get('signature', '')
        if not received_signature:
            print("⚠️ DITOLAK: Pesan tidak memiliki HMAC Signature.")
            return

        verify_data = {k: v for k, v in cmd_data.items() if k != 'signature'}
        calculated = generate_hmac_signature(verify_data)
        if not hmac.compare_digest(received_signature, calculated):
            print("🚨 DITOLAK: Signature tidak valid!")
            return

        if action == 'snapshot':
            process_snapshot()
        elif action == 'connection_test':
            print("🏓 Connection test diterima dari server.")
        elif action == 'chat':
            username = cmd_data.get('username', 'Admin')
            message = cmd_data.get('message', '')
            print(f"💬 [LIVE CHAT] {username}: {message}")
        else:
            print(f"⚠️ Action tidak dikenal: {action}")
    except Exception as e:
        print(f"⚠️ Error memproses command: {e}")


def process_snapshot():
    """Ambil gambar, enkripsi AES, kirim via HTTP POST ke /api/iot/snapshot."""
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

    print("📤 Mengirim gambar terenkripsi ke server...")
    try:
        resp = requests.post(API_SNAPSHOT_URL, json=payload_base, timeout=15)
        if resp.status_code == 200:
            print("✅ Snapshot berhasil dikirim dan di-broadcast!")
        else:
            print(f"❌ Gagal mengirim snapshot: {resp.status_code} — {resp.text}")
    except Exception as e:
        print(f"❌ Error mengirim snapshot: {e}")
    print("-" * 40)


def send_chat_message(message):
    """Kirim pesan chat dari terminal ke server via HTTP POST."""
    payload_base = {
        "username": "IoT Device",
        "message": message
    }
    signature = generate_hmac_signature(payload_base)

    try:
        resp = requests.post(API_CHAT_URL, json={
            "mac_address": MAC_ADDRESS,
            "username": "IoT Device",
            "message": message,
            "signature": signature,
        }, timeout=10)
        if resp.status_code == 200:
            print(f"📤 [TERKIRIM AMAN] {message}")
        else:
            print(f"❌ Chat gagal: {resp.status_code}")
    except Exception as e:
        print(f"❌ Error chat: {e}")


# ============================================================
# WEBSOCKET (Pusher Protocol → Reverb)
# ============================================================
async def websocket_client():
    """Koneksi WebSocket ke Reverb menggunakan Pusher protocol secara manual."""
    clean_mac = MAC_ADDRESS.replace(':', '')
    channel_name = f"presence-iot.device.{clean_mac}"
    ws_uri = f"{REVERB_SCHEME}://{REVERB_HOST}:{REVERB_PORT}/app/{REVERB_APP_KEY}?protocol=7&client=python&version=1.0"

    reconnect_count = 0

    while reconnect_count < MAX_RECONNECT_ATTEMPTS:
        try:
            print(f"\n🔗 Menghubungkan ke Reverb ({ws_uri})...")

            async with websockets.connect(ws_uri, ping_interval=25, ping_timeout=10) as ws:
                reconnect_count = 0  # Reset on success

                # ── 1. Tunggu connection_established ──
                raw = await ws.recv()
                msg = json.loads(raw)
                if msg.get('event') != 'pusher:connection_established':
                    print(f"❌ Unexpected event: {msg.get('event')}")
                    continue

                conn_data = json.loads(msg['data'])
                socket_id = conn_data['socket_id']
                print(f"✅ Terhubung ke Reverb! socket_id: {socket_id}")

                # ── 2. Auth via HTTP POST ke /api/iot/ws-auth ──
                timestamp = int(time.time())
                signature = generate_auth_signature(MAC_ADDRESS, timestamp)

                # Memastikan URL memiliki scheme https://
                auth_url = API_WS_AUTH_URL
                if not auth_url.startswith('http'):
                    auth_url = f"https://{auth_url}"

                auth_resp = requests.post(auth_url, json={
                    'socket_id': socket_id,
                    'channel_name': channel_name,
                    'mac_address': MAC_ADDRESS,
                    'timestamp': timestamp,
                    'signature': signature,
                }, timeout=10)

                if auth_resp.status_code == 403:
                    print(f"\n{'='*60}")
                    print(f"[FATAL] Device '{MAC_ADDRESS}' TIDAK TERDAFTAR di server!")
                    print(f"{'='*60}")
                    return  # Stop entirely
                elif auth_resp.status_code != 200:
                    print(f"❌ Auth gagal ({auth_resp.status_code}): {auth_resp.text}")
                    break

                auth_data = auth_resp.json()
                print(f"✅ WebSocket Auth berhasil")

                # ── 3. Subscribe ke Presence Channel ──
                subscribe_msg = {
                    "event": "pusher:subscribe",
                    "data": {
                        "auth": auth_data['auth'],
                        "channel": channel_name,
                        "channel_data": auth_data['channel_data']
                    }
                }
                await ws.send(json.dumps(subscribe_msg))
                print(f"📡 Subscribing ke {channel_name}...")

                # ── 4. Jalankan chat input di thread terpisah ──
                loop = asyncio.get_event_loop()
                chat_running = True

                def chat_input_loop():
                    while chat_running:
                        try:
                            line = sys.stdin.readline()
                            if line:
                                line = line.strip()
                                if line:
                                    send_chat_message(line)
                        except Exception:
                            break

                import threading
                chat_thread = threading.Thread(target=chat_input_loop, daemon=True)
                chat_thread.start()

                # ── 5. Listen for events ──
                async for raw_msg in ws:
                    msg = json.loads(raw_msg)
                    event = msg.get('event', '')
                    channel = msg.get('channel', '')

                    if event == 'pusher:ping':
                        await ws.send(json.dumps({"event": "pusher:pong"}))

                    elif event == 'pusher_internal:subscription_succeeded':
                        print(f"📡 Berhasil join {channel_name} — Status: ONLINE")
                        print(f"Ketik sesuatu lalu tekan Enter untuk mengirim pesan Live Chat.\n")

                    elif event == 'pusher:error':
                        err_data = json.loads(msg.get('data', '{}'))
                        print(f"❌ Pusher error: {err_data}")

                    elif event == 'command.sent':
                        handle_command(msg.get('data', '{}'))

                    elif event == 'pusher_internal:member_added':
                        member = json.loads(msg.get('data', '{}'))
                        print(f"👋 Member bergabung: {member}")

                    elif event == 'pusher_internal:member_removed':
                        member = json.loads(msg.get('data', '{}'))
                        print(f"👋 Member keluar: {member}")

                    else:
                        # Log event lain untuk debugging
                        if event and not event.startswith('pusher'):
                            print(f"📨 Event: {event} | Data: {msg.get('data', '')[:100]}")

                # Jika loop selesai (koneksi ditutup dari server)
                chat_running = False
                print("⚠️ Koneksi WebSocket ditutup oleh server.")

        except websockets.exceptions.ConnectionClosedError as e:
            print(f"❌ Koneksi terputus: {e}")
        except websockets.exceptions.InvalidStatus as e:
            print(f"❌ Gagal connect (HTTP {e.response.status_code})")
        except ConnectionRefusedError:
            print(f"❌ Server menolak koneksi ({REVERB_HOST}:{REVERB_PORT})")
        except Exception as e:
            print(f"❌ Error: {e}")

        reconnect_count += 1
        if reconnect_count < MAX_RECONNECT_ATTEMPTS:
            print(f"🔄 Reconnect dalam {RECONNECT_DELAY}s... ({reconnect_count}/{MAX_RECONNECT_ATTEMPTS})")
            await asyncio.sleep(RECONNECT_DELAY)

    print("🛑 Gagal terhubung setelah beberapa percobaan.")


# ============================================================
# MAIN
# ============================================================
def main():
    clean_mac = MAC_ADDRESS.replace(':', '')

    print("=" * 60)
    print(f"  PoliSlot IoT WebSocket Client")
    print(f"  MAC Address : {MAC_ADDRESS}")
    print(f"  Server      : {SERVER_BASE_URL}")
    print(f"  Reverb      : {REVERB_SCHEME}://{REVERB_HOST}:{REVERB_PORT}")
    print(f"  Channel     : presence-iot.device.{clean_mac}")
    print(f"  Security    : HMAC-SHA256 + AES-256-CBC")
    print("=" * 60)

    if not SHARED_SECRET:
        print("⚠️  WARNING: SHARED_SECRET kosong! Set IOT_API_SECRET yang sama dengan server.")

    try:
        asyncio.run(websocket_client())
    except KeyboardInterrupt:
        print("\n🛑 Dihentikan oleh user.")
        print("📡 Status: OFFLINE (Koneksi WebSocket ditutup → Reverb mendeteksi)")


if __name__ == "__main__":
    main()
