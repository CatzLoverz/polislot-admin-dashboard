import requests
import time
import hmac
import hashlib

# ============================================================
# KONFIGURASI
# ============================================================
API_URL = ""
MAC_ADDRESS = ""

# Shared secret — HARUS SAMA dengan IOT_API_SECRET di .env server
IOT_API_SECRET = ""

def sign_request(mac_address, timestamp, frame):
    """
    Membuat HMAC-SHA256 signature.
    Data yang di-sign: mac_address:timestamp:panjang_frame
    """
    frame_length = len(frame)
    data_to_sign = f"{mac_address}:{timestamp}:{frame_length}"
    return hmac.new(
        IOT_API_SECRET.encode('utf-8'),
        data_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()


# === Pengaturan Reconnect ===
MAX_RECONNECT_ATTEMPTS = 5
RECONNECT_DELAY = 10  # detik

def send_telemetry():
    """
    Mengirim data telemetri ke server dengan HMAC signature.
    Menangani penolakan (403/401) dengan retry logic dan graceful exit.
    """
    print(f"Starting telemetry sender to {API_URL}...")
    print(f"Device MAC: {MAC_ADDRESS}")
    print(f"Security: HMAC-SHA256 enabled")
    
    session = requests.Session()
    counter = 1
    reconnect_attempts = 0
    
    while True:
        simulated_data = f"Hello from IoT Device! Message number: {counter}"
        timestamp = int(time.time())
        signature = sign_request(MAC_ADDRESS, timestamp, simulated_data)
        
        payload = {
            "mac_address": MAC_ADDRESS,
            "frame": simulated_data,
            "timestamp": timestamp,
            "signature": signature,
        }
        
        try:
            print(f"Sending data to server: {simulated_data}")
            response = session.post(API_URL, json=payload, timeout=10)
            
            if response.status_code == 200:
                print(f"Success: {response.json()}")
                reconnect_attempts = 0  # Reset pada sukses
            elif response.status_code == 403:
                # Device TIDAK terdaftar di server — langsung berhenti.
                print(f"\n{'='*60}")
                print(f"[FATAL] Device MAC '{MAC_ADDRESS}' TIDAK TERDAFTAR di server!")
                print(f"Server menolak stream dengan status 403 Forbidden.")
                print(f"Pastikan MAC address sudah didaftarkan di panel admin.")
                print(f"{'='*60}\n")
                break
            elif response.status_code == 401:
                print(f"[DITOLAK] Signature invalid atau request expired!")
                reconnect_attempts += 1
                if reconnect_attempts < MAX_RECONNECT_ATTEMPTS:
                    print(f"Mencoba lagi dalam {RECONNECT_DELAY} detik... ({reconnect_attempts}/{MAX_RECONNECT_ATTEMPTS})")
                    time.sleep(RECONNECT_DELAY)
                    session.close()
                    session = requests.Session()
                    continue
                else:
                    print("Gagal terhubung setelah beberapa percobaan. Keluar.")
                    break
            else:
                print(f"Failed with status {response.status_code}: {response.text}")
                
        except requests.exceptions.RequestException as e:
            print(f"Error connecting to API: {e}")
            
        counter += 1
        time.sleep(3)
    
    session.close()
    print("Sender dihentikan.")

if __name__ == "__main__":
    send_telemetry()
