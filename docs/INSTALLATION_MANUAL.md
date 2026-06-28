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

### 8. Menjalankan Service Aplikasi
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
