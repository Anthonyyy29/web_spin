# Absensi Crewcall Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti Jadwal Siaran lama dengan Program mingguan + Sesi, lalu tambahkan absensi crewcall (check-in mandiri announcer/SO, koreksi Izin/Sakit oleh HR, rekap + CSV).

**Architecture:** `Program` (template) membuat dan menyinkronkan `BroadcastSession` lewat `Program::syncSessions()`. Aturan jendela dan status check-in ada di `BroadcastSession`. Absensi disimpan di `attendances`; "Tidak hadir" dan "Belum" dihitung. Area HR, announcer, dan SO memakai route group + middleware masing-masing. Halaman "Sesi saya" dipakai bersama announcer dan SO.

**Tech Stack:** Laravel 13, Pest 5 (SQLite in-memory), Blade + Tailwind v4 via Vite, MySQL di Sail.

**Spec:** `docs/superpowers/specs/2026-10-24-absensi-crewcall-design.md`

## Global Constraints

- Commit memakai identitas git repo (ezra). Pesan commit TANPA `Co-Authored-By`, "Claude", "AI", atau nama tool apa pun. Jangan pernah stage `docs/` atau `.superpowers/`.
- Teks UI dan pesan validasi berbahasa Indonesia, memakai `:Attribute` untuk huruf kapital seperti pada `JadwalSiaranRequest` lama.
- Zona waktu aplikasi `Asia/Jakarta`.
- Tes memakai factory dan `uses(RefreshDatabase::class)` per file. Jangan definisikan fungsi global di file tes (rawan bentrok nama). Pakai closure atau `beforeEach`.
- Aturan PHP proyek (`CLAUDE.md`): return type eksplisit, kurung kurawal selalu, PHPDoc dengan array shape, enum key TitleCase. Jalankan `vendor/bin/pint --dirty --format agent` setelah mengubah PHP.
- Desain UI mengikuti token yang ada (`bg-canvas`, `text-ink`, `text-muted`, `border-line`, `bg-signal-strong`, `text-danger`, `bg-signal`) dan pola tampilan view Jadwal Siaran lama: tabel `rounded-lg border border-line bg-white`, tombol utama `bg-signal-strong`, partial `auth.partials.status` dan `auth.partials.nav-link`.
- Nama route HR tanpa prefix `hr.`, mengikuti konvensi `jadwalSiaran.*` yang ada: `program.*`, `sesi.*`, `absensi.*`.
- Jendela check-in: buka `crewcall − 15 menit` (inklusif), tutup `tapping_start` (eksklusif). Hadir jika check-in < `crewcall + 6 menit` (artinya ≤ menit ke-5 penuh). Terlambat jika setelah itu.
- Ekspor CSV dibuat dengan `response()->streamDownload` dan `fputcsv`, tanpa dependensi baru.

## Review Focus

- Sesi yang dipindah atau ditunda HR tidak boleh dibuat ulang di tanggal aslinya saat program diubah (dilacak lewat `regular_date`). Tes ada di Task 2.
- Batas jendela dan zona waktu: 07.14:59 ditolak, 07.15:00 boleh, 07.35:59 Hadir, 07.36:00 Terlambat, 08.00:00 ditolak, semuanya di jam WIB. Tes ada di Task 3.
- Memperpendek periode atau mengganti hari program tidak menghapus sesi yang sudah punya absensi atau sudah di-customize. Tes ada di Task 2.
- Sesi `postponed` (tanggal dan jam null) tidak membuat daftar sesi, "Sesi saya", rekap, atau CSV error, dan tidak dihitung di rekap. Tes ada di Task 5 dan Task 7.
- Kru yang dikeluarkan dari program setelah check-in tetap punya absensi dan tetap terhitung di rekap. Sinkronisasi kru tidak menyentuh `attendances`. Tes ada di Task 2 dan Task 7.

---

### Task 1: Skema, enum, model, factory, zona waktu

**Files:**
- Modify: `config/app.php` (`'timezone' => 'Asia/Jakarta'`)
- Create: `app/Enums/SessionStatus.php`, `app/Enums/AttendanceStatus.php`, `app/Enums/CrewPosition.php`
- Create migration (via `php artisan make:migration`): `create_absensi_tables` dengan `programs`, `program_crew`, `broadcast_sessions`, `session_crew`, `attendances`. Kolom sesuai spec, ditambah `broadcast_sessions.regular_date` (date, not null, index) dan index `broadcast_sessions.session_date`.
- Create: `app/Models/Program.php`, `app/Models/BroadcastSession.php`, `app/Models/Attendance.php` + factory masing-masing.
- Modify: `app/Models/User.php` (relasi baru; `broadcastSchedules()` dihapus di Task 4), `database/factories/UserFactory.php` (state `so()`)
- Test: `tests/Feature/Models/AbsensiSchemaTest.php`

**Interfaces:**
- Produces:
  - `enum SessionStatus: string { Scheduled = 'scheduled'; Postponed = 'postponed'; Cancelled = 'cancelled'; }` + `label(): string` (Terjadwal / Ditunda / Batal).
  - `enum AttendanceStatus: string { Hadir='hadir'; Terlambat='terlambat'; Izin='izin'; Sakit='sakit'; TidakHadir='tidak_hadir'; Belum='belum'; }` + `label(): string` (Hadir / Terlambat / Izin / Sakit / Tidak hadir / Belum). Hanya empat case pertama yang pernah disimpan di DB.
  - `enum CrewPosition: string { Announcer='announcer'; So='so'; }` + `label(): string` (Announcer / SO).
  - `Program`: fillable `name, day_of_week, crewcall_time, tapping_start, tapping_end, period_start, period_end`; cast `period_start`/`period_end` => `date`; `crew(): BelongsToMany` (User, tabel `program_crew`, `withPivot('position')`); `sessions(): HasMany` (BroadcastSession).
  - `BroadcastSession`: tabel `broadcast_sessions`; fillable `program_id, regular_date, session_date, crewcall_time, tapping_start, tapping_end, status, is_customized`; cast `regular_date`/`session_date` => `date`, `status` => `SessionStatus`, `is_customized` => `bool`; `program(): BelongsTo`; `crew(): BelongsToMany` (User, `session_crew`, FK `broadcast_session_id`, `withPivot('position')`); `attendances(): HasMany`.
  - `Attendance`: fillable `broadcast_session_id, user_id, checked_in_at, status, note`; cast `checked_in_at` => `datetime`, `status` => `AttendanceStatus`; `session(): BelongsTo` (BroadcastSession, `broadcast_session_id`); `user(): BelongsTo`.
  - `User`: `programs(): BelongsToMany` (via `program_crew`), `broadcastSessions(): BelongsToMany` (via `session_crew`), `attendances(): HasMany`.
  - Factories:
    - `ProgramFactory` default: `day_of_week` 1, `crewcall_time` `07:30:00`, `tapping_start` `08:00:00`, `tapping_end` `09:30:00`, `period_start` `2026-10-05`, `period_end` `2026-10-26`.
    - `BroadcastSessionFactory` default: `regular_date` = `session_date` = `2026-10-26`, jam sama dengan ProgramFactory, `Scheduled`, `is_customized` false. State `postponed()` (tanggal/jam null), `cancelled()`.
    - `AttendanceFactory` default status `Hadir`, `checked_in_at` `2026-10-26 07:20:00`.
    - `UserFactory::so(): static` (role `so`).
  - FK: `attendances.broadcast_session_id` restrictOnDelete; `program_crew`, `session_crew`, dan `broadcast_sessions.program_id` cascadeOnDelete.

- [ ] **Step 1:** Tulis tes gagal di `AbsensiSchemaTest.php`:
  - `it links a program, its session crew and attendance` → buat Program + sesi + attach kru (`position` so) + Attendance. Assert `$session->crew->first()->pivot->position === 'so'`, `$session->attendances->first()->status === AttendanceStatus::Hadir`, `$user->broadcastSessions` berisi sesi.
  - `it uses the Jakarta timezone` → `expect(config('app.timezone'))->toBe('Asia/Jakarta')`.
- [ ] **Step 2:** Run `php artisan test --compact tests/Feature/Models/AbsensiSchemaTest.php`. Expected: FAIL (kelas belum ada).
- [ ] **Step 3:** Implementasi file-file di atas.
- [ ] **Step 4:** Run tes yang sama. Expected: PASS. Run `php artisan test --compact`. Expected: PASS (tes Jadwal Siaran lama masih hijau, karena tabel `broadcast_schedules` baru di-drop di Task 4).
- [ ] **Step 5:** Commit: `feat: skema program, sesi, dan absensi`

### Task 2: Sinkronisasi sesi dari program

**Files:**
- Modify: `app/Models/Program.php`
- Test: `tests/Feature/Models/ProgramSyncSessionsTest.php`

**Interfaces:**
- Consumes: model dan factory dari Task 1.
- Produces: `Program::syncSessions(CarbonInterface $today): void`.

Algoritma (dalam satu transaksi DB):
1. `matchingDates` = setiap tanggal dalam `[period_start, period_end]` dengan `isoWeekday() === day_of_week`.
2. Jika program belum punya sesi sama sekali, buat sesi untuk **semua** `matchingDates`. Jika sudah punya, buat hanya untuk tanggal ≥ `$today` yang belum punya sesi dengan `regular_date` itu.
3. Sesi baru: `regular_date` = `session_date` = tanggal; jam dari program; `Scheduled`; `is_customized` false; kru disalin dari `program->crew` (dengan `position`).
4. Untuk sesi `is_customized = false` dengan `regular_date ≥ $today`:
   - Jika `regular_date` tidak ada di `matchingDates`: hapus hanya jika tidak punya attendance. Jika punya, biarkan.
   - Jika ada di `matchingDates`: setel `session_date = regular_date`, salin jam dari program, dan sync kru ke kru program (`sync` dengan pivot `position`). `attendances` tidak disentuh.
5. Sesi `is_customized = true` dan sesi dengan `regular_date < $today` tidak disentuh.

- [ ] **Step 1:** Tes gagal (semua memakai `$today` eksplisit):
  - `it creates a session for every matching weekday in the period` → periode `2026-10-05`–`2027-01-25`, Senin, `$today = 2026-10-24` → 17 sesi, tanggal pertama `2026-10-05`, terakhir `2027-01-25`, setiap sesi punya kru program.
  - `it updates only upcoming sessions that were not customized` → sync awal, tandai satu sesi mendatang `is_customized` + ubah jamnya, ubah crewcall program ke `08:00:00`, sync lagi dengan `$today = 2026-11-01` → sesi sebelum `2026-11-01` masih `07:30:00`, sesi customized tetap jam miliknya, sesi mendatang lain `08:00:00`.
  - `it does not recreate a moved session on its regular date` → pindahkan sesi `2026-11-02` ke `2026-11-03` (`is_customized` true), sync lagi → tidak ada sesi lain dengan `regular_date 2026-11-02`, total sesi tetap 17.
  - `it removes sessions outside a shortened period unless they have attendance` → perpendek `period_end` ke `2026-11-30`. Sesi `2026-12-07` punya attendance, yang lain tidak. Sync → sesi tanpa absensi setelah `2026-11-30` terhapus, sesi `2026-12-07` tetap.
  - `it keeps attendance when a crew member is removed from the program` → kru A check-in di sesi mendatang, A dikeluarkan dari program, sync → A tidak lagi di `session->crew`, tapi baris attendance A tetap ada.
- [ ] **Step 2:** Run `php artisan test --compact tests/Feature/Models/ProgramSyncSessionsTest.php`. Expected: FAIL.
- [ ] **Step 3:** Implementasi `syncSessions`.
- [ ] **Step 4:** Run tes. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: sinkronisasi sesi dari program`

### Task 3: Aturan jendela dan status check-in

**Files:**
- Modify: `app/Models/BroadcastSession.php`
- Test: `tests/Feature/Models/BroadcastSessionCheckInRulesTest.php`

**Interfaces:**
- Produces:
  - `checkInOpensAt(): ?CarbonImmutable`: `session_date` + `crewcall_time` − 15 menit. Null jika bukan `Scheduled` atau tanggal/jam null.
  - `checkInClosesAt(): ?CarbonImmutable`: `session_date` + `tapping_start`. Null dengan syarat yang sama.
  - `isCheckInOpen(CarbonInterface $now): bool`: `opensAt ≤ now < closesAt`.
  - `isCheckInClosed(CarbonInterface $now): bool`: `Scheduled` dan `now ≥ closesAt`.
  - `statusForCheckIn(CarbonInterface $checkedInAt): AttendanceStatus`: Hadir jika `< crewcall + 6 menit`, selain itu Terlambat.
  - `attendanceStatusFor(User $user, CarbonInterface $now): AttendanceStatus`: status baris attendance user jika ada. Jika tidak ada: `TidakHadir` jika `isCheckInClosed($now)`, selain itu `Belum`.
  - Semua perhitungan memakai zona waktu aplikasi.

- [ ] **Step 1:** Tes gagal, untuk sesi `2026-10-26` crewcall `07:30`, tapping `08:00`:
  - `it opens check-in fifteen minutes before crewcall` → `isCheckInOpen` false di `07:14:59`, true di `07:15:00`.
  - `it closes check-in when tapping starts` → true di `07:59:59`, false di `08:00:00`.
  - `it marks check-ins up to five full minutes after crewcall as present` → `statusForCheckIn` `07:35:59` = Hadir, `07:36:00` = Terlambat.
  - `it never opens check-in for postponed or cancelled sessions` → `checkInOpensAt()` null, `isCheckInOpen` false.
  - `it derives absent and pending statuses` → tanpa attendance: `Belum` di `07:50`, `TidakHadir` di `08:00`. Attendance `Izin` mengembalikan `Izin`.
- [ ] **Step 2:** Run `php artisan test --compact tests/Feature/Models/BroadcastSessionCheckInRulesTest.php`. Expected: FAIL.
- [ ] **Step 3:** Implementasi.
- [ ] **Step 4:** Run tes. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: aturan jendela check-in sesi`

### Task 4: Hapus Jadwal Siaran lama + CRUD Program HR

**Files:**
- Delete:
  - `app/Http/Controllers/auth/hr/JadwalSiaranController.php`
  - `app/Http/Requests/auth/hr/JadwalSiaranRequest.php`
  - `app/Models/BroadcastSchedule.php`
  - `database/factories/BroadcastScheduleFactory.php`
  - `resources/views/auth/hr/jadwalSiaran/`
  - `resources/views/auth/announcer/jadwalSiaran/`
  - `resources/views/auth/partials/jadwal-cells.blade.php`
  - `tests/Feature/auth/hr/JadwalSiaranTest.php`
  - `tests/Feature/auth/announcer/JadwalSiaranTest.php`
  - method `AnnouncerController::jadwalSiaran`
  - `User::broadcastSchedules()`
  - route `jadwalSiaran` dan `announcer.jadwalSiaran`
- Create migration (via `php artisan make:migration`): `drop_broadcast_schedules_table`. `up()` men-drop `broadcast_schedules`; `down()` membuat ulang skema persis seperti `2026_10_23_012720_create_broadcast_schedules_table.php`. Migration lama tidak dihapus, karena mungkin sudah dijalankan di MySQL Sail.
- Create: `app/Http/Controllers/auth/hr/ProgramController.php`, `app/Http/Requests/auth/hr/ProgramRequest.php`, `resources/views/auth/hr/program/{index,create,edit,_form}.blade.php`
- Modify: `routes/web.php`, `resources/views/auth/hr/layout.blade.php` (nav: Dashboard, Program, Sesi, Absensi, Pengaturan nonaktif; Sesi dan Absensi masih `route => null` sampai Task 5 dan 7), `resources/views/auth/announcer/layout.blade.php` (nav: Profile, Sesi saya `route => null` sampai Task 6)
- Test: `tests/Feature/auth/hr/ProgramTest.php`

**Interfaces:**
- Consumes: `Program::syncSessions()` (Task 2).
- Produces:
  - Route `Route::resource('program', ProgramController::class)->except('show')` di group HR. Parameter `{program}`.
  - `ProgramRequest`:
    - Field: `name`, `day_of_week` (1–7), `crewcall_time`, `tapping_start`, `tapping_end` (`date_format:H:i`), `period_start`, `period_end` (`date_format:Y-m-d`, `period_end` `after_or_equal:period_start`), `announcer_ids` (array, min 1, tiap id ada dengan role `announcer`), `so_ids` (array nullable, tiap id ada dengan role `so`).
    - `after()`: `crewcall_time < tapping_start < tapping_end`, dan tidak ada id yang sama di `announcer_ids` dan `so_ids`.
    - `programAttributes(): array`: field program dengan jam dalam `H:i:s`.
    - `crewPositions(): array<int, string>`: `user_id => position`.
  - Controller store/update: simpan program, `crew()->sync(crewPositions mapped ke ['position' => ...])`, `syncSessions(today())`, redirect `program.index` + flash `status`.
  - Destroy: ditolak (redirect back dengan error `Program tidak bisa dihapus karena sudah ada absensi.`) jika ada attendance di sesinya. Selain itu hapus.
  - Index: kolom Program, Hari (nama hari Indonesia), Crewcall, Tapping, Periode, Kru (jumlah announcer/SO), Aksi.

- [ ] **Step 1:** Tes gagal:
  - `it lets hr create a program and generates its sessions` → POST, assert redirect, `programs` ada, jumlah sesi sesuai periode, kru tersimpan dengan posisi.
  - `it validates times, period and crew roles` → crewcall ≥ tapping_start, period_end < period_start, `announcer_ids` berisi user `so` → masing-masing error pada field yang tepat, dengan pesan berbahasa Indonesia.
  - `it rejects the same person in two positions`
  - `it updates a program and resyncs upcoming sessions`
  - `it refuses to delete a program that has attendance` dan `it deletes a program without attendance` (sesi ikut terhapus).
  - `it forbids non hr users from program routes` → announcer dan so dapat 403, guest diarahkan ke login.
- [ ] **Step 2:** Run tes. Expected: FAIL.
- [ ] **Step 3:** Hapus file lama, buat migration drop, implementasi program.
- [ ] **Step 4:** Run `php artisan test --compact`. Expected: PASS, tanpa referensi ke Jadwal Siaran lama (`grep -rn "jadwalSiaran\|BroadcastSchedule" app routes resources tests` kosong).
- [ ] **Step 5:** `npm run build`, lalu Pint. Commit: `feat: program mingguan menggantikan jadwal siaran`

### Task 5: Halaman Sesi HR (daftar, ubah, tunda, batal)

**Files:**
- Create: `app/Http/Controllers/auth/hr/SesiController.php`, `app/Http/Requests/auth/hr/SesiRequest.php`, `resources/views/auth/hr/sesi/{index,edit}.blade.php`, `resources/views/auth/partials/sesi-cells.blade.php`
- Modify: `routes/web.php`, nav HR (Sesi aktif)
- Test: `tests/Feature/auth/hr/SesiTest.php`

**Interfaces:**
- Produces routes (group HR), parameter `{sesi}` dibind ke `BroadcastSession $sesi`:
  - `GET sesi` → `sesi.index`, query `?program=<id>` opsional.
  - `GET sesi/{sesi}/edit` → `sesi.edit`
  - `PUT sesi/{sesi}` → `sesi.update`
  - `POST sesi/{sesi}/tunda` → `sesi.postpone`
  - `POST sesi/{sesi}/batal` → `sesi.cancel`
- Index:
  - Kotak "Perlu dijadwalkan ulang" berisi sesi `Postponed`, tiap item dengan link Jadwalkan (edit).
  - Tabel sesi bertanggal, urut `session_date` desc lalu `crewcall_time`, paginate 15, eager load `program` + `crew`.
  - Filter program berupa select.
  - Partial `sesi-cells` menampilkan tanggal (format Indonesia + penanda "Hari ini"), crewcall, tapping, serta label status (Terjadwal / Ditunda / Batal).
- `SesiRequest`:
  - `session_date` (`date_format:Y-m-d`), `crewcall_time`, `tapping_start`, `tapping_end` (`H:i`), `announcer_ids`/`so_ids` (aturan sama dengan ProgramRequest).
  - `after()`: urutan jam, lalu cek bentrok: untuk setiap kru, ada sesi `Scheduled` lain (bukan sesi ini) di `session_date` yang sama, dengan kru itu, dan `tapping_start < new tapping_end AND tapping_end > new tapping_start` → error `tapping_start`: `Bentrok dengan sesi lain milik :name.` (`:name` = username).
- Update: simpan tanggal dan jam, sync kru, `status = Scheduled`, `is_customized = true`.
- Postpone: tanggal dan jam null, `Postponed`, `is_customized = true`.
- Cancel: `Cancelled`, `is_customized = true`.

- [ ] **Step 1:** Tes gagal:
  - `it lists postponed sessions in the reschedule box` → sesi `postponed` muncul di bagian "Perlu dijadwalkan ulang", halaman tidak error.
  - `it postpones a session and clears its date and times`
  - `it reschedules a postponed session` → PUT tanggal baru, status kembali `scheduled`, `is_customized` true.
  - `it cancels a session`
  - `it rejects a crew member booked in an overlapping session`
  - `it filters sessions by program`
  - `it forbids non hr users from session routes`
- [ ] **Step 2:** Run tes. Expected: FAIL.
- [ ] **Step 3:** Implementasi.
- [ ] **Step 4:** Run tes. Expected: PASS. Pint.
- [ ] **Step 5:** Commit: `feat: kelola sesi siaran`

### Task 6: Role SO, "Sesi saya", dan check-in

**Files:**
- Create: `app/Http/Middleware/isSo.php` (pola sama dengan `isAnnouncer`), `resources/views/auth/so/layout.blade.php` (`@extends('auth.layout')`, role "Sound operator", nav "Sesi saya"), `app/Http/Controllers/auth/crew/SesiSayaController.php`, `resources/views/auth/crew/sesi/index.blade.php`
- Modify: `routes/web.php`, `app/Http/Controllers/UserController.php` (role `so` → `redirect()->intended(route('so.sesi.index'))`), nav announcer (Sesi saya aktif)
- Test: `tests/Feature/auth/crew/CheckInTest.php`

**Interfaces:**
- Consumes: aturan check-in dari Task 3.
- Produces routes:
  - group announcer: `GET sesi` → `announcer.sesi.index`, `POST sesi/{sesi}/check-in` → `announcer.sesi.checkIn`
  - group baru `prefix so`, middleware `['auth', isSo::class]`: `GET sesi` → `so.sesi.index`, `POST sesi/{sesi}/check-in` → `so.sesi.checkIn`
  - Kedua group memakai `SesiSayaController@index` / `@checkIn`.
- `index(): View`:
  - Sesi `auth()->user()->broadcastSessions()` dengan status `Scheduled` atau `Postponed` (yang `Cancelled` tidak ditampilkan), urut `session_date` asc (null di akhir), paginate 15.
  - View mendapat `$layout = 'auth.'.$role.'.layout'` dan `$routePrefix = $role` (`announcer` atau `so`).
  - Tiap baris: program, tanggal, crewcall, tapping, label `attendanceStatusFor(user, now())`, jam check-in bila ada.
  - Tombol Check-in hanya jika `isCheckInOpen(now())` dan belum ada attendance.
- `checkIn(BroadcastSession $sesi): RedirectResponse`:
  - 403 jika user bukan kru sesi.
  - Error `Sesi ini tidak bisa di-check-in.` jika bukan `Scheduled`.
  - Error `Check-in hanya bisa dari 15 menit sebelum crewcall sampai tapping dimulai.` jika di luar jendela.
  - Error `Kamu sudah check-in di sesi ini.` jika sudah ada attendance.
  - Selain itu buat Attendance dengan `checked_in_at = now()` dan `status = statusForCheckIn(now())`, redirect back dengan flash `Check-in tercatat: <label> <H:i>.`
  - Bila insert kena unique constraint (klik ganda), kembalikan error yang sama dengan "sudah check-in".

- [ ] **Step 1:** Tes gagal (sesi `2026-10-26`, crewcall 07.30, tapping 08.00, `travelTo`):
  - `it lets an announcer check in on time` (07.20 → Hadir) dan `it lets a sound operator check in late` (07.40 → Terlambat), melalui route masing-masing.
  - `it rejects check-in outside the window` (07.14, 08.00)
  - `it rejects check-in for a non crew member` → 403
  - `it rejects check-in for postponed and cancelled sessions`
  - `it rejects a second check-in`
  - `it shows my sessions including postponed ones without errors`
  - `it redirects a sound operator to their sessions after login`
  - `it keeps roles out of each other's areas` → so ke `/announcer/sesi` dan `/hr/program` 403, announcer ke `/so/sesi` 403.
- [ ] **Step 2:** Run tes. Expected: FAIL.
- [ ] **Step 3:** Implementasi.
- [ ] **Step 4:** Run tes. Expected: PASS. `npm run build`, Pint.
- [ ] **Step 5:** Commit: `feat: check-in crewcall announcer dan so`

### Task 7: Absensi HR (per sesi, per orang, CSV)

**Files:**
- Create: `app/Http/Controllers/auth/hr/AbsensiController.php`, `app/Http/Requests/auth/hr/AbsensiStatusRequest.php`, `resources/views/auth/hr/absensi/{index,session}.blade.php`
- Modify: `routes/web.php`, nav HR (Absensi aktif), `resources/views/auth/hr/sesi/index.blade.php` (link "Absensi" per sesi)
- Test: `tests/Feature/auth/hr/AbsensiTest.php`

**Interfaces:**
- Consumes: `attendanceStatusFor`, `isCheckInClosed` (Task 3).
- Produces routes (group HR):
  - `GET absensi` → `absensi.index` (per orang, query `from`, `to` dengan format `Y-m-d`; default tanggal 1 bulan ini s/d hari ini)
  - `GET absensi/export` → `absensi.export` (CSV per orang, filter sama)
  - `GET sesi/{sesi}/absensi` → `absensi.session`
  - `GET sesi/{sesi}/absensi/export` → `absensi.session.export`
  - `PUT sesi/{sesi}/absensi/{user}` → `absensi.update`
- Per sesi: tiap kru sesi dengan posisi, `attendanceStatusFor`, jam check-in, catatan, dan form ubah status (pilihan: Izin, Sakit, atau "Kembalikan").
- `AbsensiStatusRequest`: `status` in `izin,sakit,reset`; `note` nullable string max 255. User harus kru sesi (404 jika bukan).
  - Izin/Sakit: `updateOrCreate` attendance dengan status itu dan note, **tanpa mengubah** `checked_in_at` (tetap null jika belum pernah check-in).
  - Reset: jika `checked_in_at` ada, kembalikan status ke `statusForCheckIn(checked_in_at)` dan kosongkan note. Jika tidak ada, hapus baris.
- Per orang: semua user ber-role announcer dan so. Hitung Hadir, Terlambat, Izin, Sakit, Tidak hadir dari sesi `Scheduled` dalam rentang (`session_date` antara from–to) yang `isCheckInClosed(now())` dan di mana user adalah kru. Satu private method dipakai bersama oleh `index` dan `export`.
- CSV per orang, header: `Username,Role,Hadir,Terlambat,Tidak hadir,Izin,Sakit`.
- CSV per sesi, header: `Program,Tanggal,Crewcall,Username,Posisi,Status,Jam check-in,Catatan`.
- Nama file: `rekap-absensi-<from>-<to>.csv` / `absensi-<program>-<tanggal>.csv` (slug).

- [ ] **Step 1:** Tes gagal:
  - `it shows each crew member's status for a session` (Hadir, Terlambat, Tidak hadir setelah tutup, Belum sebelum tutup)
  - `it lets hr mark a member as sick with a note` dan `it resets an override back to the checked in status`
  - `it does not let hr set a check-in time` → mengirim `checked_in_at` diabaikan
  - `it summarises attendance per person for a date range` → termasuk Tidak hadir. Sesi postponed, cancelled, dan sesi yang belum tutup tidak dihitung. Kru yang dikeluarkan setelah check-in tetap terhitung Hadir.
  - `it exports the per person summary as csv` dan `it exports a session as csv` → `assertDownload`, header dan satu baris data sesuai.
  - `it forbids non hr users from attendance routes`
- [ ] **Step 2:** Run tes. Expected: FAIL.
- [ ] **Step 3:** Implementasi.
- [ ] **Step 4:** Run tes. Expected: PASS. `npm run build`, Pint.
- [ ] **Step 5:** Commit: `feat: rekap dan ekspor absensi crewcall`

### Task 8: Seeder contoh

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Pertahankan akun `HR`/`hr` dan `announcer`/`announcer`. Tambah akun SO `so`/`so` dan dua announcer contoh.
- Buat satu program contoh "K-Time" (Senin, 07.30, 08.00–09.30, periode mulai Senin minggu ini selama 16 minggu) dengan kru tersebut, lalu panggil `syncSessions(today())`.

- [ ] **Step 1:** Implementasi.
- [ ] **Step 2:** Run `php artisan migrate:fresh --seed` terhadap SQLite sementara (env `DB_CONNECTION=sqlite DB_DATABASE=<file scratch>`, **jangan** ke MySQL Sail). Expected: sukses, `programs` 1, `broadcast_sessions` 16.
- [ ] **Step 3:** Run `php artisan test --compact`. Expected: PASS.
- [ ] **Step 4:** Commit: `feat: seeder contoh program dan kru`

---

## Self-Review

- Cakupan spec:
  - Keputusan 1 (hapus fitur lama) → Task 4
  - Keputusan 2 (role so) → Task 6
  - Keputusan 3 (zona waktu) → Task 1
  - Keputusan 4–5 (pembuatan dan sinkronisasi sesi) → Task 2, 4
  - Keputusan 6 (ubah/tunda/batal sesi) → Task 5
  - Keputusan 7 (check-in) → Task 3, 6
  - Keputusan 8 (Izin/Sakit oleh HR) → Task 7
  - Keputusan 9 (rekap dan CSV) → Task 7
  - Seeder → Task 8
- Koreksi dari spec:
  - Contoh "Senin 6 Okt" di spec salah, karena 6 Okt 2026 adalah Selasa. Tes memakai 5 Okt 2026 s/d 25 Jan 2027 (17 Senin).
  - Kolom `regular_date` ditambahkan agar sesi yang dipindah tidak dibuat ulang.
- Migration drop `broadcast_schedules` sengaja ada di Task 4, bersamaan dengan penghapusan fitur lama, agar tes lama tetap hijau sampai saat itu.
- Di luar spec (tidak dikerjakan): cek bentrok kru antar **program** saat program disimpan. Bentrok hanya dicek saat sesi diubah (Task 5).
