# Polislot Admin Dashboard

Dashboard administrasi untuk mengelola aplikasi Polislot, termasuk manajemen pengguna, area parkir, misi, hadiah, dan pemantauan validasi secara realtime.

## 🚀 Fitur Utama

- **Dashboard Realtime**: Monitoring aktivitas pengguna dan validasi.
- **Manajemen User**: Pengelolaan akun pengguna mobile dan admin.
- **Area Parkir & Misi**: Pengaturan lokasi parkir dan misi yang tersedia.
- **Manajemen Hadiah**: Pengaturan hadiah yang dapat ditukarkan.
- **Database Backup & Restore**:
    - Backup otomatis terjadwal (Hourly, Daily, 3-Days).
    - Backup manual via command line.
    - Restore database lengkap.
- **Keamanan Database (RBAC)**: Pemisahan privilege user database antara Admin Dashboard dan Mobile App.
- **API Encryption**: Enkripsi end-to-end (RSA) untuk payload API mobile application.

## 🛠️ Tech Stack

- **Framework**: Laravel 12 (PHP 8.4+)
- **Database**: MySQL / MariaDB
- **Frontend**: Blade Templates (Admin Dashboard)
- **API**: Token via Sanctum (Mobile App Auth)

### Prerequisites External Service

Untuk fitur penuh, aplikasi ini membutuhkan:
- **Google Cloud Console Account**: Wajib untuk **Maps SDK for JavaScript** (Digunakan pada Map Dashboard). API Key harus dimasukkan ke `.env`.
- **Cloudflare Account** (Opsional): Diperlukan jika ingin menggunakan tunneling publik yang terintegrasi di `docker-compose`.

## 📂 Struktur Direktori

Berikut adalah gambaran umum struktur direktori penting proyek ini:

```
polislot-admin-dashboard/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Logic Aplikasi
│   │   └── Middleware/       # Middleware termasuk RSA encryption
│   └── Services/             # Business Logic
├── docker/                   # Konfigurasi Docker & scripts
├── database/                 # Migrations & Seeders
├── docs/                     # Dokumentasi Instalasi
│   ├── INSTALLATION_DOCKER.md
│   └── INSTALLATION_MANUAL.md
├── resources/views/          # Halaman Web (Blade)
├── routes/                   # Definisi URL (web.php & api.php)
└── storage/
    ├── app/
    │   ├── private/keys      # Lokasi RSA Keys (Generated saat install)		
    │   └── public            # Storage penyimpanan media
    └── logs/                 # Log laravel dan mariadb
```

## 📖 Panduan Instalasi

Silakan pilih metode instalasi yang sesuai dengan kebutuhan Anda:

- **[🐳 Instalasi Docker (Recommended)](docs/INSTALLATION_DOCKER.md)**  
  Instalasi mudah menggunakan Docker Compose. Cocok untuk environment Linux, Windows (WSL2), dan Production. Termasuk container untuk Database, Scheduler, dan Logrotate.

- **[💻 Instalasi Manual](docs/INSTALLATION_MANUAL.md)**  
  Instalasi manual menggunakan PHP & Composer di mesin lokal (XAMPP/Laragon/Native Linux).

## 🧰 Custom Utility Commands

Aplikasi ini dilengkapi dengan custom artisan commands untuk mempermudah maintenance:

| Command | Deskripsi | Contoh Usage |
| :--- | :--- | :--- |
| `php artisan db:backup` | Backup database manual ke `storage/app/backups/manual/` | `php artisan db:backup` |
| `php artisan db:list` | Melihat daftar file backup yang tersedia | `php artisan db:list` |
| `php artisan db:restore` | Restore database dari file backup tertentu | `php artisan db:restore manual/backup.sql` |
| `php artisan db:setup-admin` | Membuat user database khusus Admin Dashboard (RBAC) | `php artisan db:setup-admin user pass` |
| `php artisan db:setup-user` | Membuat user database khusus Mobile API (RBAC) | `php artisan db:setup-user user pass` |
| `php artisan backup:clean` | Menghapus backup manual yang lebih lama dari X hari | `php artisan backup:clean --days=7` |
| `php artisan schedule:work` | Menjalankan scheduler (untuk Auto Backup) | `php artisan schedule:work` |

---
*Dibuat oleh Tim PBL-TRPL409*
