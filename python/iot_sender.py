import requests
import time
import hmac
import hashlib

# ============================================================
# KONFIGURASI
# ============================================================
API_URL = "https://raihanatmaja.my.id/api/iot/stream"
MAC_ADDRESS = "00:1A:2B:3C:4D:5E"

# Shared secret — HARUS SAMA dengan IOT_API_SECRET di .env server
IOT_API_SECRET = "pOl1sL0t_ioT_s3creT_k3y_2026"

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

def send_telemetry():
    """
    Mengirim data telemetri ke server dengan HMAC signature.
    """
    print(f"Starting telemetry sender to {API_URL}...")
    print(f"Device MAC: {MAC_ADDRESS}")
    print(f"Security: HMAC-SHA256 enabled")
    
    session = requests.Session()
    counter = 1
    
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
            else:
                print(f"Failed with status {response.status_code}: {response.text}")
                
        except requests.exceptions.RequestException as e:
            print(f"Error connecting to API: {e}")
            
        counter += 1
        time.sleep(3)

if __name__ == "__main__":
    send_telemetry()
