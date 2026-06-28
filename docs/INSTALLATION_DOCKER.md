# Instalasi dengan Docker

Panduan ini menjelaskan cara menginstal dan menjalankan aplikasi Polislot Admin Dashboard menggunakan Docker.

## Prasyarat

- **Docker**: (Docker Engine atau Docker Desktop).

*Pastikan Anda juga telah memenuhi requirements external service (Google Cloud / Cloudflare) yang disebutkan di README utama.*

### Persiapan File Konfigurasi

Karena instalasi ini terpisah dari source code, Anda perlu **Menyalin** beberapa file dari folder `docker/` ke **Root Directory** folder baru Anda tersendiri:

1. Copy `docker/docker-compose.yml` -> ke root.
2. Copy `docker/mariadb.cnf` -> ke root.
3. Copy `docker/logrotate-entrypoint.sh` -> ke root.
4. Copy `docker/mosquitto.conf` -> ke root.
5. Copy `.env.example` -> ke root (Rename menjadi `.env`).

### Khusus Pengguna Linux
Berikan izin eksekusi pada file entrypoint yang sudah dicopy ke root:
```bash
chmod +x logrotate-entrypoint.sh
```

---

## Langkah Instalasi

Ikuti langkah-langkah berikut secara berurutan.

### 1. Konfigurasi Environment (.env)
Buka `.env` dan atur konfigurasi berikut:
- **Database Configuration (Docker)**: Ubah konfigurasi database agar sesuai dengan service container.
    ```ini
    DB_CONNECTION=mariadb
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=polislot
    DB_USERNAME=root
    DB_PASSWORD=password_root_yang_sama_dengan_compose
    ```
- **Admin Setup**: Isi variabel dibawah ini agar akun admin otomatis terbuat saat seeding.
    > **PENTING**: Gunakan **EMAIL YANG VALID** karena kode OTP untuk reset password akan dikirimkan ke email ini.
    ```ini
    ADMIN_EMAIL=email_valid_anda@gmail.com
    ADMIN_PASSWORD=password_aman_anda
    ```
- **SMTP Mailer**: Agar OTP dapat terkirim, Anda **WAJIB** mengonfigurasi pengaturan email (Gmail/Mailtrap/Provider lain).
    ```ini
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=email_anda@gmail.com
    MAIL_PASSWORD=app_password_anda
    MAIL_ENCRYPTION=tls
    ```
- **External Services**:
    - **Google Maps**: Wajib diisi agar fitur peta di dashboard berfungsi.
    ```ini
    GOOGLE_MAPS_JS="isi_api_key_google_cloud_anda"
    ```
    - **Cloudflare Tunnel (Opsional)**: Isi token ini jika Anda menggunakan fitur tunneling.
    ```ini
    TUNNEL_TOKEN="isi_token_cloudflare_tunnel_anda"
    ```
- **MQTT Authentication (Mosquitto)**: Isi kredensial untuk broker MQTT. Autentikasi broker ini telah **diotomatisasi secara penuh** di dalam Docker kontainer pada saat kontainer dijalankan menggunakan variabel ini. Anda tidak perlu membuat berkas password secara manual.
    ```ini
    MQTT_AUTH_USERNAME=MQTTPoliSlot
    MQTT_AUTH_PASSWORD=password_mqtt_yang_aman
    MQTT_MOBILE_USERNAME=MQTTPoliSlotMobile
    MQTT_MOBILE_PASSWORD=mobile_secure_password
    ```


### 2. Generate RSA Keys (Di Root)
**Wajib:** Generate pasangan kunci RSA untuk enkripsi API di **Root Folder** (sejajar dengan `docker-compose.yml`). Docker akan memount file ini ke lokasi yang tepat di dalam container.

```bash
openssl genrsa -out private_key.pem 4096
openssl rsa -pubout -in private_key.pem -out public_key.pem
```
*Note: Public key bisa disimpan untuk referensi, private key wajib ada.*

### 3. Verifikasi Credential
Pastikan credential root di `.env` (DB_PASSWORD) cocok dengan `MARIADB_ROOT_PASSWORD` di `docker-compose.yml`.

### 4. Menjalankan Container
Jalankan Docker Compose.
```bash
docker compose up -d
```

### 5. Generate Application Key
Generate key aplikasi Laravel di dalam container.
```bash
docker compose exec app php artisan key:generate
```

### 6. Migrasi Database
Jalankan migrasi database (fresh install).
```bash
docker compose exec app php artisan migrate --fresh
```

### 7. Setup Admin User (Seeding)
Jalankan seeder untuk membuat data awal dan akun admin (menggunakan `ADMIN_EMAIL` & `ADMIN_PASSWORD` dari `.env`).
```bash
docker compose exec app php artisan db:seed
```

### 8. Setup Database Roles
Buat user database khusus untuk aplikasi (RBAC) agar lebih aman.
```bash
# Setup user database untuk Admin Dashboard
docker compose exec app php artisan db:setup-admin polislot_admin password_db_admin

# Setup user database untuk Mobile API
docker compose exec app php artisan db:setup-user polislot_mobile password_db_mobile
```

### 9. Atur Ulang Environment (.env) - PENTING
Buka file `.env` kembali dan **GANTI** credential database root dengan user yang baru saja dibuat.

```ini
# Ganti dengan user "polislot_admin" yang dibuat di langkah 9
DB_USERNAME=polislot_admin
DB_PASSWORD=password_db_admin

# Isi juga bagian Mobile User
DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=password_db_mobile
```

### 10. Re-up Container
Jalankan kembali docker compose untuk menerapkan seluruh perubahan environment (termasuk kredensial MQTT dan user database baru) dan memastikan seluruh sistem berjalan dengan benar.
```bash
docker compose up -d --force-recreate
```

### 11. Buat port forward local dan firewall rule - Optional untuk instalasi pada WSL
Jika Anda menginstal dan menjalankan Docker di dalam WSL (Windows Subsystem for Linux), secara default layanan tersebut hanya bisa diakses dari komputer *host* (localhost). Agar aplikasi (Web Dashboard dan broker MQTT) dapat diakses oleh *device* lain di jaringan lokal (LAN) yang sama, Anda perlu membuka akses *port forwarding* dan Windows Firewall.

Kami telah menyediakan skrip PowerShell untuk mengotomatisasi proses ini. Copy file **wsl-port-forward.ps1** tempatkan pada host/windows Anda (bukan WSL). Jalankan aplikasi **PowerShell** sebagai **Administrator** (Run as Administrator), arahkan ke *root directory* proyek, kemudian eksekusi skrip berikut:

```powershell
powershell.exe -ExecutionPolicy Bypass -File .\wsl-port-forward.ps1
```

> **Catatan**: Skrip ini akan melakukan *port forwarding* (port 8080, 1883, dan 9001) dari IP WSL ke `0.0.0.0` dan secara otomatis menambahkan aturan di Windows Firewall agar *inbound connection* diizinkan. Biarkan jendela PowerShell tetap terbuka (skrip terus berjalan). Jika Anda ingin menghentikan *port forwarding* dan menghapus aturan *firewall* yang telah dibuat, cukup tekan `CTRL+C` pada terminal PowerShell tersebut.

---

🎉 **Instalasi selesai!** Aplikasi sekarang dapat diakses melalui browser:
- Dari komputer lokal: `http://localhost:8080`
- Dari *device* lain di jaringan yang sama: `http://<IP_ADDRESS_KOMPUTER_ANDA>:8080`
