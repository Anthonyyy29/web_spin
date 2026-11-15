# Absensi per Sesi Siaran Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Absensi RFID dicatat per sesi siaran (program + tanggal + jam); tap di luar sesi dicatat sebagai tamu dan ditampilkan terpisah.

**Architecture:** Tabel baru `broadcast_sessions` dan pivot `content_crew`; `absensi_logs` dapat `session_id` nullable (null = tamu). Pencocokan tap ke sesi ada di model `BroadcastSession` (`findForTap`, `statusAt`), dipakai `AbsensiController::tap`. HR mengelola sesi dan penugasan crew di dua halaman baru di grup `absensi`; riwayat, ekspor, dan dashboard dibedakan sesi/tamu.

**Tech Stack:** Laravel 13, PHP 8.5, Blade + AdminLTE/Bootstrap, Pest (SQLite memori), openspout.

**Spec:** `docs/superpowers/specs/2026-11-16-absensi-sesi-siaran-design.md`

## Global Constraints

- **Kode sederhana dan bersih** (permintaan user): tanpa service/repository/interface baru, tanpa abstraksi yang hanya dipakai sekali; logika pencocokan cukup di model, sisanya di controller; method pendek, nama jelas, ikuti gaya controller `Absensi/` yang sudah ada.
- Tanpa dependensi baru, tanpa folder dasar baru (hanya `docs/` yang sudah disetujui).
- Program = tabel `contents` (model `App\Models\Content`).
- Jendela tap: dari `jam_mulai − 60 menit` sampai `jam_selesai`, batas inklusif. Konstanta `BroadcastSession::TAP_WINDOW_MINUTES = 60`.
- `Terlambat` bila waktu tap > `jam_mulai` (tanpa toleransi); selain itu `Hadir`. Tamu: `status` `Hadir`, `session_id` null.
- Beberapa sesi cocok → pilih `jam_mulai` terdekat dengan waktu tap.
- Pesan tap persis: `UID tidak terbaca`, `Kartu tidak terdaftar` (tetap), `Anda sudah absen di sesi ini` (duplikat sesi), `Anda sudah absen hari ini` (duplikat tamu), `Absen berhasil` (sesi), `Dicatat sebagai tamu` (tamu).
- Migrasi baru dengan `down()`; jangan edit migrasi lama. Buat berkas dengan `php artisan make:*  --no-interaction`.
- Ikuti gaya yang ada: route di grup `absensi` (`role:hr`), redirect + `with('success'/'error')`, hapus lewat GET, teks UI bahasa Indonesia, pakai `$this->travel(2)->seconds()` di tes yang POST beruntun (middleware `actioncheck`).
- Setelah mengubah PHP: `vendor/bin/pint --dirty --format agent`. Tes: `php artisan test --compact <berkas>`.
- Commit atas nama user saja, tanpa `Co-Authored-By` atau sebutan AI.
- Jangan query MySQL dev; semua verifikasi lewat tes SQLite.

## Review Focus

- Kartu terdaftar tetapi `crew_id` null, atau crew tanpa program → tap jadi tamu, bukan error. (Task 1, 2)
- Tap tepat 60 menit sebelum mulai dan tepat di `jam_selesai` cocok; 61 menit sebelum mulai atau 1 detik setelah selesai → tamu. (Task 1)
- Crew di dua program dengan dua sesi cocok → sesi `jam_mulai` terdekat. (Task 1)
- Sesi dihapus (atau programnya dihapus) → log-nya jadi tamu, riwayat dan dashboard tidak error. (Task 4, 5)
- `jam_selesai` ≤ `jam_mulai` atau `content_id` tak ada ditolak di form sesi. (Task 4)
- Tap kedua di sesi yang sama tidak membuat baris baru; tamu kedua di hari yang sama juga tidak. (Task 2)

---

### Task 1: Skema, model, dan pencocokan tap ke sesi

**Files:**
- Create: migration `add_sesi_siaran_to_absensi` (`php artisan make:migration`)
- Create: `app/Models/BroadcastSession.php`, `database/factories/BroadcastSessionFactory.php` (`php artisan make:model BroadcastSession -f --no-interaction`)
- Modify: `app/Models/Content.php`, `app/Models/Crew.php`, `app/Models/AbsensiLog.php`
- Test: `tests/Feature/BroadcastSessionTest.php`

**Interfaces:**
- Produces:
  - Tabel `broadcast_sessions`: `id`, `content_id` (FK contents, cascadeOnDelete), `tanggal` date, `jam_mulai` time, `jam_selesai` time, indeks `tanggal`.
  - Tabel `content_crew`: `content_id`, `crew_id` (keduanya FK cascadeOnDelete), primary/unik pasangan keduanya.
  - `absensi_logs.session_id`: nullable FK → `broadcast_sessions.id`, `nullOnDelete()`. Kunci unik lama (`rfid_uid`,`tanggal`) dihapus, diganti unik (`rfid_uid`,`session_id`). Buat unik baru **sebelum** drop yang lama (FK `rfid_uid` butuh indeks di MySQL). `down()` mengembalikan keadaan lama.
  - `BroadcastSession`: `#[Fillable]`/`$fillable` `content_id, tanggal, jam_mulai, jam_selesai`; cast `tanggal` ke `date`; `const int TAP_WINDOW_MINUTES = 60`; `content(): BelongsTo`; `startsAt(): CarbonImmutable`; `endsAt(): CarbonImmutable`; `statusAt(CarbonInterface $at): string` (`'Hadir'|'Terlambat'`); `static findForTap(?Crew $crew, CarbonInterface $at): ?self`.
  - `Content::crews(): BelongsToMany`, `Crew::contents(): BelongsToMany` (pivot `content_crew`), `AbsensiLog::session(): BelongsTo`; `session_id` masuk `$fillable` `AbsensiLog`.
  - `BroadcastSessionFactory`: default `tanggal` hari ini, `jam_mulai` `10:00:00`, `jam_selesai` `11:00:00`, `content_id` `Content::factory()`. `AbsensiLogFactory` tetap (session_id null).

- [ ] **Step 1: Tulis tes gagal** di `tests/Feature/BroadcastSessionTest.php` (`uses(RefreshDatabase::class)`, `travelTo` tidak perlu; kirim `Carbon` eksplisit). Nama dan assertion:
  - `findForTap: cocok di jendela, batas inklusif` — sesi 10:00–11:00 hari ini, crew ada di program-nya: `09:00:00` → sesi itu; `08:59:59` → null; `11:00:00` → sesi itu; `11:00:01` → null.
  - `findForTap: tamu bila crew null atau di luar program atau hari lain` — `findForTap(null, $at)` null; crew bukan anggota program → null; sesi besok → null.
  - `findForTap: dua sesi cocok, pilih jam mulai terdekat` — crew di dua program; sesi 10:00 dan 10:30, tap 10:20 → sesi 10:30; tap 10:10 → sesi 10:00.
  - `statusAt: Hadir sampai jam mulai, Terlambat sesudahnya` — `10:00:00` → `Hadir`; `10:00:01` → `Terlambat`; `09:30:00` → `Hadir`.
  - `hapus sesi menjadikan log tamu` — `AbsensiLog` dengan `session_id`, hapus sesi → `session_id` log null dan baris tetap ada.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/BroadcastSessionTest.php` → FAIL (kelas/tabel belum ada).
- [ ] **Step 3: Buat migration, model, factory, relasi** sesuai Interfaces. `findForTap`: ambil sesi `tanggal` = tanggal `$at` milik `$crew->contents` (kosong bila `$crew` null), saring di PHP dengan jendela di atas, `sortBy` selisih mutlak `startsAt()` ke `$at`, `first()`.
- [ ] **Step 4: Jalankan** tes Step 2 → PASS. Jalankan juga `php artisan test --compact tests/Feature/AbsensiTest.php` → masih PASS.
- [ ] **Step 5: Pint dan commit**

```bash
vendor/bin/pint --dirty --format agent
git add database app tests
git commit -m "feat: skema sesi siaran dan pencocokan tap ke sesi"
```

### Task 2: Tap dicatat per sesi atau tamu

**Files:**
- Modify: `app/Http/Controllers/Admin/Absensi/AbsensiController.php`
- Modify: `resources/views/admin/absensi/index.blade.php` (blok hasil tap di handler `formTap`, ±baris 305–310)
- Test: `tests/Feature/AbsensiSesiTest.php` (baru)

**Interfaces:**
- Consumes: `BroadcastSession::findForTap(?Crew, CarbonInterface): ?self`, `BroadcastSession::statusAt(CarbonInterface): string`, `AbsensiLog.session_id`.
- Produces: respons JSON `tap()` selalu memuat `status`, `message`; pada `success` juga `nama`, `jam`, `jenis` (`'sesi'|'tamu'`), `program` (judul `Content` atau `null`), `status_absen` (`Hadir|Terlambat`).

- [ ] **Step 1: Tulis tes gagal** di `tests/Feature/AbsensiSesiTest.php` (helper kecil membuat crew + kartu `AB12` + program berisi crew + sesi 10:00–11:00 hari ini; waktu diatur dengan `$this->travelTo(today()->setTime(...))`):
  - `tap di jendela sesi mencatat absen sesi` — 09:55 → `status` success, `message` `Absen berhasil`, `jenis` `sesi`, `program` = judul, `status_absen` `Hadir`; baris punya `session_id` sesi itu.
  - `tap setelah jam mulai dicatat Terlambat` — 10:05 → `status_absen` `Terlambat`, kolom `status` `Terlambat`.
  - `tap kedua di sesi yang sama ditolak` — dua kali → kedua `warning` `Anda sudah absen di sesi ini`; `AbsensiLog::count()` 1.
  - `tap di luar sesi menjadi tamu, satu kali sehari` — 14:00 → success, `message` `Dicatat sebagai tamu`, `jenis` `tamu`, `session_id` null; tap lagi → warning `Anda sudah absen hari ini`; count 1.
  - `kartu tanpa crew tetap tamu tanpa error` — `RfidCrew::create(['rfid_uid' => 'ZZ', 'crew_id' => null])` → success `jenis` `tamu`, `nama` null.
  - `tamu tidak menghalangi absen sesi kemudian` — tap 08:00 (tamu), lalu 09:30 → baris kedua dengan `session_id`; count 2.
- [ ] **Step 2: Jalankan** `php artisan test --compact tests/Feature/AbsensiSesiTest.php` → FAIL.
- [ ] **Step 3: Ubah `tap()`**: validasi UID dan kartu tetap; `$sesi = BroadcastSession::findForTap($kartu->crew, now())`; duplikat dicek per (`rfid_uid`, `session_id`) untuk sesi, per (`rfid_uid`, `tanggal`, `session_id` null) untuk tamu; simpan dengan `status` dari `$sesi->statusAt(now())` atau `Hadir`. Pesan dan kunci JSON sesuai Global Constraints dan Interfaces.
- [ ] **Step 4: Ubah view tap**: di `formTap`, tampilkan `data.program` (bila ada) dan penanda "Terlambat" di bawah `data.message`; kelas alert tetap `success`/`danger`.
- [ ] **Step 5: Jalankan** `php artisan test --compact tests/Feature/AbsensiSesiTest.php tests/Feature/AbsensiTest.php` → PASS (tes lama `tap lewat alat` tetap hijau).
- [ ] **Step 6: Pint dan commit** — `git commit -m "feat: tap dicatat per sesi atau tamu"`.

### Task 3: Penugasan crew ke program

**Files:**
- Create: `app/Http/Controllers/Admin/Absensi/ProgramController.php`, `resources/views/admin/absensi/program.blade.php`
- Modify: `routes/web.php` (grup `absensi`), `resources/views/layouts/navmenus/hr.blade.php`
- Test: `tests/Feature/AbsensiProgramTest.php`

**Interfaces:**
- Consumes: `Content::crews()`.
- Produces: route `GET dashboard/absensi/program` (`index`), `POST dashboard/absensi/program/update` (`update`; field `content_id`, `crew_ids[]`). Menu HR `Program` dengan `$navmenu === 'Program'`.

- [ ] **Step 1: Tulis tes gagal**:
  - `hr melihat program dan menyimpan crew-nya` — HR GET `/dashboard/absensi/program` → OK, `assertSee` judul `Content` dan nama crew; POST `update` dengan `crew_ids` dua crew → `$content->crews` berisi keduanya; POST lagi (setelah `travel(2)->seconds()`) dengan satu crew → hanya satu (sync); `crew_ids` kosong → relasi kosong.
  - `update menolak crew tak ada dan peran lain` — `crew_ids` berisi id 9999 → `assertSessionHasErrors('crew_ids.0')`; admin GET → 403.
- [ ] **Step 2: Jalankan** → FAIL.
- [ ] **Step 3: Implement** `index(): View` (daftar `Content::with('crews')`, crew dari `Crew::sortedByRole()`; satu kartu per program berisi checkbox crew + tombol "Simpan") dan `update(Request): RedirectResponse` (validasi `content_id` exists:contents,id; `crew_ids` `array`, `crew_ids.*` exists:crews,id; `sync`; redirect ke `/dashboard/absensi/program` dengan `success` `Crew program diperbarui`). Tambah dua route dan item menu (ikon `bi bi-broadcast`).
- [ ] **Step 4: Jalankan** tes Step 1 → PASS.
- [ ] **Step 5: Pint dan commit** — `git commit -m "feat: penugasan crew ke program"`.

### Task 4: Kelola sesi siaran

**Files:**
- Create: `app/Http/Controllers/Admin/Absensi/SesiController.php`, `resources/views/admin/absensi/sesi.blade.php`
- Modify: `routes/web.php`, `resources/views/layouts/navmenus/hr.blade.php`
- Test: `tests/Feature/AbsensiSesiCrudTest.php`

**Interfaces:**
- Consumes: `BroadcastSession`, `Content`.
- Produces: `GET dashboard/absensi/sesi` (`index`, query `?tampil=riwayat` untuk sesi lewat; default mendatang, urut tanggal+jam naik, riwayat menurun), `POST sesi/simpan` (`simpan`), `POST sesi/update` (`update`, field `id`), `GET sesi/delete/{id}` (`delete`). Field form: `content_id`, `tanggal` (date), `jam_mulai`, `jam_selesai` (`H:i`). Menu HR `Sesi` (`$navmenu === 'Sesi'`).

- [ ] **Step 1: Tulis tes gagal**:
  - `hr menambah, melihat, mengubah tanggalnya (diundur berminggu), dan menghapus sesi` — simpan → baris ada, redirect `success`; update `tanggal` +21 hari → tersimpan; `index` menampilkan judul program, `?tampil=riwayat` menampilkan sesi kemarin; delete → hilang.
  - `validasi sesi` — `jam_selesai` sama dengan atau sebelum `jam_mulai` → `assertSessionHasErrors('jam_selesai')`; `content_id` 9999 → error `content_id`; `tanggal` bukan tanggal → error.
  - `hapus sesi menjadikan absensinya tamu` — log dengan `session_id`, delete sesi → log tetap ada, `session_id` null.
  - `peran lain ditolak` — admin GET `/dashboard/absensi/sesi` → 403.
- [ ] **Step 2: Jalankan** → FAIL.
- [ ] **Step 3: Implement** controller (model `crew.blade.php` sebagai acuan gaya form/modal; validasi `jam_selesai` `after:jam_mulai`, `date_format:H:i`; tombol hapus memakai `confirm` yang menyebut "absensinya akan menjadi tamu"), view, tiga route + `index`, dan item menu (ikon `bi bi-calendar-event`).
- [ ] **Step 4: Jalankan** tes Step 1 → PASS.
- [ ] **Step 5: Pint dan commit** — `git commit -m "feat: kelola sesi siaran"`.

### Task 5: Riwayat, ekspor, dan dashboard membedakan sesi dan tamu

**Files:**
- Modify: `app/Models/AbsensiLog.php` (scope baru), `app/Http/Controllers/Admin/Absensi/HistoryController.php`, `app/Http/Controllers/DashboardController.php` (`hr()`), `resources/views/admin/absensi/history.blade.php`, `resources/views/admin/absensi/index.blade.php`, `docs/konteks-aplikasi.md`
- Test: `tests/Feature/AbsensiRiwayatTest.php`; tes lama di `tests/Feature/AbsensiTest.php` harus tetap hijau

**Interfaces:**
- Consumes: `AbsensiLog.session_id`, `AbsensiLog::withCrew()`.
- Produces: `AbsensiLog::scopeWithSession(Builder): void` (left join `broadcast_sessions` dan `contents`, tambah select `contents.title as program`, `broadcast_sessions.jam_mulai as jam_sesi`). Query `?jenis=sesi|tamu` pada `history/all` dan `export-excel` (nilai lain/kosong = `sesi`); `history/byUid/{rfid}` tetap memuat semua jenis. Kunci JSON baru `program`, `jam_sesi` (null untuk tamu). View dashboard menerima `tamu_hari_ini` selain `absensi_hari_ini`.

- [ ] **Step 1: Tulis tes gagal** (`AbsensiRiwayatTest`, data: satu log sesi dan satu log tamu hari ini):
  - `history/all?jenis=sesi hanya sesi, ?jenis=tamu hanya tamu, default sesi` — `data` count 1 masing-masing; item sesi memuat `program` dan `jam_sesi`; item tamu keduanya null.
  - `ekspor mengikuti jenis` — `export-excel?jenis=tamu` → `assertDownload`; header kolom ekspor sesi memuat `Program` dan `Jam Sesi` (uji lewat pemanggilan controller/helper bila isi berkas sulit dibaca; minimal `assertOk`+`assertDownload` untuk kedua jenis).
  - `dashboard memisahkan tamu dan hanya sesi di grafik status dan tren` — `/dashboard` HR `assertSee` nama tamu di bagian "Tamu Hari Ini"; `chartStatus` hanya menghitung log bersesi (cek lewat `viewData('chartStatus')`).
  - `log tanpa sesi setelah sesi dihapus tidak merusak riwayat` — hapus sesi, `history/all?jenis=tamu` count 1, `/dashboard` OK.
- [ ] **Step 2: Jalankan** → FAIL.
- [ ] **Step 3: Implement** scope `withSession`; `HistoryController::history()` menerima jenis (dari `request('jenis')`) dan memakai `withCrew()->withSession()`; ekspor menambah kolom `Program` dan `Jam Sesi` (tamu: `-`). `DashboardController::hr()`: tabel harian hanya log bersesi (tambah kolom Program), `tamu_hari_ini` untuk log `session_id` null; `chartStatus` dan `chartTrend` ditambah `whereNotNull('session_id')`; `chartDivisi` tidak berubah.
- [ ] **Step 4: Ubah view**: `history.blade.php` dapat dua tombol tab (Sesi/Tamu) yang memanggil `loadHistory` dengan `?jenis=`, kolom Program dan Jam Sesi, serta tautan ekspor yang membawa `jenis` aktif; `index.blade.php` dapat kolom Program dan tabel "Tamu Hari Ini" (tetap menampilkan `jam_masuk` dan nama).
- [ ] **Step 5: Perbarui `docs/konteks-aplikasi.md`**: rute HR (`sesi`, `program`), tabel (`broadcast_sessions`, `content_crew`, `absensi_logs.session_id`), dan model berguna (`BroadcastSession::findForTap`).
- [ ] **Step 6: Jalankan** `php artisan test --compact tests/Feature/AbsensiRiwayatTest.php tests/Feature/AbsensiTest.php tests/Feature/DashboardTest.php` → PASS.
- [ ] **Step 7: Pint dan commit** — `git commit -m "feat: riwayat, ekspor, dan dashboard membedakan sesi dan tamu"`.

### Penutup

- [ ] Jalankan `php artisan test --compact tests/Feature/Absensi*Test.php tests/Feature/BroadcastSessionTest.php tests/Feature/DashboardTest.php` → semua PASS, lalu minta user menjalankan seluruh suite (`php artisan test --compact`).
