import cv2
import requests
import base64
import time

# ============================================================
# KONFIGURASI
# ============================================================
# Gunakan domain Cloudflare Tunnel (HTTPS)
API_URL = "https://raihanatmaja.my.id/api/iot/stream"
MAC_ADDRESS = "00:1A:2B:3C:4D:5E"
FPS_TARGET = 10  # Membatasi frame per detik (FPS) agar server HTTP & Reverb tidak overload

def start_video_stream():
    """
    Menangkap video dari webcam, mengubah resolusi menjadi 720p,
    mengkonversi frame menjadi JPEG (terkompresi), dan mengirimkannya via HTTP POST.
    Catatan: OpenCV (cv2) menggunakan FFMPEG sebagai backend pemrosesan videonya.
    """
    print(f"Mulai mengirim stream video ke {API_URL}...")
    
    # 0 = Kamera utama / Default Webcam
    # Jika menggunakan RTSP stream atau file video, ganti 0 dengan URL/Path file.
    cap = cv2.VideoCapture(0)
    
    if not cap.isOpened():
        print("Error: Tidak dapat membuka kamera (Webcam).")
        return

    # Memaksa resolusi FFMPEG / Kamera menjadi 720p (1280 x 720)
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 1280)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 720)
    
    print("Kamera berhasil dibuka. Tekan Ctrl+C untuk berhenti.")
    
    try:
        while True:
            start_time = time.time()
            
            # Ambil frame dari kamera
            ret, frame = cap.read()
            if not ret:
                print("Gagal mengambil frame dari kamera.")
                break
            
            # Encode frame ke format JPEG (Kualitas 70 agar lebih ringan dikirim via jaringan)
            encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), 70]
            result, buffer = cv2.imencode('.jpg', frame, encode_param)
            
            if not result:
                continue
                
            # Konversi binary JPEG ke Base64 String
            b64_string = base64.b64encode(buffer).decode('utf-8')
            
            # Format standar Data URI untuk gambar agar bisa langsung ditampilkan di elemen <img> HTML
            data_uri = f"data:image/jpeg;base64,{b64_string}"
            
            payload = {
                "mac_address": MAC_ADDRESS,
                "frame": data_uri
            }
            
            # Kirim frame ke Laravel
            try:
                # Timeout diset kecil agar tidak menumpuk jika server merespon lambat
                requests.post(API_URL, json=payload, timeout=5)
                # print("Frame sent.")
            except requests.exceptions.RequestException as e:
                print(f"Error mengirim frame: {e}")
            
            # Kontrol kecepatan frame (FPS)
            elapsed_time = time.time() - start_time
            sleep_time = max(1.0 / FPS_TARGET - elapsed_time, 0)
            time.sleep(sleep_time)

    except KeyboardInterrupt:
        print("\nMenghentikan stream video...")
    finally:
        cap.release()
        print("Kamera ditutup.")

if __name__ == "__main__":
    start_video_stream()
