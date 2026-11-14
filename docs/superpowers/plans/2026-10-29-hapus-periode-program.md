# Hapus Periode Program — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hapus periode program dan otomatisasi sesi rutin; program jadi template, semua sesi dibuat manual, `is_customized` dihapus, `regular_date` jadi "tanggal semula".

**Architecture:** Satu migration baru menghapus kolom `programs.period_start/period_end` dan `broadcast_sessions.is_customized`, serta mengisi dan mewajibkan `regular_date`. Task 1 menangani sisi program (migration dibuat di sini), Task 2 sisi sesi (migration yang sama diperluas — belum pernah dijalankan di luar tes). Tidak ada pengganti otomatisasi.

**Tech Stack:** Laravel (PHP 8.5), Blade, Pest (sqlite in-memory).

**Spec:** `docs/superpowers/specs/2026-10-29-hapus-periode-program-design.md`

## Global Constraints

- Migration dibuat dengan `php artisan make:migration remove_period_and_session_customization --no-interaction`; satu file saja untuk kedua task.
- Jangan jalankan `php artisan migrate` ke database dev (MySQL/Sail); user yang menjalankan setelah semua selesai. Tes memakai sqlite in-memory.
- Copy persis:
  - Deskripsi daftar Program: `Template program siaran: hari, jam, dan kru. Dipakai sebagai isian awal saat menambah sesi.`
  - Deskripsi halaman Sesi: `Sesi siaran per program. Tambah sesi, ubah jadwal atau kru, tunda, atau batalkan per sesi.`
  - Deskripsi Tambah sesi: `Jam dan kru diisi dari program. Kalau ada yang kamu ubah, muncul pengingat di bawah isiannya.`
  - Kotak ditunda: `semula {D, d M Y}` (locale `id`); konfirmasi Batalkan: `Batalkan sesi {program} yang semula {d/m/Y}?`
  - Flash setelah Tambah sesi: `Sesi dibuat.` (menggantikan `Sesi tambahan dibuat.`; spec diam soal ini)
- Label `sesi tambahan` dan `tambahan` tidak boleh tersisa di view mana pun.
- PHP: PHPDoc, return type eksplisit, kurung kurawal; `vendor/bin/pint --dirty --format agent` sebelum commit.
- Commit atas nama user, tanpa `Co-Authored-By`/sebutan AI. Jangan stage `docs/` atau `.superpowers/`.
- `tests/Feature/Models/ProgramSyncSessionsTest.php` dihapus (disetujui user). Tes lain tidak dihapus, hanya disesuaikan.

## Review Focus

- Sesi lama yang `regular_date`-nya `NULL` **dan** `session_date`-nya `NULL` (sesi tambahan yang ditunda) → setelah migrate terisi tanggal `created_at`, tidak gagal karena NOT NULL. (Task 2, tes migration)
- Program dengan banyak minggu berturut-turut sudah punya sesi → tanggal default melompat ke minggu kosong pertama, tidak macet. (Task 1)
- Mengubah jam/kru program saat sudah ada sesi mendatang → sesi itu **tidak** berubah. (Task 1)
- Sesi yang tanggalnya tidak pernah diubah → halaman Ubah tidak menampilkan "semula …" yang membingungkan. (Task 2)
- Halaman Absensi per program yang punya sesi lama hasil "Tambah sesi" → tetap tampil tanpa label `tambahan` dan tanpa error. (Task 2)

---

### Task 1: Hapus periode dan sinkronisasi sesi dari program

**Files:**
- Create: `database/migrations/<timestamp>_remove_period_and_session_customization.php` (bagian `programs` saja)
- Modify: `app/Models/Program.php`, `app/Http/Controllers/auth/hr/ProgramController.php`, `app/Http/Requests/auth/hr/ProgramRequest.php`, `database/factories/ProgramFactory.php`, `database/seeders/Gen7ProgramSeeder.php`
- Modify: `resources/views/auth/hr/program/_form.blade.php`, `resources/views/auth/hr/program/index.blade.php`, `resources/views/auth/hr/absensi/index.blade.php`
- Delete: `tests/Feature/Models/ProgramSyncSessionsTest.php`
- Test: `tests/Feature/auth/hr/ProgramTest.php`, `tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php`, `tests/Feature/Models/AbsensiSchemaTest.php`, `tests/Feature/auth/hr/SesiTest.php` (tes tanggal default), `tests/Feature/auth/RoleAccessTest.php` (payload)

**Interfaces:**
- Produces: `Program::nextOpenBroadcastDate(CarbonInterface $from): CarbonImmutable` — tidak pernah `null`.
- Produces: migration file yang diperluas Task 2 (`up()`/`down()` punya bagian `programs`).
- Removes: `Program::syncSessions`, `matchingDates`, `checkInHasOpened`; `ProgramController::syncCrewAndSessions` → `syncCrew(Program $program, ProgramRequest $request): void`.

- [ ] **Step 1: Tulis/ubah tes yang gagal**
  - `ProgramTest`: hapus `period_start`/`period_end` dari payload (`beforeEach`, baris ~26) dan dari tes validasi (pesan `Periode selesai …` dihapus; rename tes jadi `validates times and crew roles with indonesian messages`).
  - Ganti `lets hr create a program and generates its sessions` dengan `lets hr create a program without creating sessions`: post `program.store` → redirect sukses, `Program::count() === 1`, `BroadcastSession::count() === 0`.
  - Ganti `updates a program and resyncs upcoming sessions` dengan `updates a program without touching its sessions`: sesi mendatang (factory, `crewcall_time` `07:30:00`, kru announcer A) → put `program.update` dengan crewcall `09:00` dan kru B → sesi tetap `07:30:00` dan krunya tetap A.
  - Tambah `it('does not show period fields on the program form and list')`: `program.create` dan `program.index` → `assertDontSee('period_start', false)`, `assertDontSee('Periode')`.
  - `AbsensiSchemaTest`: tambah `it('has no program period columns')` → `expect(Schema::hasColumns('programs', ['period_start']))->toBeFalse()` dan sama untuk `period_end`.
  - `ProgramNextOpenBroadcastDateTest`: hapus helper/argumen periode. Tes:
    - `returns the next broadcast day without a session` (day 1, sesi 2026-10-26, from 2026-10-24 → `2026-11-02`)
    - `counts the start date itself when it is an open broadcast day` (day 6, from `2026-10-24 15:00` → `2026-10-24`)
    - `ignores cancelled sessions and sessions of other programs` (→ `2026-10-26`)
    - `skips several consecutive weeks that already have sessions` (sesi 2026-10-26, 11-02, 11-09 → `2026-11-16`)
  - `SesiTest`: tes `defaults the add session date to the next open broadcast day of the program` tanpa `update(['period_end' => …])`, tetap mengharapkan `2026-11-02`; hapus tes `leaves the add session date empty when the program period has no open broadcast day`.
  - `RoleAccessTest` (~baris 65): hapus `period_start`/`period_end` dari payload.
  - Hapus file `tests/Feature/Models/ProgramSyncSessionsTest.php`.

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/hr/ProgramTest.php tests/Feature/Models tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/RoleAccessTest.php`
Expected: FAIL — antara lain validasi `period_start` wajib, sesi masih dibuat saat simpan program, kolom periode masih ada, tes "consecutive weeks" mengembalikan null.

- [ ] **Step 3: Implementasi**
  - Migration `up()`: `Schema::table('programs')` drop `period_start`, `period_end`. `down()`: tambah keduanya `date()->nullable()`.
  - `Program`: buang periode dari Fillable/casts; hapus `syncSessions`, `matchingDates`, `checkInHasOpened` dan import yang tak terpakai; `nextOpenBroadcastDate` jadi non-null — satu query `session_date >= awal hari $from` (status ≠ Cancelled, program ini), lalu maju 7 hari dari hari `day_of_week` pertama ≥ `$from` sampai tanggalnya tidak ada di himpunan itu. PHPDoc diperbarui.
  - `ProgramController`: `syncCrewAndSessions` → `syncCrew` (hanya `crew()->sync`), pemanggilnya di `store`/`update` ikut.
  - `ProgramRequest`: hapus rule, attribute, message, dan field periode.
  - `ProgramFactory`, `Gen7ProgramSeeder`: hapus periode dan konstanta `PERIOD_START`/`PERIOD_END`.
  - Views: hapus isian periode di `_form`, kolom "Periode" + deskripsi baru di `program/index`, kolom "Periode" di `absensi/index`.

- [ ] **Step 4: Jalankan, pastikan lulus**

Run: perintah Step 2, plus `php artisan test --compact tests/Feature/DatabaseSeederTest.php tests/Feature/auth/hr/AbsensiTest.php`
Expected: PASS

- [ ] **Step 5: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A app database resources tests
git status --short   # pastikan docs/ dan .superpowers/ tidak ter-stage
git commit -m "feat: hapus periode program dan pembuatan sesi otomatis"
```

---

### Task 2: Hapus `is_customized` dan jadikan `regular_date` tanggal semula

**Files:**
- Modify: migration dari Task 1 (tambah bagian `broadcast_sessions`)
- Modify: `app/Models/BroadcastSession.php`, `database/factories/BroadcastSessionFactory.php`, `app/Http/Requests/auth/hr/SesiRequest.php`, `app/Http/Controllers/auth/hr/SesiController.php`
- Modify: `resources/views/auth/hr/sesi/index.blade.php`, `resources/views/auth/hr/sesi/create.blade.php`, `resources/views/auth/hr/sesi/edit.blade.php`, `resources/views/auth/hr/absensi/program.blade.php`
- Test: `tests/Feature/auth/hr/SesiTest.php`, `tests/Feature/Models/AbsensiSchemaTest.php`, `tests/Feature/auth/hr/AbsensiTest.php`

**Interfaces:**
- Consumes: migration file Task 1.
- Produces: `broadcast_sessions.regular_date` NOT NULL; tidak ada kolom/atribut `is_customized`; `SesiRequest::sessionAttributes()` mengembalikan `session_date`, tiga jam, `status` saja.

- [ ] **Step 1: Tulis/ubah tes yang gagal**
  - `SesiTest`: hapus semua assertion `is_customized` (baris ~122–214) tanpa mengurangi assertion lain di tes itu.
  - `stores an extra session outside the weekly schedule` → rename `stores a session with its date as the original date`: setelah store, `regular_date->toDateString() === session_date->toDateString()` dan `assertSessionHas('status', 'Sesi dibuat.')`.
  - Ganti `labels a postponed extra session in the reschedule box and edit form` dengan `shows the original date of a postponed session`: sesi ditunda dengan `regular_date` `2026-10-26` → `sesi.index` `assertSee('semula Sen, 26 Okt 2026')`, `assertDontSee('sesi tambahan')`; `sesi.edit` `assertSee('semula Sen, 26 Okt 2026')`; cancel tetap berhasil.
  - Tambah `it('does not show the original date for a session that was never moved')`: sesi `session_date` = `regular_date` = `2026-10-26` → `sesi.edit` `assertDontSee('semula')`.
  - `AbsensiSchemaTest`: hapus cek `is_customized`; tambah `it('has no session customization column and requires the original date')` → `Schema::hasColumn('broadcast_sessions', 'is_customized')` false.
  - Tambah di `AbsensiSchemaTest` tes migration: `it('backfills missing original dates before requiring them')` — `$migration = require` file migration baru; panggil `$migration->down()`, sisipkan via `DB::table` satu sesi `regular_date` null + `session_date` `2026-10-15` dan satu `regular_date` null + `session_date` null + `created_at` `2026-10-20 09:00:00`, panggil `$migration->up()` → `regular_date` masing-masing `2026-10-15` dan `2026-10-20`. Kalau perubahan skema gagal di dalam transaksi `RefreshDatabase`, pakai `uses(DatabaseMigrations::class)` khusus untuk file tes terpisah `tests/Feature/Models/RemovePeriodMigrationTest.php`.
  - `AbsensiTest` (~baris 416): sesi `$this->extra` tanpa `regular_date => null`/`is_customized`; kalau ada assertion `tambahan`, ganti jadi `assertDontSee('tambahan')`.

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php tests/Feature/Models/AbsensiSchemaTest.php tests/Feature/auth/hr/AbsensiTest.php`
Expected: FAIL — `is_customized` masih ada, `regular_date` null setelah store, flash masih `Sesi tambahan dibuat.`, label `sesi tambahan` masih tampil.

- [ ] **Step 3: Implementasi**
  - Migration `up()` (setelah bagian program): update `regular_date = COALESCE(session_date, DATE(created_at))` where null; `regular_date` → `nullable(false)->change()`; drop `is_customized`. `down()` (sebelum bagian program): tambah `is_customized` boolean default false; `regular_date` → `nullable()->change()`. Tulis SQL backfill yang jalan di sqlite dan MySQL (pola `DB::raw` di `2026_10_24_145406_…`).
  - `BroadcastSession` + factory: buang `is_customized`.
  - `SesiRequest::sessionAttributes()`: tanpa `is_customized`; PHPDoc disesuaikan.
  - `SesiController`: `store` isi `regular_date` dengan `session_date` tervalidasi dan flash `Sesi dibuat.`; hapus `is_customized` di `postpone`/`cancel`.
  - Views sesuai Global Constraints: deskripsi `sesi/index` dan `sesi/create`; kotak ditunda & konfirmasi Batalkan tanpa cabang "sesi tambahan"; subjudul `sesi/edit` menampilkan `(semula …)` hanya kalau `session_date` null atau ≠ `regular_date`; hapus label `tambahan` di `absensi/program`.

- [ ] **Step 4: Jalankan, pastikan lulus**

Run: `php artisan test --compact tests/Feature tests/Unit`
Expected: PASS semua. Lalu `grep -rn "is_customized\|period_start\|period_end\|sesi tambahan" app resources database/factories database/seeders tests` → hanya muncul di file migration (lama dan baru).

- [ ] **Step 5: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A app database resources tests
git status --short   # pastikan docs/ dan .superpowers/ tidak ter-stage
git commit -m "feat: hapus penanda sesi kustom, tanggal semula selalu terisi"
```

- [ ] **Step 6: Minta user** menjalankan `./vendor/bin/sail artisan migrate` di dev dan `php artisan test --compact`.
