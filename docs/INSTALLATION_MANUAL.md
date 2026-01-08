# Instalasi Manual (Tanpa Docker)

Panduan ini menjelaskan cara menginstal dan menjalankan aplikasi Polislot Admin Dashboard secara manual (Localhost/XAMPP/Laragon/VPS).

## Prasyarat

Sebelum memulai, pastikan sistem Anda memiliki:
- **PHP**: Versi 8.4 atau lebih baru.
- **Composer**: Untuk manajemen dependensi PHP.
- **MySQL / MariaDB**: Sebagai database server.
- **Web Server**: Apache atau Nginx.
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

### 2. Generate RSA Keys (Wajib)
Generate pasangan kunci RSA untuk enkripsi API agar aplikasi mobile dapat terhubung.
```bash
mkdir -p storage/app/private/keys
openssl genrsa -out storage/app/private/keys/private_key.pem 4096
openssl rsa -pubout -in storage/app/private/keys/private_key.pem -out storage/app/private/keys/public_key.pem
```

### 3. Instalasi Dependency
Install library PHP yang dibutuhkan menggunakan Composer.
```bash
composer install
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

### 8. Jalankan Aplikasi
Jalankan server pengembangan lokal.
```bash
php artisan serve
```
Aplikasi dapat diakses di `http://localhost:8080`.

### 9. Mengaktifkan Scheduler (Backup Otomatis)
Fitur backup otomatis memerlukan scheduler yang berjalan. Buka terminal baru dan jalankan:
```bash
php artisan schedule:work
```
*Biarkan perintah ini berjalan di background/terminal terpisah.*
