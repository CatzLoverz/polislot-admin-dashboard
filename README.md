# Polislot Admin Dashboard

Dashboard administrasi untuk mengelola aplikasi Polislot, termasuk manajemen pengguna, area parkir, misi, hadiah, dan pemantauan validasi secara realtime.

## Prerequisites (Prasyarat)

Sebelum memulai instalasi, pastikan sistem Anda memiliki:

- **PHP**: Versi 8.2 atau lebih baru (Untuk Manual Setup).
- **Composer**: Untuk manajemen dependensi PHP.
- **MySQL / MariaDB**: Sebagai database.
- **Google Cloud Console Account**: Wajib, untuk **Maps SDK for JavaScript** (Admin Dashboard Map).
- **Cloudflare Account** (Opsional): Jika ingin menggunakan fitur Tunneling publik.

## Konfigurasi Environment

Sebelum menjalankan aplikasi, Anda perlu melengkapi beberapa variabel di `.env`:

### 1. Google Maps (Wajib)
Dapatkan API Key dari Google Cloud Console dengan library **Maps SDK for JavaScript** aktif.
```ini
GOOGLE_MAPS_API_KEY=AIzaSyDxxxx...
```

### 2. Cloudflare Tunnel (Opsional)
Jika ingin website admin bisa diakses publik via Tunnel (tanpa expose IP):
```ini
TUNNEL_TOKEN=eyJhIjxxxx...
```
*Catatan: Jika fitur ini tidak digunakan, harap comment atau hapus service `tunnel` pada file `docker-compose.yaml`.*
## Instalasi & Menjalankan Aplikasi

Silakan pilih satu opsi instalasi di bawah ini sesuai dengan sistem operasi Anda. Ikuti langkah demi langkah secara berurutan.

---

### Opsi A: Windows (Manual - Tanpa Docker)
**Cocok untuk:** Pengembangan lokal menggunakan Laragon atau XAMPP.

#### 1. Persiapan Direktori & Clone
Buka terminal (PowerShell/Command Prompt) dan masuk ke folder `www` atau `htdocs` Anda.
```powershell
git clone https://github.com/CatzLoverz/polislot-admin-dashboard.git
cd polislot-admin-dashboard
```

#### 2. Config Git Ownership
Jalankan perintah ini agar tidak ada masalah permission file.
```powershell
git config core.fileMode false
```

#### 3. Buka Code Editor
```powershell
code .
```

#### 4. Konfigurasi Environment (.env)
Copy file `.env.example` ke `.env`.
```powershell
copy .env.example .env
```
Sesuaikan konfigurasi database di file `.env` dengan database lokal Anda (misal: localhost).

#### 5. Generate Keys (Gunakan Git Bash)
⚠️ **PENTING**: Buka **Git Bash** di folder project (Klik Kanan -> Git Bash Here) dan jalankan perintah ini untuk membuat kunci enkripsi:
```bash
openssl genrsa -out storage/app/private/keys/private_key.pem 4096
openssl rsa -pubout -in storage/app/private/keys/private_key.pem -out storage/app/private/keys/public_key.pem
```

#### 6. Instalasi Dependency
Kembali ke terminal biasa/PowerShell:
```powershell
composer install
php artisan key:generate
```

#### 7. Migrasi Database
Pastikan database MySQL sudah dibuat (misal: `polislot_db`) dan config `.env` sudah sesuai.
```powershell
php artisan migrate
```

#### 8. Setup Akun Admin (Seeder)
Buka file `database/seeders/UserSeeder.php` di editor Anda.
*   Ubah email default `...` menjadi email admin valid yang Anda inginkan.
*   Setup password jika perlu.

#### 9. Seeding & RBAC Setup
Jalankan perintah ini satu per satu:
```powershell
# Isi data awal
php artisan db:seed

# Buat User Database Admin (Dashboard)
php artisan db:setup-admin polislot_admin PasswordAdmin123

# Buat User Database Mobile (App)
php artisan db:setup-user polislot_mobile PasswordMobile123
```

#### 10. Update Environment (.env)
Buka file `.env` dan **WAJIB** update credential database sekarang:
```ini
# Ganti user root dengan user admin baru
DB_USERNAME=polislot_admin
DB_PASSWORD=PasswordAdmin123

# Tambahkan user mobile untuk API
DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=PasswordMobile123
```

#### 11. Jalankan Server
```powershell
php artisan serve
```
Akses di: `http://localhost:8000`

---

### Opsi B: Windows + Docker Desktop
**Cocok untuk:** Menggunakan Docker container di Windows dengan WSL 2 backend.

#### 1. Persiapan Direktori
Buat folder `projects` di drive pilihan Anda.
```powershell
mkdir projects
cd projects
git clone https://github.com/CatzLoverz/polislot-admin-dashboard.git
cd polislot-admin-dashboard
```

#### 2. Config Git & Editor
```powershell
code .
git config core.fileMode false
```

#### 3. Konfigurasi Environment Docker
Copy dan siapkan file konfigurasi.
```powershell
copy .env.example .env.docker
copy docker-compose.example docker-compose.yaml
```
*   **Edit `.env.docker`**: Ubah `DB_HOST=127.0.0.1` menjadi `DB_HOST=db`.
*   **Edit `docker-compose.yaml`**: Comment service `tunnel` jika tidak pakai Cloudflare.

#### 4. Setup Network & Firewall (PowerShell Admin)
Agar web admin bisa diakses dari HP/Device lain. Jalankan di **PowerShell as Administrator**:

1.  Cek IP WSL: `wsl hostname -I` (Ambil IP pertama).
2.  Jalankan command berikut (Ganti `<IP_WSL>`):
    ```powershell
    netsh interface portproxy add v4tov4 listenport=8000 listenaddress=0.0.0.0 connectport=8000 connectaddress=<IP_WSL>
    New-NetFirewallRule -DisplayName "WSL Proxy 8000" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Private
    ```

#### 5. Generate Keys (Gunakan Git Bash)
⚠️ **PENTING**: Buka **Git Bash** di folder project dan jalankan command ini (Host Side):
```bash
openssl genrsa -out storage/app/private/keys/private_key.pem 4096
openssl rsa -pubout -in storage/app/private/keys/private_key.pem -out storage/app/private/keys/public_key.pem
```

#### 6. Start Docker Container
Pastikan Docker Desktop menyala.
```powershell
docker compose up -d --build
```

#### 7. Instalasi Dependency & Key App
```powershell
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

#### 8. Migrasi Database
```powershell
docker compose exec app php artisan migrate
```

#### 9. Setup Akun Admin (Seeder)
Buka `database/seeders/UserSeeder.php` dan edit email/password admin sesuai keinginan.

#### 10. Seeding & RBAC Setup
```powershell
# 1. Seeding Data
docker compose exec app php artisan db:seed

# 2. Setup Database Users
docker compose exec app db:setup-admin polislot_admin PasswordAdmin123
docker compose exec app db:setup-user polislot_mobile PasswordMobile123
```

#### 11. Update Environment (.env)
Buka `.env` (atau `.env.docker` yang dimount ke `.env`) dan update:
```ini
DB_USERNAME=polislot_admin
DB_PASSWORD=PasswordAdmin123

DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=PasswordMobile123
```

#### 12. Restart Server
```powershell
docker compose restart
```
Akses di: `http://localhost:8000` (atau IP Komputer untuk akses dari HP).

---

### Opsi C: Linux / WSL 2 + Docker (Recommended)
**Cocok untuk:** Production server, Ubuntu, atau WSL 2 native.

#### 1. Persiapan Direktori
```bash
mkdir projects && cd projects
git clone https://github.com/CatzLoverz/polislot-admin-dashboard.git
cd polislot-admin-dashboard
code .
```

#### 2. Config Git
```bash
git config core.fileMode false
```

#### 3. Konfigurasi Environment & Logrotate
```bash
cp .env.example .env.docker
# Edit .env.docker: Ubah DB_HOST=db

cp docker-compose.example docker-compose.yaml
# Edit docker-compose.yaml: Atur tunnel/root password

cp docker/logrotate.example docker/logrotate
# Edit docker/logrotate: Sesuaikan path project
```

#### 4. Generate Keys (Host)
Jalankan di terminal host (bukan di dalam docker):
```bash
openssl genrsa -out storage/app/private/keys/private_key.pem 4096
openssl rsa -pubout -in storage/app/private/keys/private_key.pem -out storage/app/private/keys/public_key.pem
```

#### 5. Start Server (Setup Script)
Script ini otomatis mengatur permission.
```bash
chmod +x setup.sh
./setup.sh
```

#### 6. Install Dependency
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

#### 7. Migrasi
```bash
docker compose exec app php artisan migrate
```

#### 8. Setup Seeder & RBAC
1.  **Edit Seeder**: Ubah `database/seeders/UserSeeder.php` sekarang.
2.  **Jalankan Command**:
    ```bash
    docker compose exec app php artisan db:seed
    docker compose exec app db:setup-admin polislot_admin PasswordAdmin123
    docker compose exec app db:setup-user polislot_mobile PasswordMobile123
    ```

#### 9. Update Environment
Update file `.env` (atau `.env.docker`) dengan credential baru:
```ini
DB_USERNAME=polislot_admin
DB_PASSWORD=PasswordAdmin123

DB_USERNAME_MOBILE=polislot_mobile
DB_PASSWORD_MOBILE=PasswordMobile123
```

#### 10. Restart
```bash
docker compose up -d
```
Akses di: `http://localhost:8000`



## Manajemen Database (Backup & Restore)

Aplikasi ini dilengkapi fitur built-in untuk backup dan restore database.

### 1. Backup Manual

**A. Manual Setup (Local):**
```bash
php artisan db:backup
```

**B. Docker Setup (Windows/Linux):**
```bash
docker compose exec app php artisan db:backup
```
*File backup akan tersimpan di `storage/app/backups/manual/`.*

### 2. Restore Database
**Perhatian**: Restore akan menimpa seluruh data database saat ini.
Gunakan nama file yang didapat dari perintah `db:list`.

**A. Manual Setup (Local):**
```bash
php artisan db:list
# Format: php artisan db:restore <path/to/filename.sql>
php artisan db:restore manual/2025-12-22-23-59-59_backup.sql
```

**B. Docker Setup (Windows/Linux):**
```bash
docker compose exec app php artisan db:list
docker compose exec app php artisan db:restore manual/2025-12-22-23-59-59_backup.sql
```

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
