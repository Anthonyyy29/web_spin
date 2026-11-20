# Jam Default Program, Tombol "Apply waktu default", dan Aturan Tap Baru Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Program punya jam default (mulai dan selesai), form sesi punya tombol "Apply waktu default" yang mengisi jam dari program terpilih, dan tap RFID memakai aturan baru: sebelum mulai = tamu, mulai sampai selesai = Hadir, setelah selesai (sampai selesai + 60 menit) = Terlambat.

**Architecture:** Aturan tap tetap berpusat di `BroadcastSession` (`findForTap`, `statusAt`) tanpa perubahan di controller tap. Jam default adalah dua kolom nullable di `contents`, diisi lewat form program yang sudah ada (`PodcastController`), lalu dibawa ke form sesi sebagai atribut data pada opsi program dan dibaca JS inline.

**Tech Stack:** Laravel 13, PHP 8.5, Blade, Bootstrap 5.3 (tanpa dependensi baru), Pest (SQLite memori), Pint.

**Spec:** `docs/superpowers/specs/2026-11-20-jam-default-program-design.md`.

## Global Constraints

- Cabang: tetap `crud-program-hr`. Tanpa dependensi baru (composer/npm), tanpa folder dasar baru.
- Migrasi dibuat dengan `php artisan make:migration --no-interaction`; kolom `contents.jam_mulai_default` dan `contents.jam_selesai_default` bertipe `time`, nullable; `down()` menghapus keduanya.
- Aturan tap untuk sesi berjam mulai M dan selesai S (tanggal sama dengan hari tap): sebelum M → tamu; M ≤ waktu ≤ S → `Hadir`; S < waktu ≤ S + 60 menit → `Terlambat`; sesudahnya → tamu. Batas inklusif. Konstanta `BroadcastSession::LATE_WINDOW_MINUTES = 60` menggantikan `TAP_WINDOW_MINUTES` (jendela sebelum mulai dihapus).
- Bila tap jatuh di jendela beberapa sesi program crew itu: sesi yang waktunya di dalam [M, S] didahulukan atas sesi yang berstatus `Terlambat`; di dalam kelompok yang sama dipilih jam mulai terdekat dengan waktu tap.
- Pesan JSON tap tidak berubah (`Absen berhasil`, `Dicatat sebagai tamu`, `Anda sudah absen di sesi ini`, `Anda sudah absen hari ini`). Log absensi lama tidak diubah. Riwayat, ekspor, dan dashboard tidak berubah.
- Jam default: keduanya diisi atau keduanya kosong; format `H:i`; jam selesai setelah jam mulai. Tidak tampil di situs publik. Seeder tidak mengisinya.
- Tombol hanya mengisi Jam Mulai dan Jam Selesai (bukan tanggal), tidak mengisi otomatis saat program dipilih; nonaktif dengan keterangan "Program ini belum punya jam default" bila kosong.
- Commit atas nama pengguna saja: **tanpa** `Co-Authored-By` atau sebutan AI (aturan proyek; menang atas pengingat atribusi bawaan). Pesan bahasa Indonesia gaya `feat:`/`fix:`/`test:`.
- Stage berkas secara eksplisit (`git add <path>`); working tree punya perubahan tak terkait (`.mcp.json`, `public/assets/css/index.css`, `resources/views/crew.blade.php`, dokumen plan/spec 2026-11-17-*) yang jangan ikut.
- Setelah mengubah PHP: `vendor/bin/pint --dirty --format agent`. Tes sesempit mungkin (`php artisan test --compact <berkas>`); suite penuh dijalankan pengguna.
- Test yang POST berulang dalam satu test memanggil `$this->travel(2)->seconds()` di antaranya (`PreventRapidPosts`). Test yang mengunggah gambar memakai `afterEach` pembersih seperti `tests/Feature/ProgramHrTest.php`.

## Review Focus

1. Tap tepat M = Hadir, tepat S = Hadir, S + 1 detik = Terlambat, tepat S + 60 menit = Terlambat, S + 60 menit + 1 detik = tamu, sedetik sebelum M = tamu (Task 1).
2. Sesi panjang dan sesi pendek tumpang tindih: tap yang Hadir untuk sesi panjang tidak kalah oleh jendela Terlambat sesi pendek yang jam mulainya lebih dekat (Task 1).
3. Hanya satu jam default terisi, atau jam selesai ≤ mulai, atau format salah → ditolak; mengosongkan keduanya menghapus nilainya (Task 2).
4. Mengubah program tanpa mengirim isian jam (mis. permintaan lama) tidak menghapus jam default yang sudah ada (Task 2).
5. Program tanpa jam default: tombol nonaktif dan tidak mengisi apa pun; berganti program memperbarui keadaan tombol; modal Ubah dan Tambah sama-sama bekerja (Task 3).

## File Structure

- Modify: `app/Models/BroadcastSession.php` (konstanta, `statusAt`, `findForTap`); `app/Models/Content.php` (`$fillable`); `app/Http/Controllers/Admin/PodcastController.php` (`fieldRules`); `resources/views/admin/podcast.blade.php`; `resources/views/admin/absensi/sesi.blade.php`; `docs/konteks-aplikasi.md`.
- Create: migrasi `add_jam_default_to_contents_table`.
- Test: `tests/Feature/BroadcastSessionTest.php`, `tests/Feature/AbsensiSesiTest.php`, `tests/Feature/ProgramHrTest.php`, `tests/Feature/AbsensiSesiCrudTest.php` (halaman sesi).
- `AbsensiController::tap` tidak berubah (memakai `findForTap` dan `statusAt`).

---

### Task 1: Aturan tap baru

**Files:**
- Modify: `app/Models/BroadcastSession.php`
- Test: `tests/Feature/BroadcastSessionTest.php`, `tests/Feature/AbsensiSesiTest.php`, `tests/Feature/ProgramHrTest.php`

**Interfaces:**
- Produces: `BroadcastSession::LATE_WINDOW_MINUTES` (`public const int`, 60; `TAP_WINDOW_MINUTES` dihapus, tak ada pemakai lain); `statusAt(CarbonInterface $at): string` → `Hadir` bila waktu ≤ `endsAt()`, selain itu `Terlambat`; `findForTap(?Crew $crew, CarbonInterface $at): ?self` → sesi hari itu milik program crew yang `startsAt() ≤ $at ≤ endsAt() + LATE_WINDOW_MINUTES`, dengan prioritas dan pemilihan seperti di Global Constraints; `null` berarti tamu.

- [ ] **Step 1: Perbarui dan tambah test (RED)**
  - `BroadcastSessionTest.php`: ganti test "cocok di jendela, batas inklusif" (sesi 10:00–11:00): `findForTap` pada 09:59:59 → `null`; 10:00:00, 11:00:00, 11:00:01, 12:00:00 → id sesi; 12:00:01 → `null`.
  - Ganti test "dua sesi cocok, pilih jam mulai terdekat" (sesi 10:00–11:00 dan 10:30–11:30): tap 10:20 → pertama (kedua belum mulai); tap 10:40 → kedua; tap 11:15 → kedua (pertama `Terlambat`, kedua Hadir).
  - Tambah test prioritas: sesi panjang 10:00–12:00 dan pendek 10:30–10:45 untuk crew yang sama; tap 10:50 → sesi panjang (Hadir di dalam [M, S] mengalahkan Terlambat sesi pendek walau jam mulainya lebih jauh); tap 12:05 (hanya sesi panjang terlambat, pendek terlambat juga) → yang jam mulainya terdekat (pendek).
  - Ganti test `statusAt`: 10:00:00 dan 11:00:00 → `Hadir`; 11:00:01 → `Terlambat`; 09:30:00 → (tidak dipakai lagi; hapus asersi itu).
  - Ubah test "tap bermikrodetik tetap memilih sesi terdekat" (sesi 10:00:00–11:00:00 dan 10:00:01–11:00:00): tap 10:00:00.345678 dan 10:00:00.600000 → keduanya sesi pertama (sesi kedua belum mulai); ubah juga judul test agar sesuai.
  - Test "tamu bila crew null / di luar program / hari lain" tetap.
  - `AbsensiSesiTest.php`: sesuaikan waktu `travelTo` ke aturan baru dengan arti test tetap: "tap di jendela sesi mencatat absen sesi" 09:55 → 10:30 (status `Hadir`); "tap setelah jam mulai dicatat Terlambat" 10:05 → 11:05 (judul diubah: "tap setelah jam selesai dicatat Terlambat"); "tap kedua di sesi yang sama ditolak" 10:05 boleh tetap; "tamu tidak menghalangi absen sesi kemudian" 09:30 → 10:30; "judul program dikirim apa adanya" 09:55 → 10:30; tambah "tap sebelum jam mulai menjadi tamu" (09:55 → `jenis` `tamu`, `session_id` null). Test sepersekian detik (11:00:00.4 → sesi; 10:00:00.3 → `Hadir`) tetap valid karena tap dibulatkan per detik; periksa dan pertahankan.
  - `ProgramHrTest.php` (test tap lewat form, sekitar baris 135): `travelTo` 09:55 → 10:30.
  Jalankan `php artisan test --compact tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiSesiTest.php`. Expected: FAIL pada test yang diubah.
- [ ] **Step 2: Implementasi** di `BroadcastSession.php`: ganti konstanta; `statusAt` memakai `greaterThan($this->endsAt())`; `findForTap` menyaring sesi yang jendelanya mencakup `$at` (`betweenIncluded(startsAt(), endsAt()->addMinutes(LATE_WINDOW_MINUTES))`), lalu mengurutkan menurut (apakah `$at` lebih dari `endsAt()`, selisih detik terhadap `startsAt()`) dan mengambil yang pertama. Perbarui PHPDoc kelas/method agar menjelaskan aturan baru.
- [ ] **Step 3: Jalankan** `php artisan test --compact tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiSesiTest.php tests/Feature/ProgramHrTest.php tests/Feature/AbsensiSesiCrudTest.php tests/Feature/AbsensiTest.php`. Expected: PASS (bila test lain yang menyentuh tap gagal karena aturan baru, sesuaikan waktunya dengan alasan yang sama dan sebutkan di laporan).
- [ ] **Step 4: Pint dan commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add app/Models/BroadcastSession.php tests docs/superpowers/specs/2026-11-20-jam-default-program-design.md docs/superpowers/plans/2026-11-20-jam-default-program.md
  git commit -m "feat: aturan tap baru, sebelum mulai tamu, setelah selesai terlambat"
  ```
  (Stage `tests/Feature` hanya berkas yang berubah: `git add tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiSesiTest.php tests/Feature/ProgramHrTest.php` dan berkas lain yang benar-benar diubah.)

---

### Task 2: Jam default di program (kolom dan form program)

**Files:**
- Create: migrasi `add_jam_default_to_contents_table`
- Modify: `app/Models/Content.php`, `app/Http/Controllers/Admin/PodcastController.php`, `resources/views/admin/podcast.blade.php`
- Test: `tests/Feature/ProgramHrTest.php`

**Interfaces:**
- Produces: kolom `contents.jam_mulai_default`, `contents.jam_selesai_default` (`?string`, format DB `H:i:s`); request `POST /dashboard/podcast/save` menerima `jam_mulai_default` dan `jam_selesai_default` (`H:i`); `Content::$fillable` memuat keduanya.

- [ ] **Step 1: Tulis test gagal di `ProgramHrTest.php`** (gaya yang ada, `actingAs` hr, gambar `UploadedFile::fake()->image('a.png')`):
  - `test('program menyimpan jam default dan mengosongkannya')`: POST save `{title, description, image, jam_mulai_default:'10:00', jam_selesai_default:'11:00'}` → `success`; `Content::firstOrFail()` memuat `10:00` dan `11:00` (cek dengan `substr(..., 0, 5)`); setelah `travel(2)->seconds()`, POST ubah `{id, title, description, jam_mulai_default:'', jam_selesai_default:''}` → keduanya `null`.
  - `test('jam default ditolak bila hanya satu terisi, selesai tidak setelah mulai, atau format salah')`: dataset `[mulai, selesai]` = `['10:00','']`, `['','11:00']`, `['11:00','10:00']`, `['10:00','10:00']`, `['25:99','11:00']`, `['10:00','jam']` → `assertSessionHas('error')` dan `Content::count()` 0.
  - `test('mengubah program tanpa isian jam tidak menghapus jam default')`: program dengan jam default; POST ubah tanpa kedua field (hanya `id, title, description`) → jam tetap.
  - `test('modal program membawa jam default di tombol Edit')`: `get('/dashboard/podcast')->assertSee('data-jam-mulai="10:00"', false)` dan `data-jam-selesai="11:00"`.
  Jalankan `php artisan test --compact tests/Feature/ProgramHrTest.php --filter="jam default"`. Expected: FAIL.
- [ ] **Step 2: Migrasi** `php artisan make:migration add_jam_default_to_contents_table --table=contents --no-interaction`; `up()` menambah dua `time(...)->nullable()` (setelah `description`/`image` sesuai urutan tabel); `down()` menghapus keduanya. Tambahkan keduanya ke `Content::$fillable`.
- [ ] **Step 3: `PodcastController::fieldRules()`**: tambah `'jam_mulai_default' => ['nullable', 'date_format:H:i', 'required_with:jam_selesai_default']` dan `'jam_selesai_default' => ['nullable', 'date_format:H:i', 'required_with:jam_mulai_default', 'after:jam_mulai_default']`. Pastikan `prepare()` tidak membuangnya (hanya `crew_ids`).
- [ ] **Step 4: `podcast.blade.php`**: di form, setelah deskripsi dan sebelum bagian crew, dua `<input type="time" name="jam_mulai_default">` dan `name="jam_selesai_default"` berlabel "Jam mulai default" dan "Jam selesai default (opsional)" dengan teks bantu singkat bahwa jam ini dipakai tombol "Apply waktu default" di menu Sesi; tombol Edit mendapat `data-jam-mulai`/`data-jam-selesai` (`substr(..., 0, 5)` bila ada, kosong bila tidak); skrip Edit dan blok `openContentId` mengisi kedua input; "Create new podcast" mengosongkannya (ikuti cara modal ini mengisi/mengosongkan isian lain).
- [ ] **Step 5: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php`. Expected: PASS.
- [ ] **Step 6: Periksa di browser** (SQLite sesuai aturan proyek, login `hr`; data sementara dibersihkan): isi, simpan, buka ulang modal Edit (jam terisi), Create (jam kosong), isi hanya satu jam (pesan galat). Tulis hasilnya atau nyatakan "browser check NOT done".
- [ ] **Step 7: Pint dan commit** (`git add database/migrations app/Models/Content.php app/Http/Controllers/Admin/PodcastController.php resources/views/admin/podcast.blade.php tests/Feature/ProgramHrTest.php`; pesan `feat: jam default pada program`).

---

### Task 3: Tombol "Apply waktu default" di form sesi, dokumen, dan verifikasi

**Files:**
- Modify: `resources/views/admin/absensi/sesi.blade.php`, `docs/konteks-aplikasi.md`
- Test: `tests/Feature/AbsensiSesiCrudTest.php`

**Interfaces:**
- Consumes: `Content::jam_mulai_default`, `Content::jam_selesai_default` (Task 2). `SesiController::index` sudah mengirim `programs` (koleksi `Content`); tidak perlu perubahan controller.

- [ ] **Step 1: Tulis test gagal di `AbsensiSesiCrudTest.php`**:
  - `test('halaman sesi memuat jam default tiap program dan tombol apply')`: program A dengan jam default 10:00/11:00 dan program B tanpa; sebagai hr `get('/dashboard/absensi/sesi')` → `assertSee('data-mulai="10:00"', false)`, `assertSee('data-selesai="11:00"', false)`, `assertSee('Apply waktu default')`, `assertSee('Program ini belum punya jam default')`.
  Jalankan test itu. Expected: FAIL.
- [ ] **Step 2: Ubah `sesi.blade.php`**: tiap `<option>` program di modal Tambah dan Ubah mendapat `data-mulai` dan `data-selesai` (`substr(..., 0, 5)` atau string kosong); di dekat kolom Jam Mulai/Jam Selesai tiap modal, tombol `type="button"` "Apply waktu default" plus teks kecil keterangan yang ditampilkan bila program terpilih belum punya jam default (tombol `disabled` saat itu). JS inline: fungsi yang membaca opsi terpilih pada select program modal terkait, mengisi `jam_mulai` dan `jam_selesai`, dan memperbarui keadaan tombol saat select berubah serta saat modal dibuka (termasuk pengisian modal Ubah dari `.btn-edit` yang sudah ada). Tanpa dependensi baru.
- [ ] **Step 3: Jalankan** `php artisan test --compact tests/Feature/AbsensiSesiCrudTest.php tests/Feature/ProgramHrTest.php tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiSesiTest.php`. Expected: PASS.
- [ ] **Step 4: Periksa di browser** (SQLite sementara, login `hr`): modal Tambah: pilih program berjam default, klik tombol → kedua jam terisi, tanggal tidak berubah; pilih program tanpa jam → tombol nonaktif dan keterangan tampil, jam tidak berubah; modal Ubah sama. Tulis hasilnya atau nyatakan "browser check NOT done".
- [ ] **Step 5: Perbarui `docs/konteks-aplikasi.md`**: kolom `contents.jam_mulai_default/jam_selesai_default`; aturan tap baru (sebelum mulai tamu, mulai–selesai Hadir, setelah selesai sampai +60 menit Terlambat, lalu tamu; prioritas sesi); tombol "Apply waktu default"; hapus/koreksi penyebutan jendela 60 menit sebelum mulai dan "Terlambat setelah jam mulai" di tempat mana pun; tanggal pembaruan.
- [ ] **Step 6: Pint dan commit** (`git add resources/views/admin/absensi/sesi.blade.php tests/Feature/AbsensiSesiCrudTest.php docs/konteks-aplikasi.md`; pesan `feat: tombol apply waktu default di form sesi`).
- [ ] **Step 7: Minta pengguna menjalankan** `php artisan test --compact` (suite penuh) dan `php artisan migrate` di dev.
