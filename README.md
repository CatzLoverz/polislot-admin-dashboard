# Polislot Admin Dashboard

Dashboard administrasi untuk mengelola aplikasi Polislot, termasuk manajemen pengguna, area parkir, misi, hadiah, dan pemantauan validasi secara realtime.

## Prerequisites (Prasyarat)

Sebelum memulai instalasi, pastikan sistem Anda memiliki:

- **PHP**: Versi 8.1 atau lebih baru.
- **Composer**: Untuk manajemen dependensi PHP.
- **MySQL / MariaDB**: Sebagai database.
- **OpenSSL**: Untuk men-generate kunci enkripsi API.

## Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek di local environment:

### 1. Setup Awal Laravel
Clone repositori dan install dependensi:

```bash
git clone <repository_url>
cd polislot-admin-dashboard

# Install PHP Dependencies
composer install
```

Salin konfigurasi environment dan generate Key aplikasi:

**Linux / Mac / Git Bash:**
```bash
cp .env.example .env
php artisan key:generate
```

**Windows (CMD / PowerShell):**
```powershell
copy .env.example .env
php artisan key:generate
```

### 2. Konfigurasi Database & Migrasi
Buat database baru di MySQL (misal: `polislot_db`), lalu sesuaikan file `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=polislot_db
DB_USERNAME=root
DB_PASSWORD=
```

#### Penting: Setup Akun Admin
Karena aplikasi **tidak memiliki fitur pendaftaran untuk Admin**, Anda harus mengatur email dan password admin secara manual melalui seeder.
1. Buka file `database/seeders/UserSeeder.php`.
2. Ubah bagian `email` dan `password` pada blok `User::create` untuk admin.
3. Simpan perubahan.

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

### 3. Pemisahan Akun Database (RBAC)

Gunakan command Laravel yang telah disiapkan untuk membuat user database aplikasi secara otomatis.

> [!WARNING]
> **Catatan Keamanan**: Menjalankan command dengan password sebagai argumen dapat menyimpannya di *history terminal* Anda.
> Gunakan command ini di environment development aman, atau bersihkan history setelahnya (`history -c` di Linux/Mac). Jika di Production, disarankan menggunakan input interaktif atau SQL manual untuk keamanan maksimal.

```bash
# Format: php artisan db:setup-user <username> <password>
php artisan db:setup-user polislot_app password_kuat_anda
```

Setelah sukses, update `.env` Anda dengan kredensial baru tersebut:
```ini
DB_USERNAME=polislot_app
DB_PASSWORD=password_kuat_anda
```

### 4. Setup Enkripsi API (Private Key)
Aplikasi ini menggunakan enkripsi *End-to-End* untuk komunikasi API. Anda perlu men-generate pasangan kunci RSA 4096-bit.

**Linux / Mac / Laragon Terminal:**
```bash
# Buat folder
mkdir -p storage/app/private/keys

# Generate Private Key (4096 bit)
openssl genrsa -out storage/app/private/keys/private_key.pem 4096

# Extract Public Key (Opsional, untuk dibagikan ke Mobile App)
openssl rsa -in storage/app/private/keys/private_key.pem -pubout -out storage/app/private/keys/public_key.pem

# Set Permissions (Linux/Mac Only)
chmod 600 storage/app/private/keys/private_key.pem
```

**Windows (CMD / PowerShell):**
Pastikan `openssl` sudah terinstall (biasanya bawaan Git/Laragon).

```powershell
# Buat folder
mkdir storage\app\private\keys

# Generate Private Key
openssl genrsa -out storage/app/private/keys/private_key.pem 4096

# Extract Public Key
openssl rsa -in storage/app/private/keys/private_key.pem -pubout -out storage/app/private/keys/public_key.pem
```
*Catatan: Windows tidak memerlukan `chmod`. Akses file dilindungi oleh ACL sistem operasi.*

## Menjalankan Aplikasi

### 1. Jalankan Web Server
Gunakan perintah bawaan Laravel:
```bash
php artisan serve
```
Akses dashboard di: `http://localhost:8000`

### 2. Aktifkan Backup Otomatis & Scheduler
Agar fitur **Backup Database Otomatis** berjalan, worker scheduler harus aktif:

```bash
php artisan schedule:work
```
*Catatan: Di production, tambahkan entri ke Crontab server.*

## Struktur Direktori

Berikut adalah gambaran umum struktur direktori penting proyek ini:

```
polislot-admin-dashboard/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # Controller untuk Endpoint Mobile App
│   │   │   └── Web/          # Controller untuk Dashboard Admin Web
│   │   └── Middleware/
│   │       └── ApiEncryption.php # Logic enkripsi dekripsi request/response
│   ├── Models/               # Eloquent Models (User, Mission, ParkArea, dll)
│   └── Services/             # Business Logic (MissionService, HistoryService)
├── database/
│   ├── migrations/           # Struktur tabel database
│   └── seeders/              # Data dummy/awal
├── resources/
│   └── views/
│       ├── Contents/         # Halaman-halaman Dashboard (Dashboard, Rewards, Users)
│       └── Layouts/          # Template utama (Header, Sidebar, Footer)
├── routes/
│   ├── api.php               # Rute API (Mobile)
│   └── web.php               # Rute Web (Dashboard)
└── storage/
    └── app/
        └── private/keys/     # Lokasi penyimpanan Kunci Enkripsi (Rahasia!)
```
