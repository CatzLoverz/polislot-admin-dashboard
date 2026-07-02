# 🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir

Script Python yang berjalan di **perangkat edge** (Raspberry Pi, mini-PC, atau laptop) untuk mendeteksi kendaraan di area parkir menggunakan model **YOLOv8** atau **Custom-Model** dan mengirimkan data secara real-time ke server PoliSlot.

> File script berada di folder `python/` pada repository ini.

---

## 📋 Daftar Script

| Script | Protokol | Preview Window | Keterangan |
|---|---|---|---|
| `parking_detector_mqtt.py` | MQTT | ❌ | Headless, cocok untuk produksi |
| `parking_detector_mqtt_preview.py` | MQTT | ✅ | Dengan overlay kamera, untuk kalibrasi |
| `parking_detector_ws.py` | WebSocket (Reverb) | ❌ | Headless, cocok untuk produksi |
| `parking_detector_ws_preview.py` | WebSocket (Reverb) | ✅ | Dengan overlay kamera, untuk kalibrasi |

> **Pilih satu script** sesuai protokol yang dikonfigurasi di server PoliSlot.  
> Gunakan versi **preview** saat setup awal untuk memverifikasi polygon zona deteksi.

---

## ⚙️ Persyaratan Sistem

- **Python** 3.9 atau lebih baru
- **pip** (Python package manager)
- Kamera (webcam USB, IP camera, atau RTSP stream)
- Koneksi jaringan ke server PoliSlot

---

## 🚀 Instalasi

### 1. Salin Folder `python/` ke Perangkat Edge

Salin seluruh folder `python/` dari repository ke perangkat edge.

### 2. Buat Virtual Environment (Disarankan)

```bash
python -m venv venv

# Aktifkan — Linux/macOS
source venv/bin/activate

# Aktifkan — Windows
venv\Scripts\activate
```

### 3. Install Dependensi

```bash
pip install -r requirements.txt
```

Daftar dependensi (`requirements.txt`):

```
websockets
requests
opencv-python
paho-mqtt
pycryptodome
ultralytics
python-dotenv
```

> **Catatan:** Saat pertama kali menjalankan script dengan model pre-trained (misalnya `yolov8n.pt`), `ultralytics` akan mengunduh file weights secara otomatis jika belum ada di direktori aktif.

### 4. Konfigurasi File `.env`

Salin file contoh konfigurasi lalu sesuaikan isinya:

```bash
cp .env.example .env
```

Buka `.env` dan isi semua variabel sesuai lingkungan:

```dotenv
# ==========================================
# KONFIGURASI UMUM (YOLO & KAMERA)
# ==========================================

# MAC Address fisik perangkat ini (sebagai identitas unik di sistem)
MAC_ADDRESS=00:15:5D:5D:8A:5A

# Sumber video: "0" untuk webcam default, path file video, atau RTSP URL
# Contoh RTSP: rtsp://user:pass@192.168.1.100:554/stream
CAMERA_SOURCE=0

# Model deteksi yang digunakan oleh library Ultralytics.
# Dapat berupa nama model pre-trained (diunduh otomatis) atau path ke file model lokal.
# Format yang didukung: .pt (PyTorch), .onnx, .torchscript, .tflite, .engine (TensorRT), dll.
# Contoh pre-trained : yolov8n.pt | yolo11n.pt | yolov10n.pt
# Contoh custom model: /path/to/custom_model.pt | /path/to/model.onnx
YOLO_WEIGHTS=yolov8n.pt

# Ambang batas kepercayaan deteksi YOLOv8 (0.0 s.d 1.0)
# Semakin tinggi = semakin ketat. Disarankan: 0.4 - 0.6
CONFIDENCE_THRESHOLD=0.4

# Tampilkan log hasil deteksi di terminal (true/false)
ENABLE_DETECTION_LOG=true

# Tampilkan log debug koneksi yang repetitif (true/false)
ENABLE_DEBUG_LOG=false

# Filter kelas objek YOLO yang dideteksi (pisahkan dengan koma)
# 2 = car (mobil), 3 = motorcycle (motor)
TARGET_CLASSES=2,3

# Kunci rahasia bersama (HARUS SAMA persis dengan IOT_API_SECRET di Laravel .env server)
SHARED_SECRET=pOl1sL0t_ioT_s3creT_k3y_2026


# ==========================================
# KONFIGURASI MQTT (Hanya untuk skrip MQTT)
# ==========================================

MQTT_BROKER=127.0.0.1
MQTT_PORT=1883
MQTT_USER=polislot_user
MQTT_PASSWORD=secure_password

# Protokol transport MQTT:
# "tcp"  — MQTT standar (port 1883 / 8883)
# "ws"   — MQTT over WebSockets (port 80 / 443 / 8083), wajib jika melewati Cloudflare Tunnel
MQTT_PROTOCOL=tcp


# ==========================================
# KONFIGURASI WEBSOCKET (Hanya untuk skrip WS)
# ==========================================

# Base URL server PoliSlot (untuk endpoint HTTP POST /api/iot/*)
SERVER_BASE_URL=http://127.0.0.1:8080

# Host & port Laravel Reverb WebSocket
REVERB_HOST=127.0.0.1
REVERB_PORT=8080

# Protokol WS: "ws" (lokal/HTTP) atau "wss" (produksi/HTTPS)
REVERB_SCHEME=ws

# App Key Reverb (dari Laravel .env: REVERB_APP_KEY)
REVERB_APP_KEY=xcubvd4inm14ayepjhro
```

### Format Model yang Didukung (`YOLO_WEIGHTS`)

Variabel `YOLO_WEIGHTS` menerima **nama model pre-trained** atau **path ke file model lokal** dalam format apapun yang didukung library [Ultralytics](https://docs.ultralytics.com/modes/predict/):

| Format | Ekstensi | Keterangan |
|---|---|---|
| PyTorch | `.pt` | Format default, paling fleksibel |
| ONNX | `.onnx` | Portabel, didukung banyak runtime |
| TorchScript | `.torchscript` | Optimal untuk inferensi CPU |
| TensorRT | `.engine` | Optimal untuk GPU NVIDIA |
| TFLite | `.tflite` | Optimal untuk perangkat mobile/edge ARM |
| OpenVINO | `_openvino_model/` | Optimal untuk prosesor Intel |

> Ultralytics juga mendukung berbagai arsitektur model selain YOLOv8, seperti **YOLO11**, **YOLOv10**, **YOLOv9**, **YOLOv5**, **RT-DETR**, dan lainnya — selama model tersebut dikemas dalam format di atas.

---

## ▶️ Menjalankan Script

MAC Address dapat diberikan melalui **argumen terminal** (prioritas utama) atau dibaca otomatis dari **env variable** `MAC_ADDRESS` di file `.env`.

### Sintaks

```bash
python <nama_script>.py [MAC_ADDRESS]
```

### Contoh — MQTT Headless

```bash
# Menggunakan argumen terminal
python parking_detector_mqtt.py AA:BB:CC:DD:EE:FF

# Menggunakan MAC Address dari .env
python parking_detector_mqtt.py
```

### Contoh — MQTT dengan Preview

```bash
python parking_detector_mqtt_preview.py AA:BB:CC:DD:EE:FF
```

### Contoh — WebSocket Headless

```bash
python parking_detector_ws.py AA:BB:CC:DD:EE:FF
```

### Contoh — WebSocket dengan Preview

```bash
python parking_detector_ws_preview.py AA:BB:CC:DD:EE:FF
```

> Tekan `Q` pada jendela preview (atau `Ctrl+C` di terminal) untuk menghentikan script.

---

## 🔐 Keamanan

Setiap pesan yang dikirim dari perangkat edge ke server **ditandatangani secara kriptografis** menggunakan HMAC-SHA256 dengan kunci yang berasal dari `SHARED_SECRET`. Snapshot kamera dienkripsi menggunakan **AES-256-CBC** sebelum dikirim.

Pastikan nilai `SHARED_SECRET` di file `.env` perangkat **identik** dengan variabel `IOT_API_SECRET` di file `.env` server Laravel.

---

## 🌐 Koneksi Melalui Cloudflare Tunnel (Produksi)

Jika server PoliSlot diakses melalui domain publik (Cloudflare Tunnel):

**Untuk skrip MQTT:**
```dotenv
MQTT_BROKER=mqtt.domainanda.com
MQTT_PORT=443
MQTT_PROTOCOL=ws   # Wajib! Cloudflare Tunnel tidak mendukung TCP MQTT langsung
```

**Untuk skrip WebSocket:**
```dotenv
SERVER_BASE_URL=https://api.domainanda.com
REVERB_HOST=reverb.domainanda.com
REVERB_PORT=443
REVERB_SCHEME=wss
```

---

## 🗂️ Caching Konfigurasi Lokal

Script secara otomatis menyimpan konfigurasi zona deteksi (polygon, jumlah slot, threshold) yang diterima dari server ke file lokal:

```
config_cache_<MACADDRESS>.json
```

File ini dimuat saat script pertama kali dijalankan sehingga perangkat dapat langsung beroperasi **tanpa perlu menunggu kiriman config dari server** (berguna saat server restart atau koneksi terputus sementara).
