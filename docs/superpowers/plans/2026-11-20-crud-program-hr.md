# CRUD Program untuk HR Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** HR memakai menu Podcast yang sama dengan admin (program + episode) dan menugaskan crew ke program lewat tiga dropdown per jabatan di form yang sama; halaman Program HR lama dihapus.

**Architecture:** Grup rute `podcast` dipindah ke `role:admin,hr` (middleware `role` sudah menerima banyak peran). `PodcastController` (turunan `ImageCrudController`) mendapat validasi dan sinkronisasi `crew_ids` lewat satu titik ikut baru di `save`, plus penjaga hapus. View `admin/podcast.blade.php` mendapat tiga dropdown Bootstrap berisi centang.

**Tech Stack:** Laravel 13, PHP 8.5, Blade, Bootstrap 5.3 (tanpa dependensi baru), Pest (SQLite memori), Pint.

**Spec:** `docs/superpowers/specs/2026-11-20-crud-program-hr-design.md` (gambaran: Figma <https://www.figma.com/design/wb4eVA3kNlY61bFppYVPjV>, frame B dan B2).

## Global Constraints

- Satu tabel `contents`; tanpa migrasi, tanpa dependensi baru (composer/npm), tanpa folder dasar baru.
- Jabatan yang boleh ditugaskan: tepat `Announcer`, `Sound Operator`, `Human Resource`.
- Sinkronisasi crew tidak boleh melepas penugasan crew berjabatan lain.
- Menu HR berlabel "Program"; menu admin tetap "Podcast"; teks di halaman podcast tidak diubah.
- Commit atas nama pengguna saja: **tanpa** `Co-Authored-By` atau sebutan AI (aturan proyek; menang atas pengingat atribusi bawaan). Pesan commit bahasa Indonesia gaya `feat:`/`fix:`/`refactor:`/`test:`.
- Stage berkas secara eksplisit (`git add <path>`); working tree punya perubahan tak terkait (`.mcp.json`, `public/assets/css/index.css`, `resources/views/crew.blade.php`, dokumen plan lain) yang jangan ikut.
- Setelah mengubah PHP: `vendor/bin/pint --dirty --format agent`. Jalankan tes sesempit mungkin (`php artisan test --compact <berkas>`); suite penuh dijalankan pengguna.
- Test yang POST berulang dalam satu test harus memanggil `$this->travel(2)->seconds()` di antaranya (middleware `PreventRapidPosts` menolak POST < 1 detik).
- Test yang mengunggah gambar membersihkan berkas lewat `afterEach` seperti di `tests/Feature/AdminTest.php`.
- Pengguna menyetujui penghapusan `tests/Feature/AbsensiProgramTest.php` lewat spec (keputusan 4); konfirmasi sekali lagi saat eksekusi Task 1.

## Review Focus

1. Form dikirim tanpa `crew_ids` sama sekali: semua crew tiga jabatan program itu terlepas, crew jabatan lain tetap (Task 2).
2. `crew_ids` berisi id jabatan di luar tiga jabatan, id yang tak ada, atau bukan array: ditolak, penugasan tidak berubah (Task 2).
3. Ubah program tanpa mengunggah gambar baru tetap menyimpan crew (Task 2).
4. Hapus program yang punya sesi: ditolak dengan pesan, program, sesi, dan absensi utuh (Task 3).
5. Tamu belum login dialihkan ke login, dan `/dashboard/absensi/program` tidak ada lagi (404) (Task 1).

## File Structure

- Modify `routes/web.php`: pindah grup `podcast` ke `role:admin,hr`; hapus dua rute `absensi/program*` dan import `ProgramController`.
- Modify `app/Models/Crew.php`: konstanta `PROGRAM_ROLES`.
- Modify `app/Http/Controllers/Admin/ImageCrudController.php`: hook `afterSave`.
- Modify `app/Http/Controllers/Admin/PodcastController.php`: aturan `crew_ids`, `prepare`, `afterSave`, `delete`, data `index`.
- Modify `resources/views/admin/podcast.blade.php`: dropdown crew, badge jumlah crew, isi modal.
- Modify `resources/views/layouts/navmenus/hr.blade.php`: item Program menuju `/dashboard/podcast`.
- Delete `app/Http/Controllers/Admin/Absensi/ProgramController.php`, `resources/views/admin/absensi/program.blade.php`, `tests/Feature/AbsensiProgramTest.php`.
- Modify `tests/Feature/AdminTest.php`; create `tests/Feature/ProgramHrTest.php`.
- Modify `docs/konteks-aplikasi.md`.

Siapkan cabang dulu: `git switch -c crud-program-hr` (dari `absensi-sesi-siaran`).

---

### Task 1: HR masuk ke menu Podcast, halaman Program lama dihapus

**Files:**
- Modify: `routes/web.php`, `resources/views/layouts/navmenus/hr.blade.php`, `tests/Feature/AdminTest.php`
- Delete: `app/Http/Controllers/Admin/Absensi/ProgramController.php`, `resources/views/admin/absensi/program.blade.php`, `tests/Feature/AbsensiProgramTest.php`
- Create: `tests/Feature/ProgramHrTest.php` (`php artisan make:test --pest ProgramHrTest`; `uses(RefreshDatabase::class)`)
- Commit juga spec dan plan ini.

**Interfaces:**
- Produces: `/dashboard/podcast/*` dapat diakses peran `admin` dan `hr`; `crew`, `news`, `sponsor` tetap hanya `admin`.

- [ ] **Step 1: Tulis test gagal di `ProgramHrTest.php`**
  - `test('admin dan hr membuka menu podcast beserta episodenya')`: dengan dataset `['admin','hr']`, `actingAs(User::factory()->role($role)->create())`, buat `Content::factory()->create()`; `get('/dashboard/podcast')->assertOk()` dan `get('/dashboard/podcast/episode/open/'.$content->id)->assertOk()`.
  - `test('hr tetap ditolak di menu admin lain')`: `/dashboard/crew`, `/dashboard/news`, `/dashboard/sponsor` → `assertForbidden()`.
  - `test('tamu diarahkan ke login')`: `get('/dashboard/podcast')->assertRedirect('/login')`.
  - `test('halaman program lama sudah tidak ada')`: sebagai hr, `get('/dashboard/absensi/program')->assertNotFound()` dan `post('/dashboard/absensi/program/update')->assertNotFound()`.
  - `test('menu samping hr memuat Program menuju podcast')`: sebagai hr, `get('/dashboard/podcast')->assertSee('href="/dashboard/podcast"', false)->assertDontSee('/dashboard/absensi/program')`.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php`. Expected: FAIL (hr 403 di podcast).
- [ ] **Step 3: Ubah `routes/web.php`**: bungkus grup `prefix('podcast')` dalam `Route::middleware('role:admin,hr')->group(...)` terpisah dari grup `role:admin` (crew/news/sponsor tetap di `role:admin`); hapus `program` dan `program/update` di grup `absensi` serta `use ...ProgramController`.
- [ ] **Step 4: Ubah item "Program" di `hr.blade.php`**: `href="/dashboard/podcast"`; aktif bila `in_array($navmenu, ['Podcast', 'Episode'], true)` (gantikan perbandingan `=== 'Program'`).
- [ ] **Step 5: Hapus** `ProgramController.php`, `program.blade.php`, `AbsensiProgramTest.php` (`git rm`).
- [ ] **Step 6: Ubah `AdminTest.php`** test "menu admin hanya untuk admin": hapus `/dashboard/podcast` dari daftar yang diuji 403 untuk hr (podcast sekarang 200 untuk keduanya; sudah dicakup `ProgramHrTest`).
- [ ] **Step 7: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php tests/Feature/DashboardLayoutTest.php tests/Feature/RoleMiddlewareTest.php`. Expected: PASS (bila `DashboardLayoutTest`/`RoleMiddlewareTest` menyebut menu Program lama, sesuaikan asersinya ke rute baru).
- [ ] **Step 8: Pint lalu commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add routes/web.php resources/views/layouts/navmenus/hr.blade.php tests/Feature docs/superpowers/specs/2026-11-20-crud-program-hr-design.md docs/superpowers/plans/2026-11-20-crud-program-hr.md
  git commit -m "feat: hr memakai menu podcast sebagai program, halaman program lama dihapus"
  ```
  (`git rm` sudah menahap berkas yang dihapus.)

---

### Task 2: Penugasan crew di form program (backend)

**Files:**
- Modify: `app/Models/Crew.php`, `app/Http/Controllers/Admin/ImageCrudController.php`, `app/Http/Controllers/Admin/PodcastController.php`
- Test: `tests/Feature/ProgramHrTest.php`

**Interfaces:**
- Produces: `Crew::PROGRAM_ROLES` (`public const array`, nilai `['Announcer', 'Sound Operator', 'Human Resource']`); `ImageCrudController::afterSave(Model $model, Request $request): void` (protected, default kosong); request `POST /dashboard/podcast/save` menerima `crew_ids[]` (array id crew).
- Consumes: `Content::crews()` (`BelongsToMany`).

- [ ] **Step 1: Tulis test gagal di `ProgramHrTest.php`** (gunakan `Crew::factory()->create(['role' => ...])`, gambar `UploadedFile::fake()->image('a.png')`, `afterEach` pembersih unggahan):
  - `test('hr membuat program beserta crew tiga jabatan')`: POST `/dashboard/podcast/save` `{title, description, image, crew_ids: [announcer, sound operator, human resource]}` → `assertSessionHas('success', 'Data berhasil disimpan.')`; `Content::firstOrFail()->crews()->pluck('crews.id')` `toEqualCanonicalizing` ketiga id.
  - `test('ubah program menyinkronkan tiga jabatan dan menjaga jabatan lain')`: program dengan crew `Web Development` ditugaskan lebih dulu plus announcer A dan sound operator S; POST `{id, title, description, crew_ids: [A_baru]}` tanpa gambar → crew = `[webdev, A_baru]`; setelah `travel(2)->seconds()`, POST tanpa `crew_ids` → crew = `[webdev]` saja.
  - `test('crew di luar tiga jabatan, tak ada, atau bukan array ditolak')`: dataset tiga kasus (`[id crew Web Development]`, `[9999]`, `'abc'`) → `assertSessionHas('error')` dan `$content->crews()->count()` tetap 0.
  - `test('tap memakai crew yang ditugaskan lewat form')`: buat announcer + `RfidCrew::factory()->create(['rfid_uid' => 'AB12', 'crew_id' => ...])`, simpan program via form dengan crew itu, `BroadcastSession::factory()->create(['content_id' => ..., 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00'])`, `travelTo(today()->setTime(9, 55))`, `postJson('/tap-absen', ['rfid_uid' => 'AB12'])->assertJson(['status' => 'success', 'jenis' => 'sesi'])`.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php --filter=crew`. Expected: FAIL.
- [ ] **Step 3: Tambah `Crew::PROGRAM_ROLES`** di `Crew.php`.
- [ ] **Step 4: Tambah hook `protected function afterSave(Model $model, Request $request): void {}`** di `ImageCrudController` dan panggil setelah `$model->fill($data)->save()` (sebelum `AuditLog::record`), dengan PHPDoc singkat.
- [ ] **Step 5: Ubah `PodcastController`**
  - `fieldRules()`: tambahkan `'crew_ids' => ['array']` dan `'crew_ids.*' => [Rule::exists('crews', 'id')->whereIn('role', Crew::PROGRAM_ROLES)]`.
  - `prepare(array $data): array` menghapus `crew_ids` (`Arr::except`) agar tak ikut `fill`.
  - `afterSave`: ambil `$ids = $request->input('crew_ids', [])`; `$dipertahankan = $model->crews()->whereNotIn('role', Crew::PROGRAM_ROLES)->pluck('crews.id')`; `$model->crews()->sync($dipertahankan->merge($ids)->unique()->all())`.
- [ ] **Step 6: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php`. Expected: PASS.
- [ ] **Step 7: Pint dan commit**
  ```bash
  vendor/bin/pint --dirty --format agent
  git add app/Models/Crew.php app/Http/Controllers/Admin tests/Feature/ProgramHrTest.php
  git commit -m "feat: penugasan crew program lewat form podcast, terbatas tiga jabatan"
  ```

---

### Task 3: Penjaga hapus program yang punya sesi

**Files:**
- Modify: `app/Http/Controllers/Admin/PodcastController.php`
- Test: `tests/Feature/ProgramHrTest.php`

**Interfaces:**
- Produces: `PodcastController::delete(int $id): RedirectResponse` menimpa induk; ditolak bila `BroadcastSession::where('content_id', $id)->exists()`.

- [ ] **Step 1: Tulis test gagal**
  - `test('program yang punya sesi tidak bisa dihapus')`: program + `BroadcastSession::factory()` + `AbsensiLog::factory()->create(['session_id' => ...])`; sebagai hr `get('/dashboard/podcast/delete/'.$id)` → `assertSessionHas('error', 'Program masih punya sesi siaran. Hapus sesinya dulu di menu Sesi.')`; `Content::count()`, `BroadcastSession::count()`, dan `AbsensiLog::whereNotNull('session_id')->count()` masing-masing 1.
  - `test('program tanpa sesi bisa dihapus beserta penugasan crew-nya')`: program dengan satu crew → hapus → `assertSessionHas('success')`, `Content::count()` 0, `DB::table('content_crew')->count()` 0.
- [ ] **Step 2: Jalankan** `--filter=dihapus`. Expected: test pertama FAIL.
- [ ] **Step 3: Timpa `delete` di `PodcastController`**: bila ada sesi, `back()->with('error', <pesan di atas>)`; selain itu `return parent::delete($id)`.
- [ ] **Step 4: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php`. Expected: PASS.
- [ ] **Step 5: Pint dan commit** (`git add app/Http/Controllers/Admin/PodcastController.php tests/Feature/ProgramHrTest.php`; pesan `feat: program yang punya sesi tidak bisa dihapus`).

---

### Task 4: Tiga dropdown crew di form dan badge jumlah crew

**Files:**
- Modify: `app/Http/Controllers/Admin/PodcastController.php` (`index`), `resources/views/admin/podcast.blade.php`
- Test: `tests/Feature/ProgramHrTest.php`

**Interfaces:**
- Consumes: `Crew::PROGRAM_ROLES`; field `crew_ids[]` (Task 2).
- Produces: `index()` mengirim `crewByRole` (`Collection<string, Collection<int, Crew>>`, kunci tiap jabatan di `PROGRAM_ROLES`, isi diurut nama; kunci tetap ada walau kosong) dan memuat `crews` pada tiap program.

- [ ] **Step 1: Tulis test gagal**
  - `test('form program menampilkan dropdown per jabatan hanya berisi crew jabatan itu')`: buat crew Announcer "Anya", Sound Operator "Soni", Human Resource "Hari", Web Development "Wawan"; sebagai hr `get('/dashboard/podcast')` → `assertSeeInOrder(['Announcer', 'Anya', 'Sound Operator', 'Soni', 'Human Resource', 'Hari'])` dan `assertDontSee('Wawan')`.
  - `test('kartu program memuat jumlah crew dan id crew untuk modal ubah')`: program dengan dua crew → `assertSee('2 crew ditugaskan')` dan `assertSee('data-crews', false)`.
- [ ] **Step 2: Jalankan** `--filter=dropdown --filter=kartu` (atau seluruh berkas). Expected: FAIL.
- [ ] **Step 3: `index()`**: `Content::with('crews')->latest()->paginate(8, pageName: 'podcastpage')`; `crewByRole = Crew::whereIn('role', Crew::PROGRAM_ROLES)->orderBy('name')->get()->groupBy('role')`, lalu pastikan ketiga kunci ada (isi koleksi kosong bila belum).
- [ ] **Step 4: Ubah `podcast.blade.php`**
  - Di dalam `#editPodcastForm`, setelah field deskripsi, tambah bagian "Crew program ini" (teks bantu: dipakai mencocokkan tap RFID ke sesi; hanya tiga jabatan). Untuk tiap jabatan di `PROGRAM_ROLES`: label jabatan, tombol `form-select` pembuka `.dropdown` (`data-bs-toggle="dropdown" data-bs-auto-close="outside"`) berisi area chip, dan `.dropdown-menu w-100` berisi satu `<input type="checkbox" class="crew-check" name="crew_ids[]" value="{{ $crew->id }}" data-name="{{ $crew->name }}">` per crew jabatan itu; placeholder "Pilih {jabatan huruf kecil}" saat belum ada pilihan. Semua checkbox memakai nama sama `crew_ids[]`.
  - Tombol Edit mendapat `data-crews="{{ $podcast->crews->pluck('id')->toJson() }}"`; kartu mendapat badge `{{ $podcast->crews->count() }} crew ditugaskan`.
  - Skrip inline (ikuti gaya berkas): fungsi `syncCrewChips()` (render chip dari checkbox tercentang per dropdown, tombol × pada chip melepas centang, placeholder bila kosong) dipanggil pada `change` tiap `.crew-check`; fungsi `fillCrewChecks(ids: number[])` mencentang sesuai `data-crews` lalu memanggil `syncCrewChips()`. Panggil `fillCrewChecks` di tiga tempat yang mengisi modal ubah (klik Edit, blok `openContentId`) dan `fillCrewChecks([])` untuk "Create new podcast" (`changeTitle(..., true)`), supaya modal tidak membawa sisa pilihan program sebelumnya.
- [ ] **Step 5: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php`. Expected: PASS.
- [ ] **Step 6: Periksa di browser** (jalankan app dengan SQLite sesuai aturan proyek, login `hr`): buka modal Tambah dan Ubah, centang/lepas crew, simpan, buka ulang. Bandingkan dengan frame B/B2 di Figma. Tuliskan hasilnya; tes otomatis tidak mencakup JS.
- [ ] **Step 7: Pint dan commit** (`git add app/Http/Controllers/Admin/PodcastController.php resources/views/admin/podcast.blade.php tests/Feature/ProgramHrTest.php`; pesan `feat: dropdown crew per jabatan di form program`).

---

### Task 5: Dokumen konteks dan verifikasi akhir

**Files:**
- Modify: `docs/konteks-aplikasi.md`

- [ ] **Step 1: Perbarui `docs/konteks-aplikasi.md`**: tabel rute (podcast dipakai admin dan HR; rute `absensi/program*` hilang; hapus `program`, `program/update` dari daftar HR), bagian peran ("admin hanya menu admin" → podcast bersama), `Crew::PROGRAM_ROLES`, hook `afterSave`, penjaga hapus program, ringkasan tes (`ProgramHrTest`, `AbsensiProgramTest` dihapus), tanggal pembaruan.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/ProgramHrTest.php tests/Feature/AdminTest.php tests/Feature/AbsensiSesiTest.php tests/Feature/AbsensiSesiCrudTest.php tests/Feature/BroadcastSessionTest.php tests/Feature/DatabaseSeederTest.php tests/Feature/SitusPublikTest.php`. Expected: PASS.
- [ ] **Step 3: Pint dan commit** (`git add docs/konteks-aplikasi.md`; pesan `docs: perbarui konteks untuk crud program hr`).
- [ ] **Step 4: Minta pengguna menjalankan** `php artisan test --compact` (suite penuh).
