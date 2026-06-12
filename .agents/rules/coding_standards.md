---
trigger: always_on
description: Standar penulisan kode PHP dan Laravel untuk Agent agar mematuhi best practices pada proyek.
---

# Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

Proyek ini menggunakan standar penulisan kode yang bersih dan rapi mengikuti standar komunitas Laravel. AI Agent **DIWAJIBKAN** untuk mematuhi aturan berikut saat menulis atau memodifikasi kode:

## 1. Import (Namespace / `use` Statement)
- **Gunakan Import di Atas:** DILARANG menggunakan *inline namespace imports* atau nama class secara eksplisit penuh (Fully Qualified Class Name) di dalam baris logika kode (contoh yang salah: `\App\Models\User::find(1)`).
- **Kerapian:** Selalu letakkan `use` statement di bagian atas file (contoh yang benar: `use App\Models\User;`), lalu panggil nama class-nya saja di dalam kode. Kelompokkan dan urutkan import agar rapi.

## 2. PHPDoc (Komentar Fungsi & Class)
Setiap method/fungsi (terutama pada *Controller*, *Service*, dan *Helper*) yang dibuat WAJIB menyertakan blok PHPDoc yang standar:
- **Deskripsi Singkat:** Penjelasan ringkas (1-2 kalimat) mengenai tujuan fungsi tersebut.
- **Parameter (`@param`):** Sebutkan tipe data dan nama variabel (serta deskripsi singkat jika perlu).
- **Return (`@return`):** Definisikan tipe data yang dikembalikan (misalnya: `\Illuminate\Http\JsonResponse`, `\Illuminate\View\View`, `bool`, `void`, dll).
- **Exception (`@throws`):** (Opsional) Jika fungsi secara eksplisit membuang exception tertentu.

*Contoh Format:*
```php
/**
 * Menyimpan data area parkir baru beserta lokasinya ke database.
 *
 * @param \Illuminate\Http\Request $request Data input dari form
 * @return \Illuminate\Http\RedirectResponse Redirect kembali ke index dengan pesan sukses
 */
```

## 3. Konvensi Penulisan Model (Eloquent)
Agar kode model mudah dibaca dan diprediksi:
- **Deklarasi Eksplisit:** Meskipun Laravel bisa menebak secara otomatis, deklarasikan properti kunci secara eksplisit untuk memperjelas konteks tabel:
  - `protected $table = 'nama_tabel';`
  - `protected $primaryKey = 'id_kolom';`
  - `protected $fillable = [...];` (Hindari menggunakan `$guarded = []` secara membabi-buta demi keamanan Mass Assignment).
- **Casting Tipe Data:** Gunakan properti `protected $casts = [...];` untuk mendefinisikan casting data otomatis (seperti `boolean`, `array`, `json`, `datetime`).
- **Penamaan Relasi:** 
  - Relasi *Singular* (HasOne, BelongsTo) ditulis menggunakan *camelCase* kata tunggal (misal: `public function parkArea()`).
  - Relasi *Plural* (HasMany, BelongsToMany) ditulis menggunakan *camelCase* kata jamak (misal: `public function parkSubareas()`).

## 4. Best Practices Laravel Lainnya
- **Fat Model, Skinny Controller:** Hindari menulis logika bisnis (business logic) yang rumit langsung di *Controller*. *Controller* sebaiknya hanya menerima *request*, mendelegasikan pemrosesan ke *Service* atau *Model*, lalu mengembalikan *response*.
- **Cegah N+1 Query:** Selalu gunakan *Eager Loading* (`with(...)` atau `load(...)`) saat mengambil data dari database yang melibatkan relasi (terutama yang akan dilooping di dalam array/view).
- **Gunakan Form Request:** Untuk aturan validasi yang panjang dan rumit, disarankan memisahkannya ke dalam class `FormRequest` khusus daripada menggunakan `$request->validate()` langsung di *Controller*.
- **Helper Laravel:** Sebisa mungkin gunakan fungsi-fungsi bawaan *Helper* Laravel (`route()`, `asset()`, `config()`, `now()`, `collect()`, dsb) alih-alih menggunakan fungsi PHP standar yang lebih panjang atau hardcode string.
- **Konsistensi Format API:** Saat merespons *request* API (JSON), pastikan format selalu konsisten (memiliki properti struktur standar, misal: `status`, `message`, dan `data`). Gunakan *method* pembantu di `BaseController` jika tersedia (seperti `$this->sendSuccess()` atau `$this->sendError()`).
- **Penanganan Error & Transaksi Database:** SEBISA MUNGKIN gunakan blok `try...catch` dan transaksi database (`DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`) saat melakukan operasi penulisan/modifikasi ke database (Create, Update, Delete) yang krusial. Hal ini untuk mencegah data yang korup apabila terjadi kegagalan parsial di tengah-tengah proses. Log error pada blok `catch` dan berikan pesan yang aman bagi pengguna.
