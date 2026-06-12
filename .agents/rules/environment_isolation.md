---
trigger: always_on
---

---
trigger: always_on
description: Membatasi ruang lingkup operasi AI Agent hanya pada folder pengembangan dan mencegah pemindaian file pada folder runtime Docker.
---

# Aturan Pemisahan Lingkungan Pengembangan dan Runtime Docker

Proyek ini menggunakan arsitektur pemisahan direktori fisik:
1. **Folder Pengembangan (Host OS/WSL)**: Direktori eksklusif untuk memodifikasi kode sumber aplikasi.
2. **Folder Runtime (Docker di WSL)**: Direktori instalasi terpisah yang mengonfigurasi dan mengeksekusi kontainer, mengacu pada struktur `INSTALLATION_DOCKER.md`.

Instruksi Operasional AI Agent:
- **Batasi Pemindaian (Scanning)**: Lakukan analisis, pencarian, dan modifikasi eksklusif pada Folder Pengembangan. Jangan memindai file konfigurasi atau status eksekusi pada Folder Runtime.
- **Isolasi Eksekusi**: Status aplikasi pada Folder Runtime bersifat *read-only* bagi AI Agent. JANGAN mencoba mengubah file konfigurasi Docker pada folder runtime ketika melakukan perubahan kode sumber.
- **Pengecualian Testing Otomatis**: Jangan menjalankan perintah automasi Docker (seperti `docker build` atau `docker compose up`) untuk menguji modifikasi kode. Proses pembuatan *image* dan eksekusi kontainer dikelola secara independen oleh pengguna di lingkungan WSL yang berbeda.