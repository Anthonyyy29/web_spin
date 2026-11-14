# Rapikan Tampilan Mobile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Di layar HP (di bawah 768px), halaman tidak lagi bergeser ke samping. Daftar HR dan "Sesi saya" kru tampil sebagai baris bertumpuk yang terbaca tanpa digeser. Rekap matriks tetap berupa tabel geser yang rapi.

**Architecture:** Tidak ada perubahan PHP di luar tes. Semua perubahan ada di kelas Tailwind Blade.
- **Bug geser halaman.** Penyebabnya: label `sr-only` (`position: absolute`) di dalam tabel lolos dari pembungkus `overflow-x-auto` karena pembungkusnya tidak `relative`. Perbaikannya: semua pembungkus diberi `relative`, lalu dikunci dengan satu tes invarian.
- **Daftar.** Markup tabel yang sama diubah jadi blok di HP lewat varian `max-md:`. Tidak ada markup daftar kedua, supaya form dan `id` tidak dobel. Desktop (≥ `md`) tetap tabel seperti sekarang.

**Tech Stack:** Laravel 13, PHP 8.5, Blade + Tailwind v4 (`max-md:`, `md:`), Pest 5.

**Spec:** Tidak ada dokumen spec terpisah. Keputusan dari percakapan 2026-11-01 dicatat di bagian **Keputusan** dan **Temuan** di bawah. Plan B (isi dashboard HR dan announcer) dirancang terpisah setelah plan ini selesai.

## Keputusan (dari user)

- Pekerjaan dibagi dua plan. Plan ini (A) hanya merapikan halaman yang sudah ada. Dashboard tetap placeholder.
- Pola tabel di HP **campuran**:
  - Daftar jadi baris bertumpuk: Program, Sesi, Absensi per program, Absensi sesi, dan Sesi saya.
  - Rekap matriks tetap tabel geser dengan kolom nama menempel di kiri: Absensi per orang dan Absensi satu program.

## Temuan (diukur di lebar 390px, salinan SQLite)

| Halaman | Masalah |
|---|---|
| `/hr/program`, `/hr/sesi`, `/hr/sesi/{id}/absensi` | Seluruh halaman bisa digeser (lebar dokumen 605 / 424 / 545px). Dengan `relative` di pembungkus, lebarnya kembali 390. |
| `/hr/sesi` | Baris filter meluap: "Tambah sesi" terdorong ke luar garis kanan. |
| `/hr/sesi/{id}/absensi` | Nama patah 3–4 baris. Form "Ubah status" ada di kolom paling kanan dan tidak terlihat. |
| `/hr/absensi` | Angka Hadir/Terlambat/… tertutup di kanan. Header patah. |
| `/announcer/sesi`, `/so/sesi` | Tombol **Check-in** dan kolom Kehadiran ada di luar layar. Pesan kosong tidak di tengah layar. |
| `/hr/absensi/orang`, `/hr/absensi/program/{id}` | Kolom nama yang menempel memakan lebih dari separuh layar (`whitespace-nowrap`). Ada jarak kosong besar di atas tombol Terapkan (`pt-7` saat baris patah). |
| Header mobile | Logo `h-8` terlalu kecil. |

## Global Constraints

- **HP** berarti di bawah `md` (768px). Semua kelas tampilan HP memakai varian `max-md:`. Teks khusus HP memakai `md:hidden`. Tampilan ≥ `md` tidak berubah, kecuali tambahan `relative` di pembungkus.
- **Satu markup.** Jangan membuat daftar HP terpisah dari tabel. Elemen tabel diubah jadi blok di HP.
- **Resep baris bertumpuk** (dipakai Task 2–5):
  - `table` dan setiap `tbody` diberi `max-md:block`.
  - `thead` diberi `max-md:hidden`.
  - Baris data `tr` diberi `max-md:grid max-md:gap-x-3 max-md:gap-y-1 max-md:px-4 max-md:py-3`, ditambah `max-md:grid-cols-*` yang ditentukan tiap task. Grid dipilih, bukan flex-wrap, supaya letak sel tidak bergantung panjang teks.
  - `td` diberi `max-md:p-0`, ditambah penempatan grid (`max-md:col-span-*`, `max-md:col-start-*`) dari tiap task.
  - Baris kosong (`@empty`) diberi `td` → `max-md:block max-md:px-4 max-md:py-10`.
  - Kotak luar tetap `rounded-lg border border-line bg-white`, dan pemisah tetap `divide-y divide-line`. Jangan pecah jadi kartu-kartu terpisah dengan bayangan.
- **Label HP.** Nilai yang kehilangan header di HP diberi label teks `<span class="md:hidden">…</span>`, berisi kata yang sama persis dengan header kolomnya. Contoh: `Crewcall`, `Tapping`, `Sesi selesai`, `Hadir`, `Terlambat`, `Izin`, `Sakit`, `Tidak hadir`. Pakai sentence case, bukan huruf kapital, dan jangan pakai pemisah `·` buatan. Jarak antarnilai cukup dari `gap-x-3`.
- Tombol utama di HP (Check-in, Simpan, tombol aksi ⋯) minimal setinggi 44px (`max-md:min-h-11`, atau `size-11` untuk tombol ikon).
- Hanya token warna yang sudah ada: `ink`, `muted`, `line`, `canvas`, `signal`, `signal-strong`, `danger`. Tidak ada font atau warna baru.
- Teks HTML yang sudah ada (header, copy, `option`, `aria-label`) tidak diubah. Kalau sebuah tes lama gagal karena label HP baru, sempitkan assertion-nya (contoh: `'>Tidak hadir</td>'`). **Jangan** hapus tes.
- Pengecekan visual hanya di server SQLite (bagian **Cara cek di HP**). **Tidak pernah** di MySQL dev, termasuk `sail`, `migrate`, atau query apa pun.
- PHP hanya berubah di file tes. Setelah itu jalankan `vendor/bin/pint --dirty --format agent`.
- Commit atas nama user (git config yang ada), **tanpa** `Co-Authored-By` dan tanpa sebutan AI. Pesan commit gaya repo, misalnya `fix: ...` atau `feat: ...`. Jangan commit `docs/superpowers`.

## Cara cek di HP

Dipakai di langkah verifikasi setiap task. Chrome tidak bisa mengecil di bawah ±600px, jadi halaman dibuka dalam iframe selebar 390px.

1. Siapkan salinan cermin lalu jalankan server (background):
   ```bash
   S=/private/tmp/claude-501/-Users-zra-Kuliah-SPIN-web-spin/5ad7f524-a477-4740-9e66-9a23033059e3/scratchpad
   cp $S/mirror.sqlite $S/ui-check.sqlite
   cd public && DB_CONNECTION=sqlite DB_DATABASE=$S/ui-check.sqlite APP_URL=http://localhost:8123 \
     php -S 127.0.0.1:8123 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
   ```
   Jangan pakai `php artisan serve`, karena proses anaknya bisa membaca ulang `.env` yang menunjuk ke MySQL. Bukti server memakai SQLite: `select count(*) from sessions` di `ui-check.sqlite` bertambah setelah `curl http://127.0.0.1:8123/login`.
2. Login di `http://localhost:8123/login`:
   - HR: akun dari `DatabaseSeeder`.
   - Kru: username announcer dari `Gen7MemberSeeder` dengan `Gen7MemberSeeder::DEFAULT_PASSWORD`.
   Vite dev server user (`public/hot`) harus hidup supaya CSS baru termuat. Kalau mati, minta user menjalankan `npm run dev`.
3. Supaya tombol Check-in terlihat, buka satu jendela check-in di **salinan** (bukan cermin, bukan MySQL):
   ```bash
   sqlite3 $S/ui-check.sqlite "update broadcast_sessions set session_date=date('now','localtime'), crewcall_time=time('now','localtime','+10 minutes'), tapping_start=time('now','localtime','+40 minutes'), tapping_end=time('now','localtime','+100 minutes') where id=(select broadcast_session_id from session_crew where user_id=10 limit 1)"
   ```
4. Jalankan di konsol tab (lewat `javascript_tool`). Hasilnya `doc` harus `390` dan semua `inView` harus `true`:
   ```js
   window.check = (path, selectors = [], width = 390) => new Promise(done => {
     document.body.innerHTML = ''; document.body.style.margin = '0';
     const f = document.createElement('iframe');
     f.style.cssText = `width:${width}px;height:763px;border:0;display:block;background:#fff`;
     f.onload = () => { const d = f.contentWindow.document;
       done({ path, doc: d.documentElement.scrollWidth,
         inView: selectors.map(s => [s, [...d.querySelectorAll(s)].every(e => e.getBoundingClientRect().right <= width)]) }); };
     f.src = path; document.body.appendChild(f);
   });
   ```
   Ambil screenshot area `[0,0,390,763]` untuk dilihat mata.

## Review Focus

1. **Nama panjang di baris bertumpuk.** Contoh: "Syavirni Dwi Amalia (Sound Operator)" atau "THE UNTOLD STORY". Teksnya harus patah baris tanpa mendorong tombol keluar layar. Dicek dengan `check()` di Task 2–5 (selector tombol `inView`).
2. **Pesan kosong di HP.** Harus di tengah lebar layar, bukan di tengah tabel 700px. Dicek di `/hr/sesi` tab Mendatang dan "Sesi saya" Mendatang (Task 3, Task 5).
3. **Sesi ditunda di "Sesi saya".** Baris `colspan` "Menunggu jadwal baru / Ditunda" harus tetap terbaca sebagai satu baris bertumpuk. Ditangani di Task 5 dan dikunci tes yang ada di `CheckInTest`.
4. **Popover aksi sesi di HP.** Menu ⋯ yang ditempel JS `data-anchored-popover` harus muncul utuh di dalam layar. Dicek dengan klik tombol ⋯ di Task 3.
5. **Lebar 768–1024px** (sidebar 240px muncul). Tabel tampil sebagai tabel lagi dan hanya bergeser di dalam kotaknya. Dicek dengan `check(path, [], 800)` di Task 6.

---

### Task 1: Hentikan geser halaman dan perbesar logo header

**Files:**
- Modify: `resources/views/auth/hr/program/index.blade.php:22`, `resources/views/auth/hr/sesi/index.blade.php:67`, `resources/views/auth/hr/absensi/index.blade.php:10`, `resources/views/auth/hr/absensi/session.blade.php:37`, `resources/views/auth/crew/sesi/table.blade.php:2`. Tambahkan `relative` di depan `overflow-x-auto`.
- Modify: `resources/views/auth/layout.blade.php:13`. Logo header mobile `h-8` → `h-10`.
- Create (`php artisan make:test --pest auth/ResponsiveLayoutTest --no-interaction`): `tests/Feature/auth/ResponsiveLayoutTest.php`

**Interfaces:**
- Produces: tes invarian yang dipakai ulang Task 2–6. Setiap task menjalankannya lagi.

- [ ] **Step 1: Tulis tes yang gagal**

```php
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-10-26 07:20:00');
    $this->hr = User::factory()->hr()->create();
    $this->announcer = User::factory()->announcer()->create();
    $this->program = Program::factory()->create();
    $this->program->crew()->attach($this->announcer->id, ['position' => 'announcer']);
    $this->session = BroadcastSession::factory()->for($this->program)->create();
    $this->session->crew()->attach($this->announcer->id, ['position' => 'announcer']);
});

it('keeps every horizontal scroll area as the containing block of the labels inside it', function (string $who, Closure $url) {
    $html = $this->actingAs($this->{$who})->get($url->call($this))->assertOk()->getContent();

    preg_match_all('/class="([^"]*\boverflow-x-auto\b[^"]*)"/', $html, $matches);

    expect($matches[1])->not->toBeEmpty();
    foreach ($matches[1] as $classes) {
        expect($classes)->toMatch('/(^|\s)relative(\s|$)/');
    }
})->with([
    'program' => ['hr', fn () => route('program.index')],
    'sesi' => ['hr', fn () => route('sesi.index')],
    'absensi per program' => ['hr', fn () => route('absensi.index')],
    'absensi per orang' => ['hr', fn () => route('absensi.people', ['from' => '2026-10-01', 'to' => '2026-10-31'])],
    'absensi satu program' => ['hr', fn () => route('absensi.program', $this->program)],
    'absensi sesi' => ['hr', fn () => route('absensi.session', $this->session)],
    'sesi saya' => ['announcer', fn () => route('announcer.sesi.index')],
]);
```

- [ ] **Step 2: Jalankan dan pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/ResponsiveLayoutTest.php`
Expected: 5 FAIL (program, sesi, absensi per program, absensi sesi, sesi saya) dan 2 PASS (per orang, satu program sudah `relative`).

- [ ] **Step 3: Tambahkan `relative` di kelima pembungkus dan ubah logo menjadi `h-10`**

- [ ] **Step 4: Jalankan tes dan cek di HP**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact tests/Feature/auth/ResponsiveLayoutTest.php tests/Feature/auth/MobileMenuTest.php`
Expected: semua PASS.
Cek di HP: `check()` untuk `/hr/program`, `/hr/sesi`, `/hr/sesi/16/absensi` → `doc: 390`.

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/auth/ResponsiveLayoutTest.php resources/views/auth/layout.blade.php resources/views/auth/hr/program/index.blade.php resources/views/auth/hr/sesi/index.blade.php resources/views/auth/hr/absensi/index.blade.php resources/views/auth/hr/absensi/session.blade.php resources/views/auth/crew/sesi/table.blade.php
git commit -m "fix: halaman tidak lagi bergeser ke samping di hp"
```

### Task 2: Program dan Absensi per program jadi baris bertumpuk

**Files:**
- Modify: `resources/views/auth/hr/program/index.blade.php:22-63`
- Modify: `resources/views/auth/hr/absensi/index.blade.php:10-45`
- Test: `tests/Feature/auth/hr/ProgramTest.php`, `tests/Feature/auth/hr/AbsensiTest.php`, `tests/Feature/auth/ResponsiveLayoutTest.php` (yang sudah ada; tidak ada tes baru, karena perubahan murni kelas)

**Interfaces:**
- Consumes: resep baris bertumpuk dan label HP dari Global Constraints.

Tata letak HP, satu baris = satu blok:

```
Program                         Absensi per program
┌────────────────────────────┐  ┌────────────────────────────┐
│ K-TIME           Ubah Hapus│  │ K-TIME                Senin│
│ Senin                      │  │ Sesi selesai Hadir Terlambat│
│ Crewcall 07:30             │  │ 4            3     1        │
│ Tapping 08:00 - 09:30      │  │ Izin         Sakit Tidak hadir│
│ 2 announcer, 1 SO          │  │ 0            0     0        │
└────────────────────────────┘  └────────────────────────────┘
```

- [ ] **Step 1: Program.**
  - Baris: `max-md:grid-cols-[1fr_auto]`.
  - Sel nama: kolom 1 baris 1.
  - Sel aksi (Ubah/Hapus): `max-md:col-start-2 max-md:row-start-1`.
  - Sel Hari, Crewcall, Tapping, Kru: masing-masing `max-md:col-span-2`, satu per baris.
  - Label HP: `Crewcall`, `Tapping`. Hari dan Kru tanpa label.
  - Link Ubah dan tombol Hapus diberi `max-md:inline-flex max-md:min-h-11 max-md:items-center`.
- [ ] **Step 2: Absensi per program.**
  - Baris: `max-md:grid-cols-3`.
  - Sel nama program: `max-md:col-span-2`. Sel hari: `max-md:text-right max-md:text-muted`.
  - Keenam sel angka: `max-md:text-left`, satu sel per kolom, jadi dua baris berisi tiga.
  - Label HP di tiap sel angka ditaruh di atas angkanya: `<span class="block text-xs text-muted md:hidden">Hadir</span>`, dengan kata yang sama dengan header.
  - Nilai tetap `tabular-nums`, dan `Tidak hadir > 0` tetap `font-medium text-danger`.
- [ ] **Step 3: Jalankan tes**

Run: `php artisan test --compact tests/Feature/auth/hr/ProgramTest.php tests/Feature/auth/hr/AbsensiTest.php tests/Feature/auth/ResponsiveLayoutTest.php`
Expected: semua PASS. Kalau ada assertion lama yang gagal karena label HP, sempitkan sesuai Global Constraints.

- [ ] **Step 4: Cek di HP**

`check('/hr/program', ['main a[href*="/edit"]', 'main form button'])` dan `check('/hr/absensi')` → `doc: 390` dan semua `inView: true`. Di screenshot, tampilan cocok dengan sketsa dan "THE UNTOLD STORY" tidak mendorong Ubah/Hapus. Cek juga di 1280px (jendela biasa): tabel desktop tetap sama seperti sebelumnya.

- [ ] **Step 5: Commit**

```bash
git add resources/views/auth/hr/program/index.blade.php resources/views/auth/hr/absensi/index.blade.php
git commit -m "feat: daftar program dan rekap absensi rapi di hp"
```

### Task 3: Halaman Sesi (filter, grup tanggal, menu aksi)

**Files:**
- Modify: `resources/views/auth/hr/sesi/index.blade.php:11-26` (form filter) dan `:67-130` (tabel)
- Test: `tests/Feature/auth/hr/SesiTest.php`, `tests/Feature/auth/ResponsiveLayoutTest.php`

**Interfaces:**
- Consumes: resep baris bertumpuk. Popover `data-anchored-popover` (`resources/js/app.js`) tidak diubah.

```
┌ Program ─────────────┐
│[Semua program ▾][Terapkan][Tambah sesi]│   ← satu baris, select menyusut
Mendatang  Riwayat
┌──────────────────────────────┐
│ Rabu, 28 Okt 2026  Hari ini  │  ← th grup tanggal, blok
│ BISIK BERNADA            [⋯] │
│ Crewcall 10:00  Tapping 10:30–12:00│  ← dua kolom sejajar antarbaris
│ Terjadwal       2 announcer, 1 SO  │
└──────────────────────────────┘
```

- [ ] **Step 1: Form filter.**
  - Form: `flex w-full items-end gap-2 md:w-auto`.
  - Pembungkus select: `min-w-0 flex-1 md:flex-none`. Select: `w-full md:w-auto`.
  - "Terapkan" dan "Tambah sesi": `whitespace-nowrap`.
  - Urutan dan elemen tidak berubah; "Tambah sesi" tetap di dalam form.
- [ ] **Step 2: Tabel.**
  - `table`, setiap `tbody` (termasuk `@empty`), dan `th[scope=rowgroup]` diberi `max-md:block`.
  - Baris sesi diberi resep baris bertumpuk plus `max-md:relative max-md:grid-cols-2 max-md:pr-14`.
  - Sel program: `max-md:col-span-2 max-md:whitespace-normal`.
  - Crewcall dan Tapping mengisi satu baris (kolom 1 dan 2). Status dan Kru mengisi baris berikutnya.
  - Sel aksi: `max-md:absolute max-md:top-1.5 max-md:right-1.5 max-md:p-0`. Tombol ⋯ diberi `max-md:size-11`.
  - Label HP: `Crewcall`, `Tapping`.
  - Status dan Kru tanpa label, tetap `text-muted`, dan Dibatalkan tetap `text-danger`.
- [ ] **Step 3: Jalankan tes**

Run: `php artisan test --compact tests/Feature/auth/hr/SesiTest.php tests/Feature/auth/ResponsiveLayoutTest.php`
Expected: semua PASS.

- [ ] **Step 4: Cek di HP**
  - `check('/hr/sesi', ['main form a', 'main form button'])` (tab Mendatang kosong di cermin) → pesan "Belum ada sesi mendatang." di tengah layar.
  - `check('/hr/sesi?waktu=riwayat', ['button[popovertarget]'])` → `doc: 390` dan semua `inView`.
  - Klik satu tombol ⋯ di dalam iframe (`d.querySelector('button[popovertarget]').click()`). Lalu pastikan `getBoundingClientRect()` popover `left >= 0` dan `right <= 390`.

- [ ] **Step 5: Commit**

```bash
git add resources/views/auth/hr/sesi/index.blade.php
git commit -m "feat: halaman sesi rapi di hp"
```

### Task 4: Absensi satu sesi, dengan form ubah status di bawah tiap kru

**Files:**
- Modify: `resources/views/auth/hr/absensi/session.blade.php:37-89`
- Test: `tests/Feature/auth/hr/AbsensiTest.php`, `tests/Feature/auth/ResponsiveLayoutTest.php`

**Interfaces:**
- Consumes: resep baris bertumpuk. `id` form `status-{user}` dan `note-{user}` tidak berubah. Ada tes yang menegaskan string `<option …>` persis, jadi jangan sentuh tag `option`.

```
┌──────────────────────────────┐
│ Belva Vanya Aulia  Announcer │
│ Sakit  Check-in 07:20        │
│ Catatan: Check-in dari luar…  │  ← hanya kalau ada catatan
│ [Sakit ▾        ] [Simpan]   │
│ [Catatan                   ] │
└──────────────────────────────┘
```

- [ ] **Step 1: Baris data.**
  - Pakai resep baris bertumpuk dengan `max-md:grid-cols-[1fr_auto]`.
  - Sel username: kolom 1. Sel posisi: kolom 2, `max-md:text-right max-md:text-muted`.
  - Sel status: kolom 1 baris 2.
  - Sel jam check-in: kolom 2 baris 2, `max-md:text-right`, label HP `Check-in`. Disembunyikan di HP saat nilainya `-` (`max-md:hidden`, hanya kalau `checked_in_at` null).
  - Sel catatan: `max-md:col-span-2`, label HP `Catatan:`, dan `max-md:hidden` saat catatan kosong.
- [ ] **Step 2: Sel form (hanya `$isScheduled`).**
  - Sel: `max-md:col-span-2 max-md:pt-2`.
  - Form: `max-md:grid max-md:grid-cols-[1fr_auto] max-md:gap-2`.
  - Select: `max-md:w-full max-md:min-h-11`.
  - Tombol Simpan: `max-md:min-h-11`, di kolom kedua baris pertama.
  - Input catatan: `max-md:col-span-2 max-md:w-full max-md:min-h-11`. Urutan DOM select → input → tombol tetap; posisi diatur dengan `max-md:order-*`.
- [ ] **Step 3: Jalankan tes**

Run: `php artisan test --compact tests/Feature/auth/hr/AbsensiTest.php tests/Feature/auth/ResponsiveLayoutTest.php`
Expected: semua PASS. Perhatikan `assertDontSee('>Tidak hadir</td>', false)`: label HP tidak boleh membuat string itu muncul untuk kru yang bukan Tidak hadir.

- [ ] **Step 4: Cek di HP**

`check('/hr/sesi/16/absensi', ['main select', 'main input[name=note]', 'main button[type=submit]'])` → `doc: 390` dan semua `inView`. Di screenshot, nama tidak patah lebih dari 2 baris.

- [ ] **Step 5: Commit**

```bash
git add resources/views/auth/hr/absensi/session.blade.php
git commit -m "feat: absensi sesi rapi di hp"
```

### Task 5: "Sesi saya" kru, dengan tombol Check-in selebar layar

**Files:**
- Modify: `resources/views/auth/crew/sesi/table.blade.php`
- Test: `tests/Feature/auth/crew/CheckInTest.php`, `tests/Feature/auth/ResponsiveLayoutTest.php`

**Interfaces:**
- Consumes: resep baris bertumpuk. Dipakai oleh `/announcer/sesi` dan `/so/sesi` lewat `index.blade.php`, yang tidak diubah.

```
┌──────────────────────────────┐
│ MOVIE HUNTER                 │
│ Jum, 23 Okt 2026  Hari ini   │
│ Crewcall 10:00  Tapping 10:30–12:00
│ Belum                        │
│ [         Check-in         ] │  ← hanya saat jendela terbuka
└──────────────────────────────┘
Ditunda:  MOVIE HUNTER / Menunggu jadwal baru  Ditunda
```

- [ ] **Step 1: Baris sesi biasa.**
  - Pakai resep baris bertumpuk dengan `max-md:grid-cols-2`.
  - Sel program dan sel tanggal: masing-masing `max-md:col-span-2`. Crewcall dan Tapping mengisi satu baris (kolom 1 dan 2).
  - Sel tanggal: pita oranye `before:` "Hari ini" dipindah ke sisi kiri baris di HP. Caranya: baris diberi `max-md:relative`, dan pita di sel tanggal hanya aktif `md:` (`md:before:absolute …`). Baris hari ini di HP diberi `max-md:before:absolute max-md:before:inset-y-0 max-md:before:left-0 max-md:before:w-1 max-md:before:bg-signal`.
  - Label HP: `Crewcall`, `Tapping`. Sel kehadiran: `max-md:col-span-2`.
  - Sel aksi: `max-md:col-span-2 max-md:pt-1`. Kalau tidak ada tombol, sel ini `max-md:hidden` (pakai kondisi yang sama dengan `@if` tombol).
  - Form tombol: `max-md:justify-stretch`. Tombol: `max-md:w-full max-md:min-h-11`.
- [ ] **Step 2: Baris ditunda dan baris kosong.**
  - Baris ditunda: resep yang sama, `max-md:grid-cols-2`. Sel program `max-md:col-span-2`. Sel `colspan=3` ("Menunggu jadwal baru") di kolom 1, sel "Ditunda" di kolom 2. Sel aksi kosong `max-md:hidden`.
  - Baris kosong: resep `@empty`.
- [ ] **Step 3: Jalankan tes**

Run: `php artisan test --compact tests/Feature/auth/crew/CheckInTest.php tests/Feature/auth/ResponsiveLayoutTest.php`
Expected: semua PASS.

- [ ] **Step 4: Cek di HP** (login kru, lalu langkah 3 di "Cara cek di HP")

`check('/announcer/sesi', ['main form button'])` → `doc: 390`, tombol Check-in `inView: true`, dan selebar kotak. Lihat juga riwayat dan pesan "Belum ada sesi mendatang." di tengah layar setelah salinan disegarkan ulang (`cp` lagi).

- [ ] **Step 5: Commit**

```bash
git add resources/views/auth/crew/sesi/table.blade.php
git commit -m "feat: sesi saya rapi di hp dengan tombol check-in penuh"
```

### Task 6: Rekap matriks (Per orang dan satu program)

**Files:**
- Modify: `resources/views/auth/hr/absensi/people.blade.php:26,38,62`
- Modify: `resources/views/auth/hr/absensi/program.blade.php:26,39,56`
- Test: `tests/Feature/auth/hr/AbsensiTest.php`, `tests/Feature/auth/ResponsiveLayoutTest.php`

**Interfaces:**
- Consumes: tidak ada. Tabel tetap tabel, termasuk di HP.

- [ ] **Step 1: Dua perubahan, sama di kedua view.**
  - Pembungkus tombol Terapkan: `pt-7` → `sm:pt-7`. Di HP tombol turun ke baris sendiri tanpa jarak kosong.
  - Kolom nama yang menempel (`th` header dan `th scope=row`): tambahkan `max-md:max-w-36 max-md:whitespace-normal`. Nama panjang patah jadi 2 baris dan tidak menutup lebih dari ±40% layar. Header kolom nama juga diberi `max-md:max-w-36`.
- [ ] **Step 2: Jalankan tes**

Run: `php artisan test --compact tests/Feature/auth/hr/AbsensiTest.php tests/Feature/auth/ResponsiveLayoutTest.php`
Expected: semua PASS.

- [ ] **Step 3: Cek di HP dan tablet**
  - `check('/hr/absensi/orang?from=2026-10-01&to=2026-10-31')` dan `check('/hr/absensi/program/1')` → `doc: 390`. Lebar `th[scope=row]` ≤ 160px.
  - Lebar 800: `check('/hr/sesi?waktu=riwayat', [], 800)`, `check('/hr/sesi/16/absensi', [], 800)`, dan `check('/announcer/sesi', [], 800)` (login kru) → `doc: 800`, dan tabel kembali tampil sebagai tabel.

- [ ] **Step 4: Commit, lalu minta user menjalankan suite penuh**

```bash
git add resources/views/auth/hr/absensi/people.blade.php resources/views/auth/hr/absensi/program.blade.php
git commit -m "fix: rekap absensi matriks lebih ringkas di hp"
```

Setelah commit, minta user menjalankan `php artisan test --compact`. Hentikan server `php -S` port 8123, lalu hapus `ui-check.sqlite`.
