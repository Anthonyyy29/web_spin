# Ekspor Absensi Excel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti tiga ekspor absensi HR dari CSV ke .xlsx berwarna, dan ubah rekap Per orang menjadi matriks orang × sesi (halaman + Excel).

**Architecture:** Satu kelas `App\Support\AttendanceSpreadsheet` membungkus openspout dan menulis satu tabel (judul, header, isi, gabungan sel header, freeze, warna status) ke `StreamedResponse`. `AbsensiController` menyiapkan baris; logika baris matriks dipakai bersama oleh satu program dan per orang lewat satu helper privat.

**Tech Stack:** Laravel 13, PHP 8.5, Blade + Tailwind v4, Pest 5, openspout/openspout ^5.13.

**Spec:** `docs/superpowers/specs/2026-10-29-ekspor-absensi-excel-design.md`

## Global Constraints

- Dependensi baru hanya `openspout/openspout:^5.13` (disetujui user). Jangan tambah dependensi lain.
- Warna status (latar/tulisan): Hadir `DCFCE7`/`166534`; Terlambat `FEF3C7`/`92400E`; Izin & Sakit `DBEAFE`/`1E40AF`; Tidak hadir `FEE2E2`/`B42318` tebal; Belum `F4F5F7`/`5F6875`. Header tebal, latar `E2E5E9`. Judul tebal ukuran 14.
- Urutan total di mana pun: `Hadir, Terlambat, Izin, Sakit, Tidak hadir`.
- Bukan kru sesi itu = `–` (en dash) di Excel; posisi kosong = `-`.
- String dari pengguna tidak boleh menjadi rumus: tulis semua string dengan `OpenSpout\Common\Entity\Cell\StringCell`, jangan `Cell::fromValue()`.
- Label tombol: `Ekspor Excel`. Nama route dan URL tidak berubah. Nama file sama, ekstensi `.xlsx`. Content-Type `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`.
- Tes jalan di SQLite in-memory (phpunit.xml). Pengecekan manual hanya di SQLite cermin, **tidak pernah** ke MySQL dev.
- Setelah ubah PHP: `vendor/bin/pint --dirty --format agent`. Tes tersempit dengan `php artisan test --compact <file>`.
- Commit atas nama user (git config yang ada), **tanpa** `Co-Authored-By` atau sebutan AI. Jangan commit `docs/superpowers`.
- Komentar dan copy UI berbahasa Indonesia; PHPDoc mengikuti gaya Inggris yang ada di controller.

## Review Focus

1. Username/catatan berawalan `=` (mis. `=1+1`) harus tetap teks di Excel, bukan rumus → tes di Task 1.
2. Rentang Per orang tanpa sesi: halaman dan Excel tetap jalan (hanya Username, Role, total; tanpa gabungan tanggal) → tes di Task 3.
3. Dua sesi dari program berbeda di tanggal yang sama: satu sel tanggal digabung, urut `crewcall_time` → tes di Task 3.
4. Sesi yang check-in-nya belum ditutup tampil `Belum` (abu) dan tidak dihitung di total → tes di Task 2 dan 3.
5. User ber-role announcer/SO tanpa sesi di rentang itu tetap muncul dengan `–` dan total 0 → tes di Task 3.

---

### Task 1: `AttendanceSpreadsheet` + dependensi openspout

**Files:**
- Modify: `composer.json`, `composer.lock` (via `composer require openspout/openspout:^5.13`)
- Create: `app/Support/AttendanceSpreadsheet.php` (via `php artisan make:class Support/AttendanceSpreadsheet --no-interaction`)
- Modify: `tests/Pest.php` (tambah fungsi pembaca)
- Test: `tests/Feature/Support/AttendanceSpreadsheetTest.php` (via `php artisan make:test --pest Support/AttendanceSpreadsheetTest --no-interaction`)

**Interfaces:**
- Produces:
  - `App\Support\AttendanceSpreadsheet::download(string $filename, string $title, array $headerRows, iterable $bodyRows, array $headerMerges = [], int $frozenColumns = 0): StreamedResponse`
    - `$headerRows`: `list<list<string|null>>`; null = sel header kosong (tetap bergaya header).
    - `$bodyRows`: `iterable<list<string|int|AttendanceStatus|null>>`; `AttendanceStatus` → label + gaya status; `string` → `StringCell`; `int` → `NumericCell`; `null` → `EmptyCell`.
    - `$headerMerges`: `list<array{0: int, 1: int, 2: int, 3: int}>` = `[kolomAwal, barisAwal, kolomAkhir, barisAkhir]`, kolom dan baris **0-based relatif ke header** (baris header pertama = 0). Kelas menggesernya ke baris sheet 3.
    - `$frozenColumns`: jumlah kolom kiri yang dibekukan (0 = tidak ada).
  - `App\Support\AttendanceSpreadsheet::statusStyle(AttendanceStatus $status): OpenSpout\Common\Entity\Style\Style`
  - Fungsi tes global di `tests/Pest.php`: `readSpreadsheet(TestResponse $response): array{rows: list<list<mixed>>, merges: list<string>, sheetXml: string, stylesXml: string}`; `rows` memuat baris kosong (indeks 0 = judul, 1 = kosong, 2 = header pertama).

- [ ] **Step 1: Pasang dependensi**

Run: `composer require openspout/openspout:^5.13`
Expected: `composer.json` memuat `"openspout/openspout": "^5.13"`.

- [ ] **Step 2: Tambah `readSpreadsheet()` di `tests/Pest.php`**

Tulis `$response->streamedContent()` ke file sementara `.xlsx`, buka dengan `new OpenSpout\Reader\XLSX\Reader(new OpenSpout\Reader\XLSX\Options(SHOULD_PRESERVE_EMPTY_ROWS: true, SHOULD_LOAD_MERGE_CELLS: true))`, kumpulkan `$row->toArray()` sheet pertama dan `$sheet->getMergeCells()`; baca `xl/worksheets/sheet1.xml` dan `xl/styles.xml` dengan `ZipArchive`; hapus file sementara.

- [ ] **Step 3: Tulis tes yang gagal** di `tests/Feature/Support/AttendanceSpreadsheetTest.php`

```php
use App\Enums\AttendanceStatus;
use App\Support\AttendanceSpreadsheet;
use Illuminate\Testing\TestResponse;

it('writes a titled table with merged header cells and frozen panes', function () {
    $response = TestResponse::fromBaseResponse(AttendanceSpreadsheet::download(
        'contoh.xlsx',
        'Judul contoh',
        [['Username', 'Role', '12/10/2026', null, 'Hadir'], [null, null, 'Pagi Ceria', 'Malam Minggu', null]],
        [['ani', 'Announcer', AttendanceStatus::Hadir, '–', 2]],
        [[0, 0, 0, 1], [2, 0, 3, 0]],
        frozenColumns: 2,
    ));

    $sheet = readSpreadsheet($response);

    expect($response->headers->get('Content-Type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($response->headers->get('Content-Disposition'))->toContain('contoh.xlsx')
        ->and($sheet['rows'][0][0])->toBe('Judul contoh')
        ->and($sheet['rows'][1])->toBe([])
        ->and($sheet['rows'][2])->toBe(['Username', 'Role', '12/10/2026', '', 'Hadir'])
        ->and($sheet['rows'][3])->toBe(['', '', 'Pagi Ceria', 'Malam Minggu', ''])
        ->and($sheet['rows'][4])->toBe(['ani', 'Announcer', 'Hadir', '–', 2])
        ->and($sheet['merges'])->toEqualCanonicalizing(['A3:A4', 'C3:D3'])
        ->and($sheet['sheetXml'])->toContain('xSplit="2"')->toContain('ySplit="4"')
        ->and($sheet['stylesXml'])->toContain('DCFCE7')->toContain('E2E5E9');
});

it('freezes only the header rows when no column is frozen', function () {
    $sheet = readSpreadsheet(TestResponse::fromBaseResponse(
        AttendanceSpreadsheet::download('a.xlsx', 'Judul', [['Status']], [[AttendanceStatus::Belum]]),
    ));

    expect($sheet['sheetXml'])->toContain('ySplit="3"')->not->toContain('xSplit=');
});

it('keeps user text that looks like a formula as plain text', function () {
    $sheet = readSpreadsheet(TestResponse::fromBaseResponse(
        AttendanceSpreadsheet::download('a.xlsx', '=1+1', [['Catatan']], [['=HYPERLINK("http://x","klik")']]),
    ));

    expect($sheet['sheetXml'])->not->toContain('<f>')
        ->and($sheet['rows'][0][0])->toBe('=1+1')
        ->and($sheet['rows'][3][0])->toBe('=HYPERLINK("http://x","klik")');
});

it('colours each attendance status', function (AttendanceStatus $status, string $background, string $font, bool $bold) {
    $style = AttendanceSpreadsheet::statusStyle($status);

    expect($style->backgroundColor)->toBe($background)
        ->and($style->fontColor)->toBe($font)
        ->and($style->fontBold)->toBe($bold);
})->with([
    [AttendanceStatus::Hadir, 'DCFCE7', '166534', false],
    [AttendanceStatus::Terlambat, 'FEF3C7', '92400E', false],
    [AttendanceStatus::Izin, 'DBEAFE', '1E40AF', false],
    [AttendanceStatus::Sakit, 'DBEAFE', '1E40AF', false],
    [AttendanceStatus::TidakHadir, 'FEE2E2', 'B42318', true],
    [AttendanceStatus::Belum, 'F4F5F7', '5F6875', false],
]);
```

- [ ] **Step 4: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/Support/AttendanceSpreadsheetTest.php`
Expected: FAIL, `AttendanceSpreadsheet::download` belum ada.

- [ ] **Step 5: Implementasikan `AttendanceSpreadsheet`** (final class, method static)

- `download()` mengembalikan `response()->streamDownload(...)` dengan Content-Type di Global Constraints. Di dalam callback: `new OpenSpout\Writer\XLSX\Writer($options)` → `openToFile('php://output')`.
- Sebelum menulis baris, siapkan `$options`:
  - Lebar kolom `22` untuk kolom 1..`$frozenColumns`, `14` untuk sisanya (`setColumnWidth`, kolom 1-based). Jumlah kolom = `count($headerRows[0])`.
  - Setiap merge `[c1, r1, c2, r2]` → `$options->mergeCells(c1, r1 + 3, c2, r2 + 3)`.
- Freeze: `$writer->getCurrentSheet()->setSheetView(...)`. `withFreezeRow(3 + count($headerRows))`. Kalau `$frozenColumns > 0`, tambahkan `withFreezeColumn(<huruf kolom ke-$frozenColumns + 1>)`, misalnya 2 → `'C'`.
- Isi baris:
  - Baris 1: judul, tebal, ukuran 14.
  - Baris 2: `new Row([])`.
  - Lalu baris header, lalu baris isi.
- `statusStyle()` mengembalikan `new Style(...)` dengan nilai dari Global Constraints. Izin dan Sakit memakai gaya yang sama.

- [ ] **Step 6: Jalankan, pastikan lulus**

Run: `php artisan test --compact tests/Feature/Support/AttendanceSpreadsheetTest.php`
Expected: PASS (9 tes).

- [ ] **Step 7: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add composer.json composer.lock app/Support/AttendanceSpreadsheet.php tests/Pest.php tests/Feature/Support/AttendanceSpreadsheetTest.php
git commit -m "feat: tambah penulis excel absensi dengan openspout"
```

---

### Task 2: Ekspor satu program dan satu sesi ke Excel + urutan total

**Files:**
- Modify: `app/Http/Controllers/auth/hr/AbsensiController.php` — `programExport` (84-107), `sessionExport` (148-171), `emptyTotals` (357-366); hapus `csvDownload` dan `writeCsvRow` (464-486); tambah helper judul rentang
- Modify: `resources/views/auth/hr/absensi/program.blade.php:31`, `resources/views/auth/hr/absensi/session.blade.php:20` (label `Ekspor Excel`)
- Test: `tests/Feature/auth/hr/AbsensiTest.php`

**Interfaces:**
- Consumes: `AttendanceSpreadsheet::download(...)` dan `readSpreadsheet()` dari Task 1.
- Produces:
  - `emptyTotals()` dengan urutan key `hadir, terlambat, izin, sakit, tidak_hadir`. Task 3 mengandalkan `array_values($row['totals'])` mengikuti urutan ini.
  - `private function rangeLabel(?string $from, ?string $to): string` mengembalikan `semua tanggal`, `1 Okt 2026 – 20 Okt 2026`, `mulai 1 Okt 2026`, atau `sampai 20 Okt 2026`. Formatnya `Carbon::parse(...)->locale('id')->translatedFormat('j M Y')`. Task 3 memakainya untuk judul Per orang.

- [ ] **Step 1: Ubah tes jadi gagal** di `tests/Feature/auth/hr/AbsensiTest.php`

- Hapus helper `$this->streamed` di `beforeEach`.
- Di `lists programs with their attendance totals…` dan `shows a crew by date matrix…`, urutan key array totals jadi `['hadir', 'terlambat', 'izin', 'sakit', 'tidak_hadir']`, angkanya tetap.
- Ganti `exports the program matrix as csv` → `exports the program matrix as excel`:

```php
$response = $this->actingAs($this->hr)
    ->get(route('absensi.program.export', $this->program))
    ->assertOk()
    ->assertDownload('absensi-program-pagi-ceria.xlsx');

$rows = readSpreadsheet($response)['rows'];

expect($rows[0][0])->toBe('Absensi Pagi Ceria · semua tanggal')
    ->and($rows[2])->toBe(['Username', 'Posisi', '05/10/2026', '12/10/2026', '15/10/2026', '26/10/2026', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tidak hadir'])
    ->and($rows[3])->toBe(['ani', 'Announcer', 'Hadir', 'Terlambat', 'Tidak hadir', 'Belum', 1, 1, 0, 0, 1])
    ->and($rows[4])->toBe(['cici', 'SO', 'Sakit', 'Tidak hadir', '–', 'Belum', 0, 0, 0, 1, 1])
    ->and($rows[5])->toBe(['fani', '-', 'Hadir', '–', '–', '–', 1, 0, 0, 0, 0])
    ->and($rows)->toHaveCount(6);

$filtered = $this->actingAs($this->hr)
    ->get(route('absensi.program.export', [$this->program, 'from' => '2026-10-10', 'to' => '2026-10-20']))
    ->assertDownload('absensi-program-pagi-ceria-2026-10-10-2026-10-20.xlsx');

expect(readSpreadsheet($filtered)['rows'][0][0])->toBe('Absensi Pagi Ceria · 10 Okt 2026 – 20 Okt 2026');
```

- Ganti `exports a session as csv` → `exports a session as excel`:
  - nama unduhan `absensi-pagi-ceria-2026-10-26.xlsx`
  - `$rows[0][0]` = `'Absensi Pagi Ceria · Senin, 26 Okt 2026'`
  - `$rows[2]` = header yang sama seperti sebelumnya
  - `$rows[3]` = `['Pagi Ceria', '2026-10-26', '07:30', 'ani', 'Announcer', 'Terlambat', '07:40', 'Macet, banjir']`
  - `$rows[4]` = `['Pagi Ceria', '2026-10-26', '07:30', 'cici', 'SO', 'Tidak hadir', '', '']`
  - `toHaveCount(5)`
- Ganti `exports a postponed session as csv without a date` → `…as excel…`:
  - nama unduhan `absensi-pagi-ceria-ditunda.xlsx`
  - `$rows[0][0]` = `'Absensi Pagi Ceria · ditunda'`
  - `$rows[3]` = `['Pagi Ceria', '', '', 'ani', 'Announcer', 'Belum', '', '']`
- Tambah di `exports the program matrix as excel`: `->assertSee('Ekspor Excel')` pada `get(route('absensi.program', $this->program))`. Tambah juga pada halaman sesi di `exports a session as excel`.
- `exports the per person summary as csv` → `exports the per person summary as excel` (bentuk lama, sementara sampai Task 3): unduhan `rekap-absensi-2026-10-01-2026-10-26.xlsx`; `$rows[2]` = `['Username', 'Role', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tidak hadir']`; `$rows[3]` = `['ani', 'Announcer', 0, 1, 0, 0, 0]`; `toHaveCount(4)`.

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/hr/AbsensiTest.php`
Expected: FAIL di tes ekspor (masih CSV) dan tes urutan totals.

- [ ] **Step 3: Implementasikan**

- `emptyTotals()`: ubah urutan key sesuai Interfaces.
- `programExport()`:
  - panggil `AttendanceSpreadsheet::download($filename, 'Absensi '.$program->name.' · '.$this->rangeLabel($from, $to), [header], rows, frozenColumns: 2)`
  - sel `null` diisi `'–'`, status diteruskan sebagai enum, total diambil dengan `array_values($row['totals'])`
- `export()` (sementara, diganti di Task 3): `AttendanceSpreadsheet::download("rekap-absensi-{$from}-{$to}.xlsx", 'Rekap absensi per orang · '.$this->rangeLabel($from, $to), [header lama dengan urutan total baru], baris dari summaryRows(), frozenColumns: 2)`.
- `sessionExport()`:
  - judul `'Absensi '.$program.' · '.($sesi->session_date?->locale('id')->translatedFormat('l, j M Y') ?? 'ditunda')`
  - kolom Status berisi `$member['status']` (enum), kolom lain sama seperti sekarang, tanpa freeze kolom
- Hapus `csvDownload()` dan `writeCsvRow()`. Pastikan import yang tidak terpakai tidak tertinggal.
- Ganti teks dua tombol di Blade jadi `Ekspor Excel`.

- [ ] **Step 4: Jalankan, pastikan lulus**

Run: `php artisan test --compact tests/Feature/auth/hr/AbsensiTest.php`
Expected: PASS semua.

- [ ] **Step 5: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/auth/hr/AbsensiController.php resources/views/auth/hr/absensi/program.blade.php resources/views/auth/hr/absensi/session.blade.php tests/Feature/auth/hr/AbsensiTest.php
git commit -m "feat: ekspor absensi program dan sesi ke excel"
```

---

### Task 3: Rekap Per orang menjadi matriks orang × sesi (halaman + Excel)

**Files:**
- Modify: `app/Http/Controllers/auth/hr/AbsensiController.php`: `people` (55-64), `export` (112-131), `programMatrix` (293-350); hapus `summaryRows` (386-430)
- Create: `resources/views/auth/hr/absensi/_status-cell.blade.php`
- Modify: `resources/views/auth/hr/absensi/program.blade.php`: sel status memakai partial
- Modify: `resources/views/auth/hr/absensi/people.blade.php`
- Test: `tests/Feature/auth/hr/AbsensiTest.php`

**Interfaces:**
- Consumes: `AttendanceSpreadsheet::download` (Task 1), `emptyTotals()` urutan baru dan `rangeLabel()` (Task 2).
- Produces:
  - `private function matrixRows(Collection $sessions, Collection $members, CarbonInterface $now): Collection`
    - `$members` berisi `Collection<int, array{user: User, position: string|null}>`.
    - Mengembalikan `Collection<int, array{user: User, position: string|null, cells: array<int, AttendanceStatus|null>, totals: array<string, int>}>`, diurutkan `positionOrder(position)` lalu `username`.
    - Sel berisi `null` kalau orang itu bukan kru sesi tersebut dan tidak punya baris absensi di sana.
    - Total hanya menghitung sesi yang `isCheckInClosed($now)`.
    - `programMatrix()` memakai helper ini.
  - `private function peopleMatrix(string $from, string $to): array{sessions: Collection<int, BroadcastSession>, rows: Collection<…sama…>}`
    - Sesi: `BroadcastSession` berstatus `Scheduled`, `session_date` di dalam rentang, eager load `program`, `crew`, `attendances`, urut `session_date`, lalu `crewcall_time`.
    - Anggota: semua user dengan role announcer/SO, `position = $user->crewPosition()->value`.
  - Data view `auth.hr.absensi.people`: `sessions`, `rows`, `from`, `to`.
  - Partial `_status-cell` menerima param `$status` (`AttendanceStatus|null`). Markup `<td>` dipindah apa adanya dari `program.blade.php:51-61`.

- [ ] **Step 1: Ubah tes jadi gagal**

- `summarises attendance per person for a date range`: assert view data berbentuk matriks.

```php
expect(collect($response->viewData('sessions'))->pluck('session_date')->map->toDateString()->all())->toBe(['2026-10-05', '2026-10-12', '2026-10-26']);

$rows = collect($response->viewData('rows'))->keyBy(fn ($row) => $row['user']->username);
$cells = fn (string $name): array => array_map(fn (?AttendanceStatus $status) => $status?->value, array_values($rows[$name]['cells']));

expect($rows->keys()->all())->toBe(['ani', 'budi', 'dodi', 'eko', 'fani', 'cici'])
    ->and($cells('ani'))->toBe(['hadir', 'terlambat', 'belum'])
    ->and($cells('budi'))->toBe(['terlambat', 'izin', 'belum'])
    ->and($cells('cici'))->toBe(['sakit', 'tidak_hadir', 'belum'])
    ->and($cells('dodi'))->toBe([null, null, null])
    ->and($cells('fani'))->toBe(['hadir', null, null])
    ->and($rows['ani']['totals'])->toBe(['hadir' => 1, 'terlambat' => 1, 'izin' => 0, 'sakit' => 0, 'tidak_hadir' => 0])
    ->and($rows['budi']['totals'])->toBe(['hadir' => 0, 'terlambat' => 1, 'izin' => 1, 'sakit' => 0, 'tidak_hadir' => 0])
    ->and($rows['cici']['totals'])->toBe(['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 1, 'tidak_hadir' => 1])
    ->and($rows['eko']['totals'])->toBe(['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'tidak_hadir' => 0]);
```

- Tes baru `groups same-day sessions of different programs under one date`:
  - Data:
    - `ani` adalah kru sesi Pagi Ceria `2026-10-12` (crewcall default `07:30`) dan check-in `07:20`.
    - `ani` juga kru sesi program baru `Malam Minggu` `2026-10-12` dengan `crewcall_time` `19:00:00`, `tapping_start` `19:30:00`, `tapping_end` `20:30:00`, tanpa check-in.
  - Halaman `absensi.people` (from `2026-10-01`, to `2026-10-26`):
    - `assertSeeInOrder(['12 Okt', 'Pagi Ceria', 'Malam Minggu'])`
    - `assertSee('colspan="2"', false)`
    - `assertSee(route('absensi.session', $pagiSession))`
  - Excel `absensi.export` dengan rentang yang sama, cek hasil `readSpreadsheet`:
    - `rows[0][0]` = `'Rekap absensi per orang · 1 Okt 2026 – 26 Okt 2026'`
    - `rows[2]` = `['Username', 'Role', '12/10/2026', '', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tidak hadir']`
    - `rows[3]` = `['', '', 'Pagi Ceria', 'Malam Minggu', '', '', '', '', '']`
    - `rows[4]` = `['ani', 'Announcer', 'Hadir', 'Tidak hadir', 1, 0, 0, 0, 1]`
    - `merges` toEqualCanonicalizing `['A3:A4', 'B3:B4', 'C3:D3', 'E3:E4', 'F3:F4', 'G3:G4', 'H3:H4', 'I3:I4']`
- Ganti `exports the per person summary as excel` (dari Task 2) → `exports the per person matrix as excel`:
  - Data: satu sesi `2026-10-12` Pagi Ceria, `ani` check-in `07:40`.
  - Nama unduhan `rekap-absensi-2026-10-01-2026-10-26.xlsx`.
  - `rows[2]` = `['Username', 'Role', '12/10/2026', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tidak hadir']`
  - `rows[3]` = `['', '', 'Pagi Ceria', '', '', '', '', '']`
  - `rows[4]` = `['ani', 'Announcer', 'Terlambat', 0, 1, 0, 0, 0]`
  - `rows` toHaveCount(5)
  - Halaman `absensi.people` → `assertSee('Ekspor Excel')`
- Tes baru `renders the per person recap without sessions in the range`:
  - Data: `ani` announcer, tanpa sesi.
  - Halaman (from/to `2026-10-01`/`2026-10-26`): assertOk, assertSee `ani`, assertSee `Belum ada sesi terjadwal di rentang tanggal ini.`
  - Excel:
    - `rows[2]` = `['Username', 'Role', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tidak hadir']`
    - `rows[4]` = `['ani', 'Announcer', 0, 0, 0, 0, 0]`
    - `merges` = 7 gabungan vertikal `A3:A4` … `G3:G4`

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/hr/AbsensiTest.php`
Expected: FAIL di empat tes di atas.

- [ ] **Step 3: Implementasikan controller**

- Ambil logika per user dari `programMatrix()` (baris 325-347) ke `matrixRows()`. `programMatrix()` cukup menyusun `$members` lalu memanggil `matrixRows()`.
- Tambah `peopleMatrix()`. `people()` meneruskan hasilnya ke view, ditambah `from` dan `to`.
- `export()`:
  - Kelompokkan sesi per `session_date->toDateString()`.
  - Baris header atas: `Username`, `Role`, lalu tanggal `d/m/Y` di sesi pertama tiap kelompok dan `null` di sisanya, lalu 5 label total.
  - Baris header bawah: `null`, `null`, nama program per sesi, lalu `null` ×5.
  - Gabungan sel:
    - kolom Username, Role, dan 5 total digabung vertikal `[c, 0, c, 1]`
    - tiap kelompok tanggal yang isinya lebih dari 1 sesi digabung horizontal `[cAwal, 0, cAkhir, 0]`
  - `frozenColumns: 2`.
  - Judul `'Rekap absensi per orang · '.$this->rangeLabel($from, $to)`.
- Hapus `summaryRows()`.

- [ ] **Step 4: Implementasikan view**

- Buat `_status-cell.blade.php`. Pakai di `program.blade.php` dan `people.blade.php`.
- Di `people.blade.php`:
  - Deskripsi:
    `Status tiap announcer dan SO per sesi dari semua program. Total di kanan hanya menghitung sesi yang check-in-nya sudah ditutup. Klik nama program untuk membuka absensi sesi itu.`
  - Header dua baris:
    - Baris atas: `th` Username (`rowspan="2"`, sticky kiri seperti `program.blade.php`), `th` Role (`rowspan="2"`), satu `th scope="colgroup"` per tanggal (`colspan` = jumlah sesi hari itu; label `translatedFormat('d M')`, locale `id`), lalu 5 `th` total (`rowspan="2"`, yang pertama `border-l`). Urutannya Hadir, Terlambat, Izin, Sakit, Tidak hadir.
    - Baris bawah: `th scope="col"` per sesi berisi tautan nama program ke `route('absensi.session', $session)`, dengan gaya tautan tanggal di `program.blade.php`.
  - Isi tabel:
    - Username jadi `th scope="row"` sticky.
    - Sel status lewat partial.
    - Total kanan sama dengan `program.blade.php`.
    - Kalau `rows` kosong: baris `colspan` = `sessions->count() + 7` berisi `Belum ada announcer atau SO.`
  - Di bawah tabel, kalau `sessions` kosong: `<p class="mt-4 text-sm text-muted">Belum ada sesi terjadwal di rentang tanggal ini.</p>`.
  - Label tombol jadi `Ekspor Excel`.

- [ ] **Step 5: Jalankan, pastikan lulus**

Run: `php artisan test --compact tests/Feature/auth/hr/AbsensiTest.php tests/Feature/Support/AttendanceSpreadsheetTest.php tests/Feature/auth/RoleAccessTest.php`
Expected: PASS semua.

- [ ] **Step 6: Cek tampilan di SQLite cermin**

Jalankan app pada database cermin, misalnya `DB_CONNECTION=sqlite DB_DATABASE=<scratchpad>/mirror.sqlite php artisan serve --port=8001`. Isi data yang memuat dua sesi dari program berbeda di hari yang sama. Lalu cek dua hal:
- Halaman `/absensi/orang` di lebar 1280px dan 390px: header tidak terpotong, dan kolom Username tetap terlihat (sticky) saat di-scroll ke kanan.
- Unduhan Excel bisa dibuka.
Jangan pernah menyentuh MySQL dev.

- [ ] **Step 7: Pint lalu commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/auth/hr/AbsensiController.php resources/views/auth/hr/absensi/_status-cell.blade.php resources/views/auth/hr/absensi/program.blade.php resources/views/auth/hr/absensi/people.blade.php tests/Feature/auth/hr/AbsensiTest.php
git commit -m "feat: rekap absensi per orang per sesi di halaman dan excel"
```
