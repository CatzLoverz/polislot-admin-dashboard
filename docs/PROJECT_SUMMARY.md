# Ringkasan Proyek PoliSlot

Dokumen ini merangkum arsitektur deployment, prosedur instalasi (Website/Server dan Mobile), serta daftar periksa keamanan (Security Checklist) untuk proyek PoliSlot.

## 1. Deployment Architecture

Sistem PoliSlot menggunakan arsitektur *Client-Server* dengan integrasi IoT, yang terdiri dari tiga komponen utama:

-   **Backend Server & Admin Dashboard (Laravel 12):**
    -   Bertindak sebagai pusat data (API Server via Laravel Sanctum) dan dashboard administrasi manajemen (Blade Templates).
    -   Menggunakan **MariaDB / MySQL** sebagai sistem database.
    -   Menerapkan **Role-Based Access Control (RBAC)** di tingkat database. Admin Dashboard dan Mobile App API menggunakan kredensial database yang berbeda (`polislot_admin` dan `polislot_mobile`) untuk memisahkan *privilege*.
    -   **Enkripsi Hybrid (RSA/AES)** digunakan untuk mengamankan *payload* komunikasi API dengan aplikasi mobile.
-   **Mobile App (Flutter ^3.10.0):**
    -   Aplikasi klien (Android/iOS) menggunakan *State Management* Riverpod dan paket *networking* Dio.
    -   Menampilkan lokasi parkir menggunakan Google Maps API.
    -   Menggunakan `public_key.pem` untuk mengenkripsi data sensitif yang dikirim ke backend.
-   **Infrastruktur & IoT (Docker):**
    -   Infrastruktur server dideploy menggunakan **Docker Compose**, yang mencakup container untuk:
        -   `app`: Aplikasi Laravel.
        -   `db`: MariaDB.
        -   `scheduler`: Cron job untuk auto-backup database.
        -   `logrotate`: Manajemen rotasi log otomatis.
        -   `mosquitto`: MQTT Broker untuk komunikasi dengan perangkat IoT parkir, dilengkapi autentikasi.
        -   `tunnel`: Cloudflare tunnel (opsional) untuk eksposur aman ke internet tanpa *port forwarding*.

---

## 2. Installation Procedure

### 2.1. Backend Server & Admin Dashboard (Via Docker - Recommended)

*Metode Docker sangat direkomendasikan untuk environment Server/Production karena mencakup kontainer yang sudah dikonfigurasi untuk Database, Scheduler (Auto-backup), dan Logrotate.*

#### Langkah 1: Persiapan File Konfigurasi
Karena instalasi docker terpisah dari source code utama (berada di folder `docker/`), Anda perlu menyalin beberapa file ke *Root Directory* (sejajar dengan folder `app/`):
- Salin `docker/docker-compose.yml` -> ke root.
- Salin `docker/mariadb.cnf` -> ke root.
- Salin `docker/logrotate-entrypoint.sh` -> ke root. (Khusus Linux: berikan izin eksekusi `chmod +x logrotate-entrypoint.sh`).
- Salin `docker/mosquitto.conf` -> ke root.
- Salin `.env.example` -> ke root lalu *rename* menjadi `.env`.

#### Langkah 2: Konfigurasi Environment (`.env`)
Buka file `.env` dan atur parameter krusial berikut:
```ini
# --- Konfigurasi Database ---
DB_CONNECTION=mariadb
DB_HOST=db
DB_PORT=3306
DB_DATABASE=polislot
DB_USERNAME=root
DB_PASSWORD=<password_database_sama_dengan_docker_compose>

# --- Kredensial Admin Otomatis ---
ADMIN_EMAIL=email_valid_anda@gmail.com
ADMIN_PASSWORD=password_aman_anda

# --- Kredensial SMTP Email (Wajib untuk OTP) ---
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_email_anda
MAIL_ENCRYPTION=tls

# --- Layanan Eksternal & IoT ---
GOOGLE_MAPS_JS="isi_api_key_google_cloud_anda"
MQTT_AUTH_USERNAME=MQTTPoliSlot
MQTT_AUTH_PASSWORD=password_mqtt_aman
```

#### Langkah 3: Konfigurasi `docker-compose.yml`
Buka `docker-compose.yml` yang sudah di root, pastikan bagian `MARIADB_ROOT_PASSWORD` diubah sesuai dengan `DB_PASSWORD` root yang di-set di `.env`.

#### Langkah 4: Generate RSA Keys (Penting!)
Di root directory proyek, jalankan perintah berikut untuk membuat pasangan kunci *Hybrid Encryption* (RSA):
```bash
openssl genrsa -out private_key.pem 4096
openssl rsa -pubout -in private_key.pem -out public_key.pem
```
*(Catatan: Kunci ini akan dimount oleh Docker. `public_key.pem` nanti akan digunakan untuk instalasi Mobile App).*

#### Langkah 5: Jalankan Kontainer Docker
Mulai seluruh layanan (App, DB, Logrotate, Mosquitto, Tunnel):
```bash
docker compose up -d
```

#### Langkah 6: Inisialisasi Aplikasi Laravel
Masuk ke kontainer aplikasi untuk men-generate APP_KEY, menjalankan migrasi tabel, dan memasukkan data admin awal:
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --fresh
docker compose exec app php artisan db:seed
```

#### Langkah 7: Setup Role-Based Access Control (RBAC) Database
Demi keamanan, buat user database spesifik (bukan root) untuk Dashboard dan Mobile API:
```bash
# Setup user khusus Admin Dashboard
docker compose exec app php artisan db:setup-admin polislot_admin password_db_admin

# Setup user khusus Mobile API
docker compose exec app php artisan db:setup-user polislot_mobile password_db_mobile
```

#### Langkah 8: Finalisasi Konfigurasi
Buka kembali `.env` dan ganti kredensial database root dengan user RBAC yang baru saja dibuat:
```ini
DB_USERNAME=polislot_admin
DB_PASSWORD=password_db_admin

DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=password_db_mobile
```
Setelah itu terapkan perubahan kredensial dengan me-restart kontainer:
```bash
docker compose up -d --force-recreate
```
*Aplikasi kini dapat diakses di http://localhost:8080 (atau domain tunnel Anda).*

---

### 2.2. Backend Server (Instalasi Manual Lokal / XAMPP)

Jika Anda tidak menggunakan Docker (misal di Laragon/XAMPP), ikuti ringkasan berikut:
1. Copy `.env.example` ke `.env` dan atur DB connection ke MySQL lokal (`DB_HOST=127.0.0.1`).
2. Tentukan letak binary mysql untuk auto-backup: `DUMP_BINARY_PATH="C:/xampp/mysql/bin/"`.
3. Generate RSA Keys (arahkan langsung ke folder storage):
   ```bash
   openssl genrsa -out storage/app/private/keys/private_key.pem 4096
   openssl rsa -pubout -in storage/app/private/keys/private_key.pem -out storage/app/private/keys/public_key.pem
   ```
4. Install dependensi: `composer install`.
5. Generate Key & DB Setup:
   ```bash
   php artisan key:generate
   php artisan migrate --fresh --seed
   ```
6. Jalankan Server: `php artisan serve` dan pastikan menjalankan cron dengan `php artisan schedule:work` di terminal terpisah.

---

### 2.3. PoliSlot Mobile App (Flutter)

#### Langkah 1: Persiapan Perangkat Lunak
- Flutter SDK (versi >= 3.10.0) telah terinstal.
- Android Studio (untuk OS Android) atau Xcode (untuk iOS).
- Backend Server (PoliSlot Dashboard) telah berjalan dan dapat diakses (mendapatkan IP/Domain).

#### Langkah 2: Kloning & Pengunduhan Dependensi
```bash
git clone <url-repo-mobile>
cd polislot_mobile_catz
flutter pub get
```

#### Langkah 3: Konfigurasi Environment & Google Maps
Salin file `.env.example` menjadi `.env` di *root* proyek mobile dan lengkapi nilai-nilai variabelnya:
```env
API_BASE_URL=http://<ip-backend-atau-domain>/api
MQTT_HOST=127.0.0.1:8080
MQTT_PORT=9001
MQTT_USERNAME=MQTTPoliSlot
MQTT_PASSWORD=password_mqtt_anda
GOOGLE_MAPS_API_KEY=isi_dengan_api_key_google_maps_anda
```

#### Langkah 4: Kunci Publik RSA
Salin file `public_key.pem` yang didapatkan dari langkah instalasi server backend ke dalam direktori `assets/keys/` di aplikasi mobile. Kunci ini mutlak diperlukan untuk proses enkripsi *payload* yang dikirimkan ke server.

#### Langkah 5: Code Generation (Riverpod)
Aplikasi menggunakan Riverpod dan *JSON Serializable*, jalankan *build_runner* untuk mengompilasi kode yang diperlukan:
```bash
dart run build_runner build --delete-conflicting-outputs
```

#### Langkah 6: Kompilasi dan Jalankan Aplikasi
Hubungkan perangkat seluler (fisik/emulator) dan jalankan:
```bash
# Mode pengembangan / Debug
flutter run

# Membangun file rilis APK untuk didistribusikan
flutter build apk --release
```

---

### 2.4. Perangkat Edge IoT (Parking Detector)

Perangkat edge (berbasis Python) bertugas melakukan deteksi kendaraan menggunakan AI (YOLOv8) dan mengirim datanya ke server via MQTT. File-file ini berada di direktori `python/` pada repositori admin dashboard.

#### Langkah 1: Persiapan Perangkat Keras dan Lunak
- Sistem Operasi: Linux (disarankan, misal Raspberry Pi OS atau Ubuntu) / Windows.
- Python 3.x terinstal.
- Kamera (Webcam / CCTV RTSP) terhubung ke perangkat.

#### Langkah 2: Instalasi Dependensi Python
Buka terminal pada perangkat Edge IoT dan arahkan ke folder `python/`, lalu instal *library* yang dibutuhkan:
```bash
pip install -r requirements.txt
pip install ultralytics
```

#### Langkah 3: Konfigurasi Internal Skrip (Krusial)
Buka file `parking_detector_mqtt.py` (atau versi websockets) menggunakan *text editor* dan sesuaikan parameter konfigurasi pada bagian atas file:
- `BROKER` & `PORT`: Tentukan alamat host dan port broker (default port `443` jika menggunakan Cloudflare Tunnel HTTPS/WSS, atau `1883` untuk *local TCP*).
- `SOURCE`: Gunakan `"0"` untuk *webcam default*, atau ganti dengan *path video/RTSP URL CCTV* Anda.
- `YOLO_WEIGHTS`: (Opsional) ganti jika menggunakan model YOLO kustom.
- `CONFIDENCE_THRESHOLD`: Ubah ambang batas akurasi AI (default `0.4`).
- `SHARED_SECRET`: **HARUS SAMA PERSIS** dengan nilai `IOT_API_SECRET` yang terdapat pada file `.env` Laravel untuk validasi HMAC dan dekripsi *snapshot* AES.

#### Langkah 4: Konfigurasi Kredensial MQTT
Pastikan Anda mengatur *environment variable* untuk kredensial MQTT agar alat dapat mengautentikasi diri ke broker Mosquitto:
- **Linux/macOS**:
  ```bash
  export MQTT_USER="MQTTPoliSlot"
  export MQTT_PASSWORD="password_mqtt_aman"
  ```
- **Windows (PowerShell)**:
  ```powershell
  $env:MQTT_USER="MQTTPoliSlot"
  $env:MQTT_PASSWORD="password_mqtt_aman"
  ```

#### Langkah 5: Menjalankan Skrip Deteksi
Jalankan skrip utama detektor berbasis MQTT. Anda dapat memasukkan *MAC Address* (digunakan sebagai ID unik alat parkir di server) sebagai argumen, atau membiarkannya kosong untuk memasukkannya secara manual saat ditanya:
```bash
python parking_detector_mqtt.py [MAC_ADDRESS]
# Contoh: python parking_detector_mqtt.py 00:1A:2B:3C:4D:5E
```
Pada saat pertama kali dijalankan, skrip akan mengunduh beban model YOLOv8 (`yolov8n.pt`) atau anda juga dapat menggunakan model custom lainnya. Selanjutnya, kamera akan aktif untuk mendeteksi kendaraan, dan mengirimkan perhitungan serta gambar terenkripsi ke dashboard secara otomatis.

---

## 3. Security Checklist

Berikut adalah minimal 20 daftar periksa keamanan (*security checklist*) yang **wajib** diterapkan sebelum sistem memasuki tahap produksi:

### Infrastruktur & Konfigurasi Server
- [ ] **1. Ubah Kredensial Default:** Pastikan `MARIADB_ROOT_PASSWORD` di `docker-compose.yml` diubah dengan *password* yang sangat kuat.
- [ ] **2. Terapkan RBAC Database:** Aplikasi **TIDAK BOLEH** berjalan menggunakan user `root` MySQL/MariaDB pada `.env` produksi. Selalu gunakan user `polislot_admin` dan `polislot_mobile`.
- [ ] **3. Matikan Debug Mode:** Pastikan `APP_DEBUG=false` pada file `.env` di server backend.
- [ ] **4. Set Environment ke Production:** Pastikan `APP_ENV=production` pada `.env` backend.
- [ ] **5. Generate APP_KEY Unik:** Pastikan `php artisan key:generate` telah dijalankan sehingga *session* dan enkripsi Laravel aman.
- [ ] **6. Amankan RSA Private Key:** Pastikan file `private_key.pem` memiliki perizinan ketat (hanya bisa dibaca oleh *user web server*/*container*) dan tidak pernah di-*commit* ke repositori Git (pastikan ada di `.gitignore`).
- [ ] **7. Terapkan HTTPS (SSL/TLS):** Backend harus selalu diakses melalui HTTPS, baik menggunakan *reverse proxy* (Nginx/Caddy) atau Cloudflare Tunnel.
- [ ] **8. Lindungi Port Database:** Port database (3306/3308) hanya boleh di-binding ke `127.0.0.1` atau hanya terisolasi di dalam *Docker Network*. Jangan buka port database ke jaringan publik.
- [ ] **9. Autentikasi MQTT Wajib:** Pastikan *broker* Mosquitto menggunakan *password* (`MQTT_AUTH_USERNAME` & `MQTT_AUTH_PASSWORD`) dan koneksi tanpa sandi dinonaktifkan.
- [ ] **10. Terapkan Logrotate:** Pastikan *container logrotate* berjalan dengan baik untuk mencegah serangan *Denial of Service* (DoS) akibat kehabisan ruang disk (*disk space exhaustion*) oleh file log.

### API & Komunikasi Data
- [ ] **11. Wajibkan Enkripsi Payload:** Pastikan *middleware* enkripsi (RSA/AES) pada rute API sensitif (login, registrasi, OTP, transaksi) di backend aktif dan tidak di-*bypass*.
- [ ] **12. Batasi (Restrict) Google Maps API Key:** Masuk ke Google Cloud Console dan batasi penggunaan API Key Map hanya untuk alamat domain backend web, dan *package name* / SHA-1 dari aplikasi Android/iOS.
- [ ] **13. Gunakan URL HTTPS di Mobile:** Variabel `API_URL` di file `.env` aplikasi mobile **wajib** menggunakan protokol `https://`.
- [ ] **14. Sembunyikan Kredensial Email (SMTP):** Pastikan `MAIL_PASSWORD` menggunakan *App Password* khusus dan bukan password akun email utama.
- [ ] **15. Amankan Kredensial Cloudflare (Opsional):** Jika menggunakan Tunnel, pastikan `TUNNEL_TOKEN` disimpan rapi dan tidak disebarluaskan.

### Aplikasi Mobile & Backend Code
- [ ] **16. Obfuscate Kode Mobile:** Saat melakukan proses *build* rilis (APK/AAB/IPA), pastikan menggunakan *flag obfuscation* (`flutter build apk --obfuscate --split-debug-info=./symbols/`) agar kode sulit di-*reverse engineering*.
- [ ] **17. Verifikasi Integritas Kunci Publik:** Pastikan `public_key.pem` di-bundle dengan aman dalam aset mobile dan divalidasi keasliannya agar tidak diganti oleh pelaku kejahatan (*Man-in-the-Middle/Tampering*).
- [ ] **18. Sanitasi & Validasi Input:** Meskipun payload API dienkripsi, backend tetap wajib melakukan validasi ketat menggunakan Laravel Form Requests untuk mencegah *SQL Injection* atau *XSS*.
- [ ] **19. Pembaruan Rutin Dependensi:** Lakukan pembaruan secara berkala pada dependensi PHP (`composer update`) dan Flutter (`flutter pub upgrade`) untuk menambal kerentanan (*vulnerability*) keamanan baru.
- [ ] **20. Backup Database Rutin:** Pastikan fitur *Auto Backup* (via cron/scheduler) berjalan dengan baik dan hasil *backup* disimpan/dikirim ke tempat yang aman di luar server tersebut (sebagai mitigasi *Ransomware* atau kerusakan *hardware*).
