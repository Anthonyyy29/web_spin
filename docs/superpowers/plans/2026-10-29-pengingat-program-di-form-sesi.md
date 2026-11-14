# Pengingat Nilai Program di Form Sesi — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Form Tambah/Ubah sesi HR terisi default dari program (termasuk tanggal hari siaran berikutnya yang belum punya sesi), dan menampilkan tulisan oranye di bawah isian yang nilainya beda dari program.

**Architecture:** Server menghitung tanggal default (`Program::nextOpenBroadcastDate`) dan menaruh nilai program di form sebagai data attribute plus elemen tulisan yang awalnya `hidden`. JS kecil di `resources/js/app.js` membandingkan nilai isian dengan data attribute saat halaman dibuka dan setiap `input`/`change`, lalu menampilkan/menyembunyikan tulisannya. Tidak ada validasi, modal, atau perubahan penyimpanan.

**Tech Stack:** Laravel (PHP 8.5), Blade, Tailwind v4, vanilla JS via Vite, Pest.

**Spec:** Desain final disetujui di chat (2026-10-29), tidak ada file spec. Ringkasannya:
- Tambah sesi: jam & kru dari program (sudah ada), tanggal default = hari siaran program berikutnya (mulai hari ini) yang belum punya sesi.
- Ubah sesi: tetap berisi data sesi itu.
- Tulisan oranye (`text-signal-strong`) di bawah isian yang beda dari program; hilang kalau nilainya dikembalikan.
- Dicek saat halaman dibuka dan tiap kali diubah; pakai `aria-live`.
- Tanpa modal, tanpa validasi baru; konfirmasi check-in di Ubah tetap seperti sekarang.

## Global Constraints

- Copy (persis):
  - Tanggal: `Bukan hari siaran program ({hari})` — contoh `Bukan hari siaran program (Jumat)`
  - Crewcall / Tapping mulai / Tapping selesai: `Beda dari program ({H:i})` — contoh `Beda dari program (10:00)`
  - Kru: `Dilepas dari kru program: {nama, nama}.` dan/atau `Bukan kru program: {nama, nama}.` (gabung dengan spasi kalau dua-duanya ada)
- Kelas tulisan: `mt-1.5 text-sm text-signal-strong`, atribut `aria-live="polite"`, awalnya `hidden`.
- Nama hari: `Senin`…`Minggu` dengan nomor ISO 1–7 (sama dengan `ProgramController::DAY_NAMES` sekarang).
- Form Program (`hr/program/_form.blade.php`) yang juga memakai `crew-fields` tidak boleh menampilkan tulisan/atribut ini.
- Tidak menambah dependensi. Komentar Blade/JS berbahasa Indonesia, mengikuti file sekitarnya.
- Commit atas nama user, tanpa `Co-Authored-By` atau sebutan AI.
- Setelah ubah PHP: `vendor/bin/pint --dirty --format agent`.

## Review Focus

- Program yang periodenya sudah habis atau tidak punya hari kosong dalam 53 minggu ke depan → tanggal default kosong, bukan error. (Task 1)
- Sesi yang **dibatalkan** di hari siaran tidak menghalangi hari itu jadi default (HR justru mungkin mau mengganti sesi itu); sesi program **lain** di tanggal yang sama juga tidak menghalangi. (Task 1)
- Isian tanggal dikosongkan → tulisan tanggal disembunyikan, tidak muncul "Bukan hari siaran". (Task 3, cek manual)
- Hari dari `YYYY-MM-DD` dihitung dengan waktu lokal (`new Date(value + 'T00:00')`), bukan UTC, supaya tidak geser sehari. (Task 3, cek manual)
- Kru program yang sudah tidak punya peran announcer/SO (tidak ada checkbox-nya) tidak membuat JS error; namanya cukup dilewati. (Task 3, cek manual)

---

### Task 1: Tanggal default dari hari siaran program

**Files:**
- Modify: `app/Models/Program.php` (tambah konstanta `DAY_NAMES` dan method `nextOpenBroadcastDate`)
- Modify: `app/Http/Controllers/auth/hr/ProgramController.php:22-30` (hapus konstanta lokal, pakai `Program::DAY_NAMES` di baris 48 dan 146)
- Modify: `app/Http/Controllers/auth/hr/SesiController.php` (`create`)
- Test: `tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php` (baru, ikuti gaya `ProgramSyncSessionsTest.php`)
- Test: `tests/Feature/auth/hr/SesiTest.php`

**Interfaces:**
- Produces: `Program::DAY_NAMES` — `array<int, string>` `[1 => 'Senin', …, 7 => 'Minggu']`
- Produces: `Program::nextOpenBroadcastDate(CarbonInterface $from): ?CarbonImmutable` — tanggal pertama ≥ `$from` (awal hari) yang ISO weekday-nya `day_of_week` dan belum punya sesi program ini dengan `session_date` itu dan status selain `Cancelled`; cari maksimal 53 minggu, lalu `null`.

- [ ] **Step 1: Tulis tes model yang gagal** di `tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php` (factory default: `day_of_week` 1 = Senin; 2026-10-24 = Sabtu):

```php
it('returns the next broadcast day without a session', function () {
    $program = Program::factory()->create(['day_of_week' => 1]);
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-11-02');
});

it('counts the start date itself when it is an open broadcast day', function () {
    $program = Program::factory()->create(['day_of_week' => 6]);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24 15:00'))->toDateString())->toBe('2026-10-24');
});

it('ignores cancelled sessions and sessions of other programs', function () {
    $program = Program::factory()->create(['day_of_week' => 1]);
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26', 'status' => SessionStatus::Cancelled]);
    BroadcastSession::factory()->for(Program::factory())->create(['session_date' => '2026-10-26']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-10-26');
});

it('returns null when every broadcast day in the next 53 weeks has a session', function () {
    $program = Program::factory()->create(['day_of_week' => 1]);
    foreach (range(0, 52) as $week) {
        BroadcastSession::factory()->for($program)->create(['session_date' => CarbonImmutable::parse('2026-10-26')->addWeeks($week)]);
    }

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24')))->toBeNull();
});
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php`
Expected: FAIL, `Call to undefined method App\Models\Program::nextOpenBroadcastDate()`

- [ ] **Step 3: Implementasi** `Program::DAY_NAMES` dan `nextOpenBroadcastDate(CarbonInterface $from): ?CarbonImmutable` di `app/Models/Program.php`. Ambil semua `session_date` terisi milik program ini dengan status ≠ `Cancelled` dalam rentang `[$from, $from + 53 minggu]` dengan satu query, lalu loop per minggu mulai dari hari `day_of_week` pertama ≥ `$from`. Ganti `self::DAY_NAMES` di `ProgramController` dengan `Program::DAY_NAMES` dan hapus konstanta lamanya.

- [ ] **Step 4: Jalankan, pastikan lulus**

Run: `php artisan test --compact tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php tests/Feature/auth/hr/ProgramTest.php`
Expected: PASS

- [ ] **Step 5: Tulis tes form yang gagal** di `tests/Feature/auth/hr/SesiTest.php` (setelah `prefills the add session form from the chosen program`):

```php
it('defaults the add session date to the next open broadcast day of the program', function () {
    ($this->sessionWithCrew)(['session_date' => '2026-10-26']);

    $response = $this->actingAs($this->hr)
        ->get(route('sesi.create', ['program' => $this->program->id]))
        ->assertOk()
        ->assertSee('name="session_date" value="2026-11-02"', false);

    expect($response->viewData('session')->session_date->toDateString())->toBe('2026-11-02');
});
```

- [ ] **Step 6: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php --filter="next open broadcast day"`
Expected: FAIL (`session_date` null)

- [ ] **Step 7: Implementasi** — di `SesiController::create`, tambahkan `'session_date' => $program->nextOpenBroadcastDate(now())` ke atribut `new BroadcastSession([...])` saat program ada.

- [ ] **Step 8: Jalankan seluruh tes sesi & program**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/hr/ProgramTest.php tests/Feature/Models`
Expected: PASS

- [ ] **Step 9: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/Program.php app/Http/Controllers/auth/hr/ProgramController.php app/Http/Controllers/auth/hr/SesiController.php tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php tests/Feature/auth/hr/SesiTest.php
git commit -m "feat: tanggal default tambah sesi ikut hari siaran program"
```

---

### Task 2: Nilai program dan tulisan pengingat di form

**Files:**
- Modify: `app/Http/Controllers/auth/hr/SesiController.php` (`create`, `edit`, `formData`)
- Modify: `resources/views/auth/hr/sesi/_fields.blade.php`
- Modify: `resources/views/auth/hr/partials/crew-fields.blade.php`
- Test: `tests/Feature/auth/hr/SesiTest.php`, `tests/Feature/auth/hr/ProgramTest.php`

**Interfaces:**
- Consumes: `Program::DAY_NAMES` (Task 1)
- Produces (view data dari `formData`): `$programReference` — `array{dayOfWeek: int, dayName: string, crewcall_time: string, tapping_start: string, tapping_end: string, announcerIds: list<int>, soIds: list<int>}`, jam berformat `H:i`. `formData(BroadcastSession $session, Collection $crew, Program $program)` — parameter ketiga baru; `create` mengirim `$program`, `edit` mengirim `$sesi->program` (eager load `program.crew`).
- Produces (markup, kontrak untuk Task 3):
  - `<input id="session_date" … data-program-weekday="{dayOfWeek}">` + `<p id="session_date-program" data-program-hint hidden …>Bukan hari siaran program ({dayName})</p>`
  - `<input id="crewcall_time|tapping_start|tapping_end" … data-program-value="{H:i}">` + `<p id="{id}-program" data-program-hint hidden …>Beda dari program ({H:i})</p>`
  - `<fieldset … data-program-crew='[id,…]'>` untuk announcer dan SO, + `<p id="announcer_ids-program|so_ids-program" data-program-hint hidden …></p>` (teks diisi JS). Hanya dirender kalau `$programReference` ada.

- [ ] **Step 1: Tulis tes yang gagal** di `SesiTest.php`:

```php
it('renders program reference values on the add session form', function () {
    $this->program->update(['day_of_week' => 5, 'crewcall_time' => '07:30:00', 'tapping_start' => '08:00:00', 'tapping_end' => '09:30:00']);
    $this->program->crew()->attach($this->announcer, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->get(route('sesi.create', ['program' => $this->program->id]))
        ->assertOk()
        ->assertSee('data-program-weekday="5"', false)
        ->assertSee('Bukan hari siaran program (Jumat)')
        ->assertSee('data-program-value="07:30"', false)
        ->assertSee('Beda dari program (09:30)')
        ->assertSee("data-program-crew='[{$this->announcer->id}]'", false)
        ->assertSee("data-program-crew='[]'", false);
});

it('compares an edited session with its program', function () {
    $this->program->update(['crewcall_time' => '07:30:00']);
    $session = ($this->sessionWithCrew)(['crewcall_time' => '09:00:00', 'is_customized' => true]);

    $this->actingAs($this->hr)
        ->get(route('sesi.edit', $session))
        ->assertOk()
        ->assertSee('value="09:00"', false)
        ->assertSee('data-program-value="07:30"', false);
});
```

Dan di `ProgramTest.php`:

```php
it('does not render session program hints on the program form', function () {
    $this->actingAs($this->hr)->get(route('program.create'))->assertOk()->assertDontSee('data-program-crew', false);
});
```

(Sesuaikan nama user HR dengan setup `ProgramTest.php`.)

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/hr/ProgramTest.php --filter="program reference|compares an edited|program hints"`
Expected: 2 FAIL (Sesi), 1 PASS (Program)

- [ ] **Step 3: Implementasi** `formData` + `$programReference`, lalu markup di `_fields.blade.php` dan `crew-fields.blade.php` sesuai kontrak di Interfaces. Di `crew-fields`, pakai `$programReference ?? null` supaya include dari form Program tetap aman. Tulis atribut JSON pakai `@json(...)` dalam kutip tunggal.

- [ ] **Step 4: Jalankan, pastikan lulus**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/hr/ProgramTest.php`
Expected: PASS

- [ ] **Step 5: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/auth/hr/SesiController.php resources/views/auth/hr/sesi/_fields.blade.php resources/views/auth/hr/partials/crew-fields.blade.php tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/hr/ProgramTest.php
git commit -m "feat: nilai program sebagai pengingat di form sesi"
```

---

### Task 3: Tampilkan tulisan saat isian beda dari program (JS)

**Files:**
- Modify: `resources/js/app.js` (tambah blok baru di bawah kode popover, dengan komentar Indonesia seperti blok popover)

**Interfaces:**
- Consumes: kontrak markup Task 2 (`data-program-weekday`, `data-program-value`, `data-program-crew`, `#{id}-program`).

- [ ] **Step 1: Implementasi** — satu fungsi `refreshProgramHint(field)` dan pemanggilnya:
  - Input `[data-program-value]`: tulisan tampil kalau `value !== ''` dan `value !== dataset.programValue`.
  - Input `[data-program-weekday]`: tulisan tampil kalau `value !== ''` dan hari ISO dari `new Date(value + 'T00:00')` (`getDay()`, 0 → 7) ≠ `dataset.programWeekday`.
  - Fieldset `[data-program-crew]`: bandingkan id checkbox tercentang dengan JSON; ambil nama dari teks `<label>` checkbox; susun copy `Dilepas dari kru program: …` / `Bukan kru program: …` (lihat Global Constraints); checkbox yang tidak ada di DOM dilewati; tulisan `hidden` kalau tidak ada beda.
  - Jalankan untuk semua elemen saat `DOMContentLoaded`/load modul, dan pada event `input` + `change` yang di-delegate dari `document`.

- [ ] **Step 2: Build aset**

Run: `npm run build`
Expected: build sukses tanpa error.

- [ ] **Step 3: Cek manual di browser** (`http://localhost/hr/sesi/create?program={id}`, login HR):
  - Saat dibuka: tidak ada tulisan oranye (semua nilai = program).
  - Ganti crewcall → muncul `Beda dari program (…)`; kembalikan → hilang.
  - Pilih tanggal di hari lain → muncul `Bukan hari siaran program (…)`; kosongkan tanggal → hilang.
  - Lepas satu kru program dan centang orang lain → muncul kedua kalimat kru.
  - Submit dengan error validasi (mis. tapping selesai < mulai) → setelah reload, tulisan untuk nilai `old()` yang beda langsung tampil.

- [ ] **Step 4: Jalankan tes terkait**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/hr/ProgramTest.php tests/Feature/Models`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/js/app.js
git commit -m "feat: tulisan pengingat saat isian sesi beda dari program"
```

- [ ] **Step 6: Minta user menjalankan seluruh suite**: `php artisan test --compact`
