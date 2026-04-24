import cv2
import requests
import base64
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

# === Pengaturan Kualitas Stream ===
FRAME_WIDTH = 640
FRAME_HEIGHT = 360
JPEG_QUALITY = 45
FPS_TARGET = 3

# === Session untuk reuse TCP connection ===
session = requests.Session()
session.headers.update({'Content-Type': 'application/json'})

def sign_request(mac_address, timestamp, frame):
    """Membuat HMAC-SHA256 signature."""
    frame_length = len(frame)
    data_to_sign = f"{mac_address}:{timestamp}:{frame_length}"
    return hmac.new(
        IOT_API_SECRET.encode('utf-8'),
        data_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

def start_video_stream():
    """
    Menangkap video dari webcam dan mengirimkannya via HTTP POST
    dengan HMAC signature untuk keamanan.
    """
    print(f"Mulai mengirim stream video ke {API_URL}...")
    print(f"Device MAC: {MAC_ADDRESS}")
    print(f"Resolusi: {FRAME_WIDTH}x{FRAME_HEIGHT} | JPEG Quality: {JPEG_QUALITY} | FPS: {FPS_TARGET}")
    print(f"Security: HMAC-SHA256 enabled")
    
    cap = cv2.VideoCapture(0)
    
    if not cap.isOpened():
        print("Error: Tidak dapat membuka kamera (Webcam).")
        return

    cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)
    
    print("Kamera berhasil dibuka. Tekan Ctrl+C untuk berhenti.")
    
    frame_count = 0
    error_count = 0
    jpeg_quality = JPEG_QUALITY
    
    try:
        while True:
            start_time = time.time()
            
            ret, frame = cap.read()
            if not ret:
                print("Gagal mengambil frame dari kamera.")
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
            
            # Buat timestamp dan signature
            timestamp = int(time.time())
            signature = sign_request(MAC_ADDRESS, timestamp, data_uri)
            
            payload = {
                "mac_address": MAC_ADDRESS,
                "frame": data_uri,
                "timestamp": timestamp,
                "signature": signature,
            }
            
            try:
                response = session.post(API_URL, json=payload, timeout=8)
                frame_count += 1
                
                if response.status_code == 200:
                    size_kb = len(b64_string) / 1024
                    elapsed = time.time() - start_time
                    print(f"[Frame #{frame_count}] OK | {size_kb:.1f} KB | {elapsed:.2f}s")
                    error_count = 0
                elif response.status_code == 403:
                    print(f"[DITOLAK] Device MAC tidak terdaftar di server!")
                    break
                elif response.status_code == 401:
                    print(f"[DITOLAK] Signature invalid atau request expired!")
                    break
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

    except KeyboardInterrupt:
        print(f"\nStream dihentikan. Total frame terkirim: {frame_count}")
    finally:
        cap.release()
        session.close()
        print("Kamera ditutup.")

if __name__ == "__main__":
    start_video_stream()
