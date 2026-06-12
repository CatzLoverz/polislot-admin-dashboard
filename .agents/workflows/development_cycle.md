---
description: Alur kerja untuk modifikasi kode sumber pada host OS dengan pemisahan tahapan *build* 
---

---
name: development_cycle
description: Alur kerja untuk modifikasi kode sumber pada host OS dengan pemisahan tahapan *build* Docker secara manual.
---

# Workflow: Siklus Pengembangan Kode Tersendiri

Alur kerja ini mengatur prosedur penulisan kode tanpa mengganggu lingkungan runtime kontainer yang berjalan secara terpisah.

Langkah-langkah Eksekusi:
1. **Modifikasi Kode Sumber**: Terapkan penulisan atau perbaikan kode secara langsung pada struktur direktori aplikasi di Folder Pengembangan.
2. **Validasi Sintaksis Lokal**: Lakukan pemeriksaan struktur dan logika sintaksis secara statis pada file yang dimodifikasi tanpa mencoba mengeksekusi aplikasi.
3. **Pemberitahuan Penyelesaian Modifikasi**: Setelah seluruh modifikasi kode selesai diimplementasikan, berikan konfirmasi akhir kepada pengguna dengan pernyataan berikut:
   "Modifikasi kode sumber pada Folder Pengembangan telah selesai. Silakan lakukan kompilasi *image* Docker yang baru dan jalankan kontainer secara manual pada folder runtime di WSL untuk menerapkan perubahan ini."