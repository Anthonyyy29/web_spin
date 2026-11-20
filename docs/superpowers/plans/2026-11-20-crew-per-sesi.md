# Crew per Sesi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Penugasan crew pindah dari program ke sesi siaran: crew dipilih di modal Tambah/Ubah sesi, tap RFID dicocokkan lewat crew sesi, dan form program kehilangan bagian crew.

**Architecture:** Tabel baru `broadcast_session_crew` dan relasi `BroadcastSession::crews()`; aturan sinkronisasi hidup di model (`syncCrews`); `findForTap` memakai relasi itu. Tiga dropdown crew pindah ke view sesi lewat satu partial; `content_crew`, `Content::crews()`, `Crew::contents()`, hook `afterSave`, dan `ProgramCrewSeeder` dihapus.

**Tech Stack:** Laravel 13, PHP 8.5, Blade, Bootstrap 5.3 (tanpa dependensi baru), Pest (SQLite memori), Pint.

**Spec:** `docs/superpowers/specs/2026-11-20-crew-per-sesi-design.md`.

## Global Constraints

- Cabang: tetap `crud-program-hr`. Tanpa dependensi baru (composer/npm), tanpa folder dasar baru.
- Migrasi dibuat dengan `php artisan make:migration --no-interaction`. Penyimpangan kecil dari spec yang diizinkan: dua migrasi (Task 1 membuat `broadcast_session_crew`; Task 3 menghapus `content_crew`), hasil akhirnya sama dengan yang disebut spec. Penghapusan isi `content_crew` tanpa penyalinan sudah disetujui pemilik; `down()` migrasi penghapusan membuat tabel `content_crew` kosong (kolom `content_id`, `crew_id` dengan foreign key `cascadeOnDelete` dan primary key gabungan seperti aslinya).
- `broadcast_session_crew`: `broadcast_session_id` dan `crew_id`, keduanya foreign key `cascadeOnDelete`, primary key gabungan.
- Penamaan baru: `Crew::SESSION_ROLES` (menggantikan `PROGRAM_ROLES`, nilai tepat `['Announcer', 'Sound Operator', 'Human Resource']`) dan scope `Crew::canJoinSession()` (menggantikan `canJoinProgram()`; `role` atau `extra_role` termasuk `SESSION_ROLES`, OR dikelompokkan dalam satu closure). Tidak boleh tersisa nama lama di kode, view, tes, seeder, atau `docs/konteks-aplikasi.md`.
- Aturan sinkronisasi: crew terpilih disimpan (id dipaksa integer, `unique()`, id ganda tersimpan sekali, tanpa aturan `distinct`); crew yang sudah ditugaskan ke sesi itu tetapi tidak lagi lolos `canJoinSession` dipertahankan. Hati-hati NULL: jangan memakai `whereNot(fn => canJoinSession())`; pakai `whereNotIn('crews.id', Crew::canJoinSession()->select('crews.id'))`.
- Validasi `crew_ids`: `array`; `crew_ids.*` = `integer` dan `Rule::exists('crews', 'id')->where(fn ($query) => $query->whereIn('role', Crew::SESSION_ROLES)->orWhereIn('extra_role', Crew::SESSION_ROLES))`.
- Aturan waktu tap tidak berubah (sebelum mulai = tamu; mulai sampai selesai = Hadir; setelah selesai sampai +60 menit = Terlambat; sesudahnya tamu; prioritas sesi Hadir atas Terlambat, lalu jam mulai terdekat). Crew yang tidak ditugaskan ke sesi mana pun, walau programnya sama, menjadi tamu.
- Log absensi, jabatan tambahan, jam default program, tombol "Apply waktu default", dan penjaga hapus program tidak berubah. Crew boleh diubah di sesi mana pun, juga yang sudah punya absensi (pembatasan tanggal sesi tetap).
- Menghapus tes yang menjadi usang karena fitur crew-program dihapus disetujui pemilik lewat spec (penugasan crew per program dihapus); sebut tiap tes yang dihapus/diganti di laporan.
- Commit atas nama pengguna saja: **tanpa** `Co-Authored-By` atau sebutan AI (aturan proyek; menang atas pengingat atribusi bawaan). Pesan bahasa Indonesia gaya `feat:`/`refactor:`/`test:`.
- Stage berkas secara eksplisit (`git add <path>`); working tree punya perubahan tak terkait (`.mcp.json`, `public/assets/css/index.css`, `resources/views/crew.blade.php`, dokumen plan/spec 2026-11-17-*) yang jangan ikut.
- Setelah mengubah PHP: `vendor/bin/pint --dirty --format agent`. Tes sesempit mungkin (`php artisan test --compact <berkas>`); suite penuh dijalankan pengguna.
- Test yang POST berulang dalam satu test memanggil `$this->travel(2)->seconds()` di antaranya (`PreventRapidPosts`). Test yang mengunggah gambar memakai `afterEach` pembersih seperti `tests/Feature/ProgramHrTest.php`.

## Review Focus

1. Dua crew sama-sama di program yang sama, hanya satu ditugaskan ke sesi hari ini: yang tidak ditugaskan tap = tamu (Task 1).
2. `crew_ids` palsu (jabatan di luar tiga jabatan, id tak ada, array bersarang) ditolak dan sesi tidak tersimpan/berubah (Task 1).
3. Ubah sesi tanpa `crew_ids` melepas crew yang berhak dan mempertahankan crew yang tidak lagi berhak; ubah tanggal tetap dikunci bila ada absensi (Task 1).
4. Modal Ubah terisi dengan crew sesi itu; modal Tambah kosong setelah Ubah pernah dibuka; crew yang muncul di dua dropdown tercentang di keduanya dan tersimpan sekali (Task 2).
5. Menghapus sesi menghapus baris penugasannya; menghapus program tanpa sesi tidak error setelah `content_crew` hilang; `migrate:rollback` migrasi penghapusan berjalan (Task 3).

## File Structure

- Create: migrasi `create_broadcast_session_crew_table` (Task 1), migrasi `drop_content_crew_table` (Task 3), partial `resources/views/admin/absensi/_crew-dropdowns.blade.php` (Task 2).
- Modify: `app/Models/BroadcastSession.php`, `app/Models/Crew.php`, `app/Models/Content.php`, `app/Http/Controllers/Admin/Absensi/SesiController.php`, `app/Http/Controllers/Admin/PodcastController.php`, `app/Http/Controllers/Admin/ImageCrudController.php`, `resources/views/admin/absensi/sesi.blade.php`, `resources/views/admin/podcast.blade.php`, `database/seeders/DatabaseSeeder.php`, `docs/konteks-aplikasi.md`.
- Delete: `database/seeders/ProgramCrewSeeder.php`.
- Test: `tests/Feature/BroadcastSessionTest.php`, `AbsensiSesiTest.php`, `AbsensiSesiCrudTest.php`, `ProgramHrTest.php`, `AdminTest.php`, `DatabaseSeederTest.php`.

---

### Task 1: Penugasan crew per sesi (data, model, validasi, tap)

**Files:**
- Create: migrasi `create_broadcast_session_crew_table`
- Modify: `app/Models/BroadcastSession.php`, `app/Models/Crew.php`, `app/Http/Controllers/Admin/Absensi/SesiController.php`, `app/Http/Controllers/Admin/PodcastController.php` (hanya mengikuti penamaan baru), dan semua pemakai nama lama (`grep -rn "PROGRAM_ROLES\|canJoinProgram" app database resources tests`)
- Test: `tests/Feature/BroadcastSessionTest.php`, `tests/Feature/AbsensiSesiTest.php`, `tests/Feature/AbsensiSesiCrudTest.php`, `tests/Feature/ProgramHrTest.php`, `tests/Feature/AdminTest.php`, `tests/Feature/DatabaseSeederTest.php`

**Interfaces:**
- Produces: `BroadcastSession::crews(): BelongsToMany<Crew, $this>`; `Crew::broadcastSessions(): BelongsToMany<BroadcastSession, $this>`; `BroadcastSession::syncCrews(array $crewIds): void`; `Crew::SESSION_ROLES`; `Crew::canJoinSession()` (scope, `Builder<self>`, `void`); `findForTap(?Crew $crew, CarbonInterface $at): ?self` menyaring sesi yang memuat `$crew` lewat relasi `crews`; `POST /dashboard/absensi/sesi/simpan` dan `/update` menerima `crew_ids[]`.

- [ ] **Step 1: Perbarui dan tambah test (RED)**
  - Helper tes: `sessionFor()` di `BroadcastSessionTest.php` dan `siapkanSesiHariIni()` di `AbsensiSesiTest.php` menugaskan crew ke **sesi** (`$session->crews()->attach($crew)`), bukan ke program. Test "tamu bila crew null / di luar program / hari lain" menjadi "di luar sesi": crew lain yang tidak ditugaskan = `null`.
  - Test baru `BroadcastSessionTest`: dua crew di program yang sama, hanya A ditugaskan ke sesi hari ini; `findForTap(A, 10:30)` = sesi, `findForTap(B, 10:30)` = `null`. `syncCrews`: (a) menyimpan id terpilih dan menggantikan yang lama; (b) id ganda `[$a->id, $a->id]` tersimpan satu baris; (c) crew berjabatan `Web Development` tanpa `extra_role` yang sudah ditugaskan tetap ada setelah `syncCrews([])`; (d) crew `Announcer` yang sudah ditugaskan terlepas oleh `syncCrews([])`.
  - `AbsensiSesiCrudTest.php`: `simpan` dengan `crew_ids` (Announcer, Sound Operator, Human Resource) menyimpan crew sesi; `update` menambah/melepas; `update` tanpa `crew_ids` melepas crew berhak dan mempertahankan crew tak berhak; dataset penolakan `crew_ids` = `[id Web Development]`, `[9999]`, `'abc'`, `[[id Announcer]]` → `assertSessionHasErrors` (nama kunci sesuai kunci galat Laravel) dan `BroadcastSession::count()` 0; crew `Bendahara` + `extra_role` `Announcer` diterima; tanggal sesi yang punya absensi tetap terkunci saat `crew_ids` dikirim; end-to-end: buat sesi hari ini lewat form dengan `crew_ids`, `travelTo` jam di dalam sesi, `postJson('/tap-absen', ...)` → `jenis` `sesi`.
  - `ProgramHrTest.php`: hapus test "tap memakai crew yang ditugaskan lewat form" (digantikan test end-to-end di atas; sebut dalam laporan). Tes crew-program lain di berkas ini tetap (dihapus di Task 3).
  - Ganti nama pada `AdminTest.php` (scope) dan `DatabaseSeederTest.php`: `canJoinProgram` → `canJoinSession`.
  Jalankan `php artisan test --compact tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiSesiCrudTest.php tests/Feature/AbsensiSesiTest.php`. Expected: FAIL.
- [ ] **Step 2: Migrasi** `php artisan make:migration create_broadcast_session_crew_table --no-interaction` sesuai Global Constraints; `down()` `dropIfExists`.
- [ ] **Step 3: Model.** `BroadcastSession::crews()` (`belongsToMany(Crew::class, 'broadcast_session_crew')`); `Crew::broadcastSessions()` sebaliknya; ganti nama konstanta dan scope di `Crew.php` (PHPDoc diperbarui); implement `syncCrews` sesuai aturan sinkronisasi (PHPDoc singkat); `findForTap` memakai `whereHas('crews', fn ($query) => $query->whereKey($crew->getKey()))` menggantikan `whereIn('content_id', $crew->contents()->...)`, sisanya tidak berubah. `Content::crews()` dan `Crew::contents()` BELUM dihapus (Task 3).
- [ ] **Step 4: `SesiController`.** `validated()` menambah aturan `crew_ids` seperti Global Constraints dan mengembalikan data sesi tanpa `crew_ids` (misal dengan `Arr::except` dan mengambil `crew_ids` terpisah dari request); `simpan` dan `update` memanggil `$sesi->syncCrews($crewIds)` setelah simpan/update; aturan tanggal terkunci tidak berubah. Perbarui `PodcastController` agar memakai nama baru saja (perilaku tidak berubah).
- [ ] **Step 5: Jalankan** `php artisan test --compact tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiSesiCrudTest.php tests/Feature/AbsensiSesiTest.php tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php tests/Feature/DatabaseSeederTest.php tests/Feature/AbsensiTest.php`. Expected: PASS. Pastikan `grep -rn "PROGRAM_ROLES\|canJoinProgram" app database resources tests` kosong.
- [ ] **Step 6: Pint dan commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add database/migrations app tests docs/superpowers/specs/2026-11-20-crew-per-sesi-design.md docs/superpowers/plans/2026-11-20-crew-per-sesi.md
  git commit -m "feat: penugasan crew per sesi dan tap mengikuti crew sesi"
  ```
  (Stage hanya berkas yang benar-benar berubah di `app`/`tests`, bukan seluruh direktori bila ada berkas tak terkait.)

---

### Task 2: Dropdown crew di modal sesi dan kolom crew

**Files:**
- Create: `resources/views/admin/absensi/_crew-dropdowns.blade.php`
- Modify: `app/Http/Controllers/Admin/Absensi/SesiController.php` (`index`), `resources/views/admin/absensi/sesi.blade.php`
- Test: `tests/Feature/AbsensiSesiCrudTest.php`

**Interfaces:**
- Consumes: `BroadcastSession::crews()`, `Crew::canJoinSession()`, `Crew::SESSION_ROLES` (Task 1).
- Produces: `SesiController::index` mengirim `crewByRole` (`Collection<string, Collection<int, Crew>>`: kunci tiap `Crew::SESSION_ROLES`, ada walau kosong; isi crew yang `role` atau `extra_role`-nya sama dengan kunci, diurut nama; satu kueri `canJoinSession`, pemecahan di PHP) dan memuat `crews` pada `sessions` (`with(['content', 'crews'])`); partial menerima `$prefix` (`tambah` atau `edit`) dan `$crewByRole`.

- [ ] **Step 1: Tulis test gagal di `AbsensiSesiCrudTest.php`**: (a) `viewData('crewByRole')` memuat ketiga kunci, crew `Web Development` tanpa tambahan tidak muncul, crew `Bendahara` + `extra_role` `Sound Operator` hanya di grup Sound Operator, crew `Announcer` + `extra_role` `Sound Operator` di dua grup, urut nama; (b) halaman memuat kolom "Crew" dan nama crew sesi (dua crew dipisah koma) dan teks kosong bila belum ada; (c) tombol Edit membawa `data-crews` berisi id crew sesi (assert string JSON lewat `toJson()` yang sama dengan view); (d) halaman memuat partial dua kali (awalan `tambah` dan `edit`): `assertSee('name="crew_ids[]"', false)` dan id kotak centang berbeda per modal (mis. `crew-tambah-` dan `crew-edit-` muncul).
  Jalankan test itu. Expected: FAIL.
- [ ] **Step 2: `SesiController::index`**: bangun `crewByRole` seperti di atas dan kirim ke view; `with(['content', 'crews'])` pada kueri sesi.
- [ ] **Step 3: Partial `_crew-dropdowns.blade.php`**: tiga dropdown Bootstrap (satu per `Crew::SESSION_ROLES`) berisi `<input type="checkbox" class="crew-check" name="crew_ids[]" value="{{ $crew->id }}" id="crew-{{ $prefix }}-{{ slug jabatan }}-{{ $crew->id }}">`, area chip, placeholder "Pilih {jabatan huruf kecil}", dan pesan "Belum ada crew {jabatan}." bila kosong. Pindahkan markup/gaya dari bagian crew di `admin/podcast.blade.php` (baca berkas itu; jangan hapus dari sana sekarang).
- [ ] **Step 4: `sesi.blade.php`**: sertakan partial di modal Tambah (`prefix` `tambah`) dan Ubah (`prefix` `edit`) di bawah isian jam; kolom "Crew" di tabel; tombol Edit mendapat `data-crews="{{ $session->crews->pluck('id')->toJson() }}"`; port JS dari `podcast.blade.php` (chip, cerminan kotak centang bernilai sama dalam satu modal, ×, isi/kosongkan) yang berparameter modal (cakupan kotak centang dibatasi ke modal terkait); modal Ubah diisi dari `data-crews` di handler `.btn-edit` yang ada; modal Tambah dikosongkan saat dibuka. Tanpa dependensi baru.
- [ ] **Step 5: Jalankan** `php artisan test --compact tests/Feature/AbsensiSesiCrudTest.php tests/Feature/AbsensiSesiTest.php`. Expected: PASS.
- [ ] **Step 6: Periksa di browser** (SQLite sesuai aturan proyek, login `hr`; data sementara dibersihkan): modal Tambah: pilih crew dari tiga dropdown, chip muncul, simpan, crew tampil di tabel; modal Ubah terisi dengan crew sesi itu; buka Tambah setelah Ubah → kosong; crew di dua dropdown mencerminkan centang dan × melepas di keduanya; submit tanpa crew tidak menghapus apa pun yang tidak seharusnya. Tulis hasilnya atau nyatakan "browser check NOT done".
- [ ] **Step 7: Pint dan commit** (`git add app/Http/Controllers/Admin/Absensi/SesiController.php resources/views/admin/absensi tests/Feature/AbsensiSesiCrudTest.php`; pesan `feat: dropdown crew di form sesi dan kolom crew`).

---

### Task 3: Hapus penugasan crew dari program, seeder, dokumen

**Files:**
- Create: migrasi `drop_content_crew_table`
- Modify: `app/Models/Content.php`, `app/Models/Crew.php`, `app/Http/Controllers/Admin/PodcastController.php`, `app/Http/Controllers/Admin/ImageCrudController.php`, `resources/views/admin/podcast.blade.php`, `database/seeders/DatabaseSeeder.php`, `docs/konteks-aplikasi.md`
- Delete: `database/seeders/ProgramCrewSeeder.php`
- Test: `tests/Feature/ProgramHrTest.php`, `tests/Feature/DatabaseSeederTest.php`, `tests/Feature/AbsensiSesiCrudTest.php`

- [ ] **Step 1: Ubah/hapus test (RED)**
  - `ProgramHrTest.php`: hapus test yang hanya menguji crew-program (menyimpan/menyinkronkan crew, penolakan `crew_ids`, dropdown per jabatan, jabatan tambahan di dropdown program, id ganda, kehilangan kelayakan, badge/`data-crews`); test penjaga hapus program: hapus penyebutan `content_crew` dan lampiran crew. Test lain (akses, jam default, penjaga hapus, hapus program tanpa sesi) tetap. Tambah: `get('/dashboard/podcast')` tidak memuat `name="crew_ids[]"` dan tidak memuat "crew ditugaskan"; POST save dengan `crew_ids` tidak error dan program tersimpan.
  - `DatabaseSeederTest.php`: hapus asersi 31 penugasan, crew K-TIME, dan crew `SPINVERSE`; tambah `expect(class_exists(\Database\Seeders\ProgramCrewSeeder::class))->toBeFalse()` (atau cek `DB` tidak punya `content_crew`); asersi jabatan tambahan tiga orang tetap.
  - `AbsensiSesiCrudTest.php`: tes migrasi: `Schema::hasTable('content_crew')` = false dan `Schema::hasTable('broadcast_session_crew')` = true; menghapus sesi menghapus baris `broadcast_session_crew` miliknya (jumlah baris pivot 0, crew tetap ada).
  Jalankan test yang berubah. Expected: FAIL (tabel/UI lama masih ada).
- [ ] **Step 2: Migrasi** `php artisan make:migration drop_content_crew_table --no-interaction`: `up()` `Schema::dropIfExists('content_crew')`; `down()` membuat ulang tabel kosong (kolom dan kunci seperti Global Constraints).
- [ ] **Step 3: Kode.** Hapus `Content::crews()` dan `Crew::contents()`; hapus dari `PodcastController`: aturan `crew_ids`, `prepare()` override, `afterSave()` override, `crewByRole`, `with('crews')` pada `index` (bersihkan `use` yang tak terpakai); hapus hook `afterSave` dan panggilannya dari `ImageCrudController` (PHPDoc ikut); di `podcast.blade.php` hapus bagian crew (dropdown, teks bantu), badge "crew ditugaskan", `data-crews`, dan seluruh JS crew (`syncCrewChips`, `setCrewChecked`, `fillCrewChecks`, pemanggilnya); hapus `ProgramCrewSeeder.php` dan keluarkan dari `DatabaseSeeder`. Pastikan `grep -rn "content_crew\|->crews()\|crew_ids\|ProgramCrewSeeder\|->contents()" app database resources routes` hanya menyisakan pemakaian sesi (`BroadcastSession::crews`, `SesiController`, view sesi) dan migrasi lama.
- [ ] **Step 4: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php tests/Feature/DatabaseSeederTest.php tests/Feature/AbsensiSesiCrudTest.php tests/Feature/AbsensiSesiTest.php tests/Feature/BroadcastSessionTest.php tests/Feature/AbsensiTest.php tests/Feature/SitusPublikTest.php tests/Feature/DashboardTest.php`. Expected: PASS. Jalankan juga sekali `php artisan migrate:rollback --step=1 --pretend` bila tersedia, atau uji `down()` lewat test migrasi yang ada, dan laporkan hasilnya.
- [ ] **Step 5: Periksa di browser** (SQLite sementara, login `hr`): form program tanpa bagian crew; simpan/ubah program berfungsi; hapus program tanpa sesi berhasil. Tulis hasilnya atau nyatakan "browser check NOT done".
- [ ] **Step 6: Perbarui `docs/konteks-aplikasi.md`**: tabel (`broadcast_session_crew`, `content_crew` dihapus), relasi dan aturan tap (crew sesi), form sesi (dropdown crew) dan form program (tanpa crew), `Crew::SESSION_ROLES`/`canJoinSession`, `BroadcastSession::syncCrews`, hook `afterSave` dihapus, seeder (`ProgramCrewSeeder` dihapus; sesi lama harus diisi crew-nya setelah migrasi), tanggal pembaruan. Verifikasi tiap klaim terhadap kode.
- [ ] **Step 7: Pint dan commit** (`git add database app resources docs/konteks-aplikasi.md tests` hanya berkas yang berubah; berkas yang dihapus ditahap lewat `git rm`; pesan `refactor: hapus penugasan crew dari program`).
- [ ] **Step 8: Minta pengguna menjalankan** `php artisan migrate` (menghapus isi `content_crew`; catat dulu penugasan yang ingin disimpan), lalu mengisi crew di sesi yang ada, dan `php artisan test --compact` (suite penuh).
