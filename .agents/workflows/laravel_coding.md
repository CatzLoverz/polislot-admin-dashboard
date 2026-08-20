---
name: laravel_coding
description: Jalankan pengecekan dan penyesuaian standar kode Laravel/PHP menggunakan Laravel Pint dan aturan proyek.
---

# Workflow: Laravel Coding Standards

Alur kerja untuk memvalidasi dan memformat kode PHP & Laravel sesuai standar proyek.

## Langkah-langkah:
1. **Pengujian Format Kode**:
   Jalankan pemeriksaan format kode dengan Laravel Pint (mode test):
   ```powershell
   ./vendor/bin/pint --test
   ```
2. **Perbaikan Otomatis**:
   Jika terdapat pelanggaran gaya penulisan kode, jalankan perbaikan otomatis:
   ```powershell
   ./vendor/bin/pint
   ```
3. **Verifikasi Standar Kode Proyek**:
   Periksa kesesuaian kode dengan aturan pada `.agents/rules/coding_standards.md`:
   - Pastikan semua import class menggunakan `use` di bagian atas file (tanpa inline FQCN).
   - Pastikan tag PHPDoc (`@param`, `@return`, `@var`, `@throws`) menggunakan *short name* tanpa FQCN jika class sudah di-import di atas.
   - Pastikan model Eloquent memiliki deklarasi eksplisit `$table`, `$primaryKey`, `$fillable`, dan `$casts`.
   - Pastikan relasi model menggunakan penamaan singular/plural camelCase yang tepat.
   - Pastikan operasi mutasi database krusial menggunakan transaksi database `DB::beginTransaction()`, `DB::commit()`, dan `DB::rollBack()` di dalam blok `try...catch`.
