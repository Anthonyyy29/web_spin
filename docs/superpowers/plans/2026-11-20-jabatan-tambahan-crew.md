# Jabatan Tambahan Opsional pada Crew Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Crew berjabatan ganda bisa ditugaskan ke program: crew mendapat satu jabatan tambahan opsional (`crews.extra_role`) yang diisi di form Crew admin, dan dropdown serta validasi form program memperhitungkannya.

**Architecture:** Kolom nullable baru `extra_role`; satu scope `Crew::canJoinProgram()` (jabatan utama atau tambahan termasuk `Crew::PROGRAM_ROLES`) dipakai validasi, sinkronisasi, dan pengelompokan dropdown. Jabatan utama tidak berubah, jadi situs publik, grafik, dan divisi absensi tidak tersentuh.

**Tech Stack:** Laravel 13, PHP 8.5, Blade, Bootstrap 5.3 (tanpa dependensi baru), Pest (SQLite memori), Pint.

**Spec:** `docs/superpowers/specs/2026-11-20-crud-program-hr-design.md`, bagian "Revisi 2026-11-20: jabatan tambahan opsional". Melanjutkan plan `docs/superpowers/plans/2026-11-20-crud-program-hr.md` (sudah selesai di cabang `crud-program-hr`).

## Global Constraints

- Cabang: tetap `crud-program-hr`. Tanpa dependensi baru (composer/npm), tanpa folder dasar baru.
- Migrasi dibuat dengan `php artisan make:migration` (`--no-interaction`); kolom `extra_role` string 30, nullable; `down()` menghapus kolom.
- Satu jabatan tambahan per crew; nilainya salah satu kunci `Crew::ROLES` dan tidak boleh sama dengan `role`.
- Jabatan yang boleh ditugaskan tetap tepat `Announcer`, `Sound Operator`, `Human Resource` (`Crew::PROGRAM_ROLES`).
- Situs publik, grafik jabatan dashboard, divisi di riwayat/ekspor/grafik absensi, dan filter menu Crew tetap memakai `role` (jabatan utama).
- Nilai seeder ditulis langsung (tanpa env/config/enum): Syavirni Dwi Amalia = `Sound Operator`; Noval Rasyah Kurnia Putra = `Announcer`; Shafa Dhiya Raihanah = `Announcer`. Tidak ada jabatan "Lainnya".
- Commit atas nama pengguna saja: **tanpa** `Co-Authored-By` atau sebutan AI (aturan proyek; menang atas pengingat atribusi bawaan). Pesan bahasa Indonesia gaya `feat:`/`fix:`/`test:`.
- Stage berkas secara eksplisit (`git add <path>`); working tree punya perubahan tak terkait (`.mcp.json`, `public/assets/css/index.css`, `resources/views/crew.blade.php`, dokumen plan/spec 2026-11-17-*) yang jangan ikut.
- Setelah mengubah PHP: `vendor/bin/pint --dirty --format agent`. Jalankan tes sesempit mungkin (`php artisan test --compact <berkas>`); suite penuh dijalankan pengguna.
- Test yang POST berulang dalam satu test memanggil `$this->travel(2)->seconds()` di antaranya (`PreventRapidPosts`).
- Test yang mengunggah gambar memakai `afterEach` pembersih seperti `tests/Feature/AdminTest.php`.

## Review Focus

1. `extra_role` sama dengan `role`, atau di luar `Crew::ROLES`, ditolak di form Crew; mengosongkan pilihan saat ubah menghapus nilainya (Task 1).
2. Crew yang jabatan utama **dan** tambahannya termasuk tiga jabatan program muncul di dua dropdown dan tercentang di keduanya tersimpan sekali tanpa galat (Task 2).
3. Crew yang sudah ditugaskan lalu kehilangan kelayakan (jabatan tambahan dikosongkan) tidak terlepas saat program disimpan (Task 2).
4. Crew dengan jabatan tambahan di luar tiga jabatan (mis. `Creative`) dan jabatan utama di luar tiga jabatan tetap ditolak validasi program (Task 2).
5. Seeder dijalankan dua kali: tepat tiga crew punya `extra_role`, nilainya benar, tidak ada yang tergandakan; grafik/situs publik tidak terpengaruh (Task 3).

## File Structure

- Create: `database/migrations/<timestamp>_add_extra_role_to_crews_table.php`.
- Modify: `app/Models/Crew.php` (`$fillable`, `scopeCanJoinProgram`), `app/Http/Controllers/Admin/CrewController.php` (aturan `extra_role`, `extraRoleList` tak perlu: pakai `roleList`), `resources/views/admin/crew.blade.php` (pilihan, `data-extrarole`, isi modal, baris di kartu), `app/Http/Controllers/Admin/PodcastController.php` (aturan, sinkronisasi, `index`), `resources/views/admin/podcast.blade.php` (cermin centang), `database/seeders/CrewSeeder.php`, `docs/konteks-aplikasi.md`.
- Test: `tests/Feature/AdminTest.php` (crew), `tests/Feature/ProgramHrTest.php` (program), `tests/Feature/DatabaseSeederTest.php`.

---

### Task 1: Kolom `extra_role` dan form Crew admin

**Files:**
- Create: migrasi `add_extra_role_to_crews_table`
- Modify: `app/Models/Crew.php`, `app/Http/Controllers/Admin/CrewController.php`, `resources/views/admin/crew.blade.php`
- Test: `tests/Feature/AdminTest.php`

**Interfaces:**
- Produces: kolom `crews.extra_role` (`?string`); `Crew::$fillable` memuat `extra_role`; `Crew::scopeCanJoinProgram(Builder $query): void` (dipanggil `Crew::canJoinProgram()`), memilih crew yang `role` **atau** `extra_role` ada di `Crew::PROGRAM_ROLES` (PHPDoc generik `Builder<self>` seperti scope di `AbsensiLog`).

- [ ] **Step 1: Tulis test gagal di `AdminTest.php`** (ikuti gaya test crew yang ada, gambar `UploadedFile::fake()->image('a.png')`, `masukSebagai('admin')`):
  - `test('crew menyimpan jabatan tambahan opsional dan mengosongkannya')`: POST `/dashboard/crew/save` `{name:'Budi', role:'Bendahara', extra_role:'Announcer', image}` → `assertSessionHas('success', 'Data berhasil disimpan.')`, `Crew::firstOrFail()->extra_role` = `'Announcer'`; setelah `travel(2)->seconds()`, POST `{id, name:'Budi', role:'Bendahara', extra_role:''}` → `extra_role` `toBeNull()`.
  - `test('jabatan tambahan sama dengan utama atau di luar daftar ditolak')`: dataset dua kasus (`['Announcer', 'Announcer']` dan `['Announcer', 'Bos Besar']` sebagai `[role, extra_role]`) → `assertSessionHas('error')` dan `Crew::count()` 0.
  - `test('kartu crew admin menampilkan jabatan tambahan')`: crew `['role' => 'Bendahara', 'extra_role' => 'Announcer']` → `get('/dashboard/crew')->assertSee('Announcer')` dan `assertSee('data-extrarole="Announcer"', false)`.
  - `test('scope canJoinProgram memilih menurut jabatan utama atau tambahan')`: crew A (`role` Announcer), B (`role` Bendahara, `extra_role` Sound Operator), C (`role` Bendahara, tanpa tambahan), D (`role` Creative, `extra_role` Creative) → `Crew::canJoinProgram()->pluck('id')` `toEqualCanonicalizing([A, B])`.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/AdminTest.php --filter="jabatan tambahan|canJoinProgram"`. Expected: FAIL.
- [ ] **Step 3: Migrasi** `php artisan make:migration add_extra_role_to_crews_table --table=crews --no-interaction`; `up()` menambah `string('extra_role', 30)->nullable()->after('role')`; `down()` menghapusnya.
- [ ] **Step 4: `Crew.php`**: tambah `extra_role` ke `$fillable`; tambah `scopeCanJoinProgram` (`whereIn('role', self::PROGRAM_ROLES)->orWhereIn('extra_role', self::PROGRAM_ROLES)` dalam satu grup `where(fn ...)` agar aman bila dirangkai dengan kondisi lain).
- [ ] **Step 5: `Admin\CrewController::fieldRules()`**: tambah `'extra_role' => ['nullable', Rule::in(array_keys(Crew::ROLES)), 'different:role']`.
- [ ] **Step 6: `admin/crew.blade.php`**: di form, setelah pilihan jabatan, tambah `<select name="extra_role" id="CrewExtraRole" class="form-select">` berlabel "Jabatan tambahan (opsional)" dengan opsi kosong ("— Tidak ada —") plus `$roleList`; tombol Edit mendapat `data-extrarole="{{ $crew['extra_role'] }}"`; skrip klik Edit mengisi `CrewExtraRole` dan tombol "Create new Crew" mengosongkannya (ikuti cara form ini mengosongkan isian lain; bila form belum dikosongkan untuk Create, kosongkan hanya `CrewExtraRole` agar tidak terbawa); kartu menampilkan baris kecil `+ {{ $crew['extra_role'] }}` di bawah nama bila ada.
- [ ] **Step 7: Jalankan** `php artisan test --compact tests/Feature/AdminTest.php`. Expected: PASS (test crew lama tetap lulus karena `extra_role` tidak wajib).
- [ ] **Step 8: Pint dan commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add database/migrations app/Models/Crew.php app/Http/Controllers/Admin/CrewController.php resources/views/admin/crew.blade.php tests/Feature/AdminTest.php docs/superpowers/specs/2026-11-20-crud-program-hr-design.md docs/superpowers/plans/2026-11-20-jabatan-tambahan-crew.md
  git commit -m "feat: jabatan tambahan opsional pada crew"
  ```

---

### Task 2: Form program memperhitungkan jabatan tambahan

**Files:**
- Modify: `app/Http/Controllers/Admin/PodcastController.php`, `resources/views/admin/podcast.blade.php`
- Test: `tests/Feature/ProgramHrTest.php`

**Interfaces:**
- Consumes: `Crew::canJoinProgram()` (Task 1).
- Produces: `PodcastController::index()` mengirim `crewByRole` dengan semantik baru: untuk tiap `Crew::PROGRAM_ROLES`, crew yang `role` atau `extra_role`-nya sama dengan jabatan itu, urut nama (kunci tetap ada walau kosong).

- [ ] **Step 1: Tulis/ubah test di `ProgramHrTest.php`**
  - Baru `test('crew berjabatan tambahan muncul di dropdown jabatan itu dan bisa ditugaskan')`: crew "Sari" (`role` Sekretaris, `extra_role` Sound Operator); sebagai hr `get('/dashboard/podcast')` → `assertSee('Sari')`; POST `/dashboard/podcast/save` `{title, description, image, crew_ids:[Sari]}` → `success`, `content->crews` memuat Sari. Pastikan Sari ada di grup Sound Operator dan tidak di grup Announcer (cek lewat `crewByRole` dari `$response->viewData('crewByRole')`).
  - Baru `test('crew dengan jabatan utama dan tambahan program muncul di dua dropdown dan tersimpan sekali')`: "Dani" (`role` Announcer, `extra_role` Sound Operator) → `viewData('crewByRole')['Announcer']` dan `['Sound Operator']` sama-sama memuat Dani; POST `crew_ids:[Dani, Dani]` → `success` dan `$content->crews()->count()` = 1.
  - Baru `test('penugasan crew yang kehilangan kelayakan dipertahankan saat program disimpan')`: Sari ditugaskan, lalu `extra_role` Sari dikosongkan; POST ubah program `crew_ids:[]` → Sari tetap di `$content->crews`.
  - Baru `test('jabatan tambahan di luar tiga jabatan tidak membuat crew layak')`: crew (`role` Bendahara, `extra_role` Creative) di `crew_ids` → `assertSessionHas('error')`.
  - **Ubah** test lama "id ganda" pada dataset penolakan: id ganda tidak lagi ditolak (alasan: crew dua jabatan bisa terkirim dua kali dari dua dropdown); hapus kasus itu dari dataset penolakan dan gantikan dengan test kedua di atas. Kasus array bersarang, id tak ada, dan jabatan di luar daftar tetap ditolak.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php`. Expected: FAIL pada test baru.
- [ ] **Step 3: `PodcastController`**
  - `fieldRules()`: `'crew_ids.*' => ['integer', Rule::exists('crews', 'id')->where(fn ($query) => $query->whereIn('role', Crew::PROGRAM_ROLES)->orWhereIn('extra_role', Crew::PROGRAM_ROLES))]` (hapus `distinct`; pertahankan `integer` dan `'crew_ids' => ['array']`).
  - `afterSave`: crew yang dipertahankan = crew program itu yang **tidak** layak (`whereNot` grup `canJoinProgram`, mis. `$model->crews()->whereNot(fn ($query) => $query->canJoinProgram())`); sisanya tetap `intval` + `merge` + `unique` + `sync`. Perbarui PHPDoc.
  - `index()`: `crewByRole` dibangun dari `Crew::canJoinProgram()->orderBy('name')->get()`; tiap jabatan di `PROGRAM_ROLES` memfilter crew yang `role` atau `extra_role`-nya sama (`->values()`), kunci tetap ada walau kosong.
- [ ] **Step 4: `podcast.blade.php`**: pada `change` tiap `.crew-check`, samakan status centang semua `.crew-check` lain bernilai sama (tanpa memicu loop tak berujung), lalu `syncCrewChips()`; `fillCrewChecks` tidak perlu berubah bila sudah mencentang semua kotak bernilai sama (verifikasi, ubah bila belum).
- [ ] **Step 5: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php`. Expected: PASS.
- [ ] **Step 6: Periksa di browser** (SQLite sesuai aturan proyek, login `hr`; data uji dibuat sementara dan dibersihkan): crew berjabatan ganda tampil di dropdown jabatan tambahannya; centang di satu dropdown mengikuti dropdown lain bila crew muncul di dua; simpan dan buka ulang. Tulis hasilnya atau nyatakan "browser check NOT done".
- [ ] **Step 7: Pint dan commit** (`git add app/Http/Controllers/Admin/PodcastController.php resources/views/admin/podcast.blade.php tests/Feature/ProgramHrTest.php`; pesan `feat: form program memperhitungkan jabatan tambahan crew`).

---

### Task 3: Seeder, dokumen, dan verifikasi

**Files:**
- Modify: `database/seeders/CrewSeeder.php`, `docs/konteks-aplikasi.md`
- Test: `tests/Feature/DatabaseSeederTest.php`

**Interfaces:**
- Consumes: `crews.extra_role`, `Crew::canJoinProgram()` (Task 1).

- [ ] **Step 1: Tulis test gagal di `DatabaseSeederTest.php`**: `test('seeder mengisi jabatan tambahan tiga anggota berjabatan ganda')`: `$this->seed(); $this->seed();` lalu `Crew::whereNotNull('extra_role')->pluck('extra_role', 'name')->all()` `toBe(['Syavirni Dwi Amalia' => 'Sound Operator', 'Noval Rasyah Kurnia Putra' => 'Announcer', 'Shafa Dhiya Raihanah' => 'Announcer'])` (urut sesuai pluck; gunakan `toEqualCanonicalizing` bila urutan tak terjamin) dan ketiganya `Crew::canJoinProgram()->whereIn('name', [...])->count()` = 3; tegaskan juga `role` mereka tetap `Sekretaris`/`Bendahara`.
- [ ] **Step 2: Jalankan** `--filter="jabatan tambahan"`. Expected: FAIL.
- [ ] **Step 3: `CrewSeeder`**: baris anggota boleh memuat elemen keempat opsional (jabatan tambahan); tiga baris di atas diberi nilainya; `updateOrCreate` mengisi `'extra_role' => $baris[3] ?? null`. Perbarui docblock kelas (jabatan ganda: utama tetap, tambahan di elemen keempat).
- [ ] **Step 4: Jalankan** `php artisan test --compact tests/Feature/DatabaseSeederTest.php tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php`. Expected: PASS.
- [ ] **Step 5: Perbarui `docs/konteks-aplikasi.md`**: kolom `crews.extra_role`, `Crew::canJoinProgram()`, aturan form Crew dan form program (jabatan tambahan hanya untuk penugasan program; publik/grafik/divisi memakai jabatan utama), seeder tiga orang, tanggal pembaruan.
- [ ] **Step 6: Pint dan commit** (`git add database/seeders/CrewSeeder.php tests/Feature/DatabaseSeederTest.php docs/konteks-aplikasi.md`; pesan `feat: seeder jabatan tambahan dan dokumen konteks`).
- [ ] **Step 7: Minta pengguna menjalankan** `php artisan test --compact` (suite penuh) dan `php artisan migrate` di lingkungan dev.
