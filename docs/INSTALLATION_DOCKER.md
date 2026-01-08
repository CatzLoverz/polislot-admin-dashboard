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

### Khusus Pengguna Linux
Berikan izin eksekusi pada file entrypoint yang sudah dicopy ke root:
```bash
chmod +x logrotate-entrypoint.sh
```

---

## Langkah Instalasi

Ikuti langkah-langkah berikut secara berurutan.

### 1. Konfigurasi Environment (.env)
Salin file `.env.example` menjadi `.env`.
```bash
cp .env.example .env
```
Buka `.env` dan atur konfigurasi berikut:
- **Database Root**: Samakan password dengan `docker-compose.yml`.
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

### 2. Atur docker-compose.yml
Pastikan `docker-compose.yml` (di root) memiliki konfigurasi `MARIADB_ROOT_PASSWORD` yang kuat dan sesuai dengan yang Anda inginkan.

### 3. Generate RSA Keys (Di Root)
**Wajib:** Generate pasangan kunci RSA untuk enkripsi API di **Root Folder** (sejajar dengan `docker-compose.yml`). Docker akan memount file ini ke lokasi yang tepat di dalam container.

```bash
openssl genrsa -out private_key.pem 4096
openssl rsa -pubout -in private_key.pem -out public_key.pem
```
*Note: Public key bisa disimpan untuk referensi, private key wajib ada.*

### 4. Verifikasi Credential
Pastikan credential root di `.env` (DB_PASSWORD) cocok dengan `MARIADB_ROOT_PASSWORD` di `docker-compose.yml`.

### 5. Menjalankan Container
Jalankan Docker Compose.
```bash
docker compose up -d
```

### 6. Generate Application Key
Generate key aplikasi Laravel di dalam container.
```bash
docker compose exec app php artisan key:generate
```

### 7. Migrasi Database
Jalankan migrasi database (fresh install).
```bash
docker compose exec app php artisan migrate --fresh
```

### 8. Setup Admin User (Seeding)
Jalankan seeder untuk membuat data awal dan akun admin (menggunakan `ADMIN_EMAIL` & `ADMIN_PASSWORD` dari `.env`).
```bash
docker compose exec app php artisan db:seed
```

### 9. Setup Database Roles
Buat user database khusus untuk aplikasi (RBAC) agar lebih aman.
```bash
# Setup user database untuk Admin Dashboard
docker compose exec app php artisan db:setup-admin polislot_admin password_db_admin

# Setup user database untuk Mobile API
docker compose exec app php artisan db:setup-user polislot_mobile password_db_mobile
```

### 10. Atur Ulang Environment (.env) - PENTING
Buka file `.env` kembali dan **GANTI** credential database root dengan user yang baru saja dibuat.

```ini
# Ganti dengan user "polislot_admin" yang dibuat di langkah 9
DB_USERNAME=polislot_admin
DB_PASSWORD=password_db_admin

# Isi juga bagian Mobile User
DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=password_db_mobile
```

### 11. Re-up Container
Jalankan kembali docker compose untuk menerapkan perubahan environment dan memastikan koneksi user baru berhasil.
```bash
docker compose up -d --force-recreate
```

Instalasi selesai! Aplikasi sekarang dapat diakses di `http://localhost:8080` (atau port yang Anda konfigurasi).
