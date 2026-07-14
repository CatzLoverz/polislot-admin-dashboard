# Claude Code — Project Instructions

## Graphify Knowledge Graph

This project has a graphify knowledge graph at `graphify-out/`.

Rules:
- For codebase or architecture questions, when `graphify-out/graph.json` exists, first run `graphify query "<question>"` (CLI) or `query_graph` (MCP). Use `graphify path "<A>" "<B>"` / `shortest_path` for relationships and `graphify explain "<concept>"` / `get_node` for focused concepts. These return a scoped subgraph, usually much smaller than `GRAPH_REPORT.md` or raw grep output.
- If `graphify-out/wiki/index.md` exists, navigate it instead of reading raw files.
- Read `graphify-out/GRAPH_REPORT.md` only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code files in this session, run `graphify update .` to keep the graph current (AST-only, no API cost).

## Environment Isolation (Pemisahan Pengembangan dan Runtime)

Proyek ini menggunakan arsitektur pemisahan direktori fisik:
1. **Folder Pengembangan (Host OS/WSL)**: Direktori eksklusif untuk memodifikasi kode sumber aplikasi.
2. **Folder Runtime (Docker di WSL)**: Direktori instalasi terpisah yang mengonfigurasi dan mengeksekusi kontainer, mengacu pada struktur `INSTALLATION_DOCKER.md`.

Instruksi Operasional:
- **Batasi Pemindaian (Scanning)**: Lakukan analisis, pencarian, dan modifikasi eksklusif pada Folder Pengembangan. Jangan memindai file konfigurasi atau status eksekusi pada Folder Runtime.
- **Isolasi Eksekusi**: Status aplikasi pada Folder Runtime bersifat *read-only*. JANGAN mencoba mengubah file konfigurasi Docker pada folder runtime ketika melakukan perubahan kode sumber.
- **Pengecualian Testing Otomatis**: Jangan menjalankan perintah automasi Docker (seperti `docker build` atau `docker compose up`) untuk menguji modifikasi kode. Proses pembuatan *image* dan eksekusi kontainer dikelola secara independen oleh pengguna di lingkungan WSL yang berbeda.

## PHP & Laravel Coding Standards

AI Agent **DIWAJIBKAN** mematuhi aturan berikut saat menulis atau memodifikasi kode:

### 1. Import (Namespace / `use` Statement)
- **Gunakan Import di Atas:** DILARANG menggunakan *inline namespace imports* atau Fully Qualified Class Name di dalam baris logika kode (contoh yang salah: `\App\Models\User::find(1)`).
- **Kerapian:** Selalu letakkan `use` statement di bagian atas file (contoh yang benar: `use App\Models\User;`), lalu panggil nama class-nya saja di dalam kode. Kelompokkan dan urutkan import agar rapi.
- **Aturan ini juga berlaku di PHPDoc:** Semua class yang sudah di-`use` di atas file HARUS ditulis dengan *short name* (tanpa namespace prefix) di dalam tag `@param`, `@return`, `@throws`, `@var`, dsb. DILARANG menulis FQCN seperti `@return \Illuminate\Http\JsonResponse` jika class `JsonResponse` sudah di-import di atas.

### 2. PHPDoc (Komentar Fungsi & Class)
Setiap method/fungsi (terutama pada *Controller*, *Service*, dan *Helper*) WAJIB menyertakan blok PHPDoc standar:
- **Deskripsi Singkat:** Penjelasan ringkas (1-2 kalimat) mengenai tujuan fungsi.
- **Parameter (`@param`):** Sebutkan tipe data dan nama variabel (serta deskripsi singkat jika perlu).
- **Return (`@return`):** Definisikan tipe data yang dikembalikan (misalnya: `JsonResponse`, `View`, `bool`, `void`, dll).
- **Exception (`@throws`):** (Opsional) Jika fungsi secara eksplisit membuang exception tertentu.

*Contoh:*
```php
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Menyimpan data area parkir baru beserta lokasinya ke database.
 *
 * @param Request $request Data input dari form
 * @return RedirectResponse Redirect kembali ke index dengan pesan sukses
 */
```

### 3. Konvensi Penulisan Model (Eloquent)
- **Deklarasi Eksplisit:** Selalu deklarasikan properti kunci secara eksplisit:
  - `protected $table = 'nama_tabel';`
  - `protected $primaryKey = 'id_kolom';`
  - `protected $fillable = [...];` (Hindari `$guarded = []` secara membabi-buta).
- **Casting Tipe Data:** Gunakan `protected $casts = [...];` untuk casting data otomatis (seperti `boolean`, `array`, `json`, `datetime`).
- **Penamaan Relasi:**
  - Relasi *Singular* (HasOne, BelongsTo) → *camelCase* kata tunggal (misal: `public function parkArea()`).
  - Relasi *Plural* (HasMany, BelongsToMany) → *camelCase* kata jamak (misal: `public function parkSubareas()`).

### 4. Best Practices Laravel
- **Fat Model, Skinny Controller:** Hindari logika bisnis rumit di Controller. Controller cukup menerima request, delegasikan ke Service/Model, lalu return response.
- **Cegah N+1 Query:** Selalu gunakan *Eager Loading* (`with(...)` atau `load(...)`) saat mengambil data dengan relasi.
- **Form Request:** Untuk validasi panjang dan rumit, pisahkan ke class `FormRequest` khusus.
- **Helper Laravel:** Gunakan helper bawaan Laravel (`route()`, `asset()`, `config()`, `now()`, `collect()`, dsb) alih-alih fungsi PHP standar atau hardcode.
- **Konsistensi Format API:** Saat merespons JSON, gunakan format konsisten (misal: `status`, `message`, `data`). Gunakan method pembantu di `BaseController` jika tersedia.
- **Transaksi Database:** Gunakan `DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()` untuk operasi penulisan/modifikasi database yang krusial. Log error pada blok `catch` dan berikan pesan aman bagi pengguna.

## Development Cycle Workflow

Alur kerja untuk modifikasi kode sumber pada host OS dengan pemisahan tahapan *build* Docker secara manual:

1. **Modifikasi Kode Sumber**: Terapkan penulisan atau perbaikan kode secara langsung pada struktur direktori aplikasi di Folder Pengembangan.
2. **Validasi Sintaksis Lokal**: Lakukan pemeriksaan struktur dan logika sintaksis secara statis pada file yang dimodifikasi tanpa mencoba mengeksekusi aplikasi.
3. **Pemberitahuan Penyelesaian**: Setelah seluruh modifikasi selesai, konfirmasi kepada pengguna:
   > "Modifikasi kode sumber pada Folder Pengembangan telah selesai. Silakan lakukan kompilasi *image* Docker yang baru dan jalankan kontainer secara manual pada folder runtime di WSL untuk menerapkan perubahan ini."
