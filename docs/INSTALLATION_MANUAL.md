# Instalasi Manual (Tanpa Docker)

Panduan ini menjelaskan cara menginstal dan menjalankan aplikasi Polislot Admin Dashboard secara manual (Localhost/XAMPP/Laragon/VPS).

## Prasyarat

Sebelum memulai, pastikan sistem Anda memiliki:
- **PHP**: Versi 8.4 atau lebih baru.
- **Composer**: Untuk manajemen dependensi PHP.
- **Node.js & NPM**: Untuk manajemen dan kompilasi dependensi frontend (Tailwind CSS, Laravel Echo).
- **MySQL / MariaDB**: Sebagai database server.
- **Web Server**: Apache atau Nginx.
- **Mosquitto MQTT Broker**: Dapat diinstal secara lokal pada OS Anda, atau menggunakan public broker.
*Pastikan Anda juga telah memenuhi requirements external service (Google Cloud / Cloudflare) yang disebutkan di README utama.*

---

## Langkah Instalasi

### 1. Konfigurasi Environment (.env)
Salin file `.env.example` menjadi `.env`.
```bash
cp .env.example .env
```
Buka file `.env` dan atur:
1.  **Database**: Sesuaikan dengan database lokal Anda.
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=polislot_local
    DB_USERNAME=root
    DB_PASSWORD=
    ```
2.  **Database Binary Path**: (Wajib untuk fitur Backup).
    Arahkan ke folder binary MySQL/MariaDB (mysqldump). Sesuaikan dengan instalasi Anda.
    
    *Contoh Windows (XAMPP):*
    ```ini
    DUMP_BINARY_PATH="C:/xampp/mysql/bin/"
    ```
    *Contoh Linux (biasanya kosong atau default):*
    ```ini
    # DUMP_BINARY_PATH="" 
    ```

3.  **Admin Account**:
    > **PENTING**: Gunakan **EMAIL YANG VALID** karena kode OTP untuk reset password akan dikirimkan ke email ini.
    ```ini
    ADMIN_EMAIL=email_valid_anda@gmail.com
    ADMIN_PASSWORD=password_aman_anda
    ```
4.  **SMTP Mailer**:
    Agar OTP dapat terkirim, Anda **WAJIB** mengonfigurasi pengaturan email.
    ```ini
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=email_anda@gmail.com
    MAIL_PASSWORD=app_password_anda
    MAIL_ENCRYPTION=tls
    ```
5.  **External Services**:
    - **Google Maps**: Wajib diisi agar fitur peta di dashboard berfungsi.
    ```ini
    GOOGLE_MAPS_JS="isi_api_key_google_cloud_anda"
    ```
6.  **Laravel Reverb (WebSocket) & IoT Security**:
    Atur konfigurasi server WebSocket dan *secret key* untuk perangkat IoT.
    ```ini
    REVERB_APP_ID="bebas_isi_angka_acak"
    REVERB_APP_KEY="bebas_isi_karakter_acak"
    REVERB_APP_SECRET="bebas_isi_karakter_acak_rahasia"
    REVERB_HOST="127.0.0.1"
    REVERB_SERVER_HOST="127.0.0.1"

    # Shared secret untuk HMAC signature dan AES Encryption dari/ke perangkat IoT
    IOT_API_SECRET="rahasia_iot_anda_disini"
    ```
7.  **MQTT Authentication (Mosquitto)**:
    Sesuaikan dengan host dan kredensial broker MQTT yang Anda gunakan.
    ```ini
    MQTT_HOST=127.0.0.1
    MQTT_PORT=1883
    MQTT_AUTH_USERNAME=polislot_user
    MQTT_AUTH_PASSWORD=secure_password
    MQTT_MOBILE_USERNAME=polislot_mobile
    MQTT_MOBILE_PASSWORD=mobile_secure_password
    ```

### 2. Generate RSA Keys (Wajib)
Generate pasangan kunci RSA untuk enkripsi API agar aplikasi mobile dapat terhubung.
```bash
openssl genrsa -out storage/app/private/keys/private_key.pem 4096
openssl rsa -pubout -in storage/app/private/keys/private_key.pem -out storage/app/private/keys/public_key.pem
```
*Note: Public key bisa disimpan untuk referensi, private key wajib ada.*

### 3. Instalasi Dependency
Install library PHP (Composer) dan dependensi Frontend (NPM) yang dibutuhkan.
```bash
composer install
npm install
npm run build
```

### 4. Generate Application Key
Generate key aplikasi Laravel.
```bash
php artisan key:generate
```

### 5. Migrasi Database & Seeding
Jalankan migrasi database dan seeder (akan membuat akun admin sesuai `.env`).
```bash
php artisan migrate --fresh --seed
```

### 6. Setup Database Roles (RBAC)
Buat user database khusus untuk keamanan (Best Practice).
```bash
# Setup user database untuk Admin Dashboard
php artisan db:setup-admin polislot_admin password_db_admin

# Setup user database untuk Mobile API
php artisan db:setup-user polislot_mobile password_db_mobile
```

### 7. Atur Ulang Environment (.env) - PENTING
Update file `.env` untuk menggunakan user database yang baru dibuat, BUKAN root.

```ini
# Ganti dengan user "polislot_admin"
DB_USERNAME=polislot_admin
DB_PASSWORD=password_db_admin

# Isi kredensial mobile
DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=password_db_mobile
```

### 8. Konfigurasi Manual MQTT Broker (Mosquitto)
*(Lewati langkah ini jika Anda menggunakan Broker MQTT Publik/Cloud eksternal).*

Untuk menjalankan Mosquitto secara lokal dan membiarkan perangkat dalam satu jaringan (IoT Edge & Mobile App) terhubung ke Broker Anda secara manual:

#### A. Instalasi Mosquitto
- **Windows**: Unduh installer dari [mosquitto.org](https://mosquitto.org/download/) dan jalankan instalasi. Secara default, ia akan terinstal di `C:\Program Files\mosquitto\`.
- **Linux (Ubuntu/Debian)**:
  ```bash
  sudo apt update
  sudo apt install mosquitto mosquitto-clients
  ```

#### B. Konfigurasi `mosquitto.conf`
Mosquitto v2.x secara default hanya mendengarkan koneksi pada `localhost` (127.0.0.1) dan memblokir koneksi dari luar. Agar perangkat lain dalam satu jaringan bisa terhubung:
1. Buka file konfigurasi Mosquitto (Windows: `C:\Program Files\mosquitto\mosquitto.conf`, Linux: `/etc/mosquitto/mosquitto.conf`).
2. Ubah atau tambahkan baris berikut untuk mengaktifkan listener port 1883 (TCP) dan port 9001 (WebSockets) untuk semua interface jaringan (`0.0.0.0`):
   ```ini
   # Listener TCP Utama (untuk Laravel & Perangkat IoT Edge)
   listener 1883 0.0.0.0
   protocol mqtt

   # Listener WebSockets (untuk Mobile App)
   listener 9001 0.0.0.0
   protocol websockets

   # Keamanan & Autentikasi
   allow_anonymous false
   password_file C:/Program Files/mosquitto/mosquitto.passwd
   acl_file C:/Program Files/mosquitto/mosquitto.acl
   ```
   *(Catatan untuk pengguna Linux: Ganti path `C:/Program Files/mosquitto/` menjadi `/etc/mosquitto/`)*.

#### C. Membuat File Password & Kredensial User
Buat kredensial autentikasi agar broker aman dari akses luar yang tidak berizin.
1. Buka Command Prompt/Terminal sebagai **Administrator**.
2. Masuk ke direktori Mosquitto (jika di Windows):
   ```cmd
   cd "C:\Program Files\mosquitto"
   ```
3. Buat berkas password dan tambahkan user pertama (`polislot_user` untuk backend Laravel & IoT Edge):
   ```cmd
   mosquitto_passwd -c mosquitto.passwd polislot_user
   ```
   *Masukkan password ketika diminta (contoh: `secure_password` sesuai dengan `.env`).*
4. Tambahkan user kedua untuk aplikasi mobile (`polislot_mobile`):
   ```cmd
   mosquitto_passwd -b mosquitto.passwd polislot_mobile mobile_secure_password
   ```

#### D. Membuat File ACL (Access Control List)
Buat file bernama `mosquitto.acl` di direktori mosquitto untuk membatasi hak akses tiap user:
1. Buat berkas teks baru bernama `mosquitto.acl`.
2. Isi berkas tersebut dengan konfigurasi berikut:
   ```ini
   # User Backend & IoT Edge (Bisa membaca dan menulis semua topik)
   user polislot_user
   topic readwrite #

   # User Mobile App (Hanya bisa membaca topik status parkir)
   user polislot_mobile
   topic read frontend/#
   ```
3. Simpan berkas tersebut di direktori yang sama dengan `mosquitto.conf`.

#### E. Menjalankan Service Mosquitto
Jalankan broker Mosquitto secara manual menggunakan file konfigurasi yang telah diubah:
- **Windows (Command Prompt):**
  ```cmd
  net stop mosquitto
  "C:\Program Files\mosquitto\mosquitto.exe" -c "C:\Program Files\mosquitto\mosquitto.conf" -v
  ```
- **Linux:**
  ```bash
  sudo systemctl restart mosquitto
  ```

---

### 9. Mengatasi Masalah Koneksi Broker Satu Jaringan (Troubleshooting)
Jika perangkat IoT Edge atau Mobile App Anda **gagal terhubung ke broker meskipun berada dalam satu jaringan Wi-Fi/LAN**, lakukan langkah-langkah berikut:

#### A. Konfigurasi IP Host & Port (.env)
- **Perangkat IoT Edge (.env)**:
  Ubah `MQTT_BROKER` menjadi IP lokal PC/Server Anda yang menjalankan broker (misal `192.168.1.100`), **BUKAN** `127.0.0.1` atau `localhost`.
  ```dotenv
  MQTT_BROKER=192.168.1.100
  MQTT_PORT=1883
  MQTT_PROTOCOL=tcp
  ```
- **Mobile App (.env)**:
  Gunakan IP lokal server MQTT Anda pada `MQTT_HOST`. Untuk pengetesan lokal tanpa sertifikat SSL (HTTPS/TLS), ubah `MQTT_SCHEME` ke `ws` (WebSockets unencrypted) dan arahkan ke port WebSockets Mosquitto (default `9001`).
  ```dotenv
  MQTT_HOST=192.168.1.100
  MQTT_PORT=9001
  MQTT_SCHEME=ws
  MQTT_USERNAME=polislot_mobile
  MQTT_PASSWORD=mobile_secure_password
  ```
- **Laravel Server (.env)**:
  Jika Laravel berjalan di mesin yang sama dengan Mosquitto, gunakan `MQTT_HOST=127.0.0.1`. Jika berbeda mesin, gunakan IP lokal server Mosquitto.

#### B. Windows Defender Firewall (Sering Menjadi Penyebab Utama)
Windows Defender Firewall secara default memblokir semua koneksi masuk (inbound) pada port 1883 dan 9001. Anda wajib membuat aturan baru (Inbound Rule) di komputer server:
1. Buka **Windows Defender Firewall with Advanced Security**.
2. Klik **Inbound Rules** di panel kiri, lalu klik **New Rule...** di panel kanan.
3. Pilih **Port** -> **Next**.
4. Pilih **TCP** dan isi **Specific local ports** dengan `1883, 9001` -> **Next**.
5. Pilih **Allow the connection** -> **Next**.
6. Centang **Domain**, **Private**, dan **Public** -> **Next**.
7. Beri nama aturan tersebut (misalnya `Mosquitto MQTT Broker`) lalu klik **Finish**.

#### C. Isolasi Jaringan Router (AP Isolation)
Beberapa router Wi-Fi (terutama router publik, kosan, atau kantor) mengaktifkan fitur **AP Isolation** (Client Isolation). Fitur ini mencegah sesama perangkat yang terhubung ke Wi-Fi yang sama untuk berkomunikasi secara lokal.
- **Gejala**: Anda bisa melakukan ping ke internet dari kedua perangkat, tetapi tidak bisa melakukan ping antar perangkat (misal dari HP ke laptop server).
- **Solusi**: Matikan fitur AP Isolation/Client Isolation di pengaturan Router Anda, atau gunakan tethering Wi-Fi dari handphone sebagai jaringan lokal alternatif untuk pengetesan.

---

### 10. Menjalankan Service Aplikasi
Untuk menjalankan aplikasi secara penuh di lingkungan lokal, Anda perlu membuka **beberapa terminal terpisah** di dalam folder proyek dan menjalankan perintah berikut secara bersamaan:

1. **Web Server**:
   ```bash
   php artisan serve --port=8080
   ```
   *(Aplikasi dapat diakses melalui browser di `http://localhost:8080`)*

2. **WebSocket Server (Reverb)**:
   ```bash
   php artisan reverb:start
   ```

3. **Queue Worker** (Untuk kelancaran eksekusi *event broadcasting*):
   ```bash
   php artisan queue:work
   ```

4. **MQTT Listener** (Untuk menerima data pembaruan parkir dari perangkat IoT):
   ```bash
   php artisan mqtt:listen
   ```

5. **Scheduler** (Dibutuhkan untuk Auto Backup database):
   ```bash
   php artisan schedule:work
   ```

*Pastikan semua terminal tersebut dibiarkan tetap terbuka dan berjalan di background (tidak tertutup).*
