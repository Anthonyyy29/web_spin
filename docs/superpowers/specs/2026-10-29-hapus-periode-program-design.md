# Hapus Periode Program — Design

Tanggal: 2026-10-29 · Disetujui di chat oleh user.

## Tujuan

Fitur periode program (`period_start`, `period_end`) dihapus seluruhnya tanpa pengganti. Bersamanya, otomatisasi pembuatan sesi rutin (`Program::syncSessions`) ikut hilang karena periode adalah satu-satunya rentang tanggalnya. Program menjadi **template** (hari siaran, jam, kru) yang hanya dipakai sebagai isian awal saat HR menambah sesi.

## Keputusan user

- Periode dihapus total, tidak diganti apa pun.
- Semua sesi dibuat manual lewat "Tambah sesi" (default dari program + tulisan pengingat yang sudah ada).
- Mengubah program **tidak mengubah sesi apa pun**.
- Kolom `is_customized` dihapus.
- Kolom `regular_date` dipertahankan dengan arti baru: **tanggal semula** sesi (diisi tanggal sesi saat dibuat), supaya sesi yang ditunda tetap menampilkan "semula Jum, 13 Nov".
- `tests/Feature/Models/ProgramSyncSessionsTest.php` boleh dihapus.

## Asumsi (tidak dibantah user)

- Hapus program tetap ikut menghapus semua sesinya (cascade sekarang), konfirmasi "beserta seluruh sesinya" tetap.
- Sesi yang sudah ada di database tetap ada dan tetap dipakai apa adanya.

## 1. Database

Satu migration baru `remove_period_and_session_customization` (dibuat dengan `php artisan make:migration`):

**up()**
1. `programs`: drop `period_start`, `period_end`.
2. `broadcast_sessions`: isi `regular_date` yang `NULL` dengan `COALESCE(session_date, DATE(created_at))` (pola yang sama dengan `2026_10_24_145406_make_regular_date_nullable_on_broadcast_sessions_table.php`), lalu ubah `regular_date` jadi `NOT NULL`.
3. `broadcast_sessions`: drop `is_customized`.

**down()**
1. `broadcast_sessions`: tambah `is_customized` boolean default `false`; ubah `regular_date` jadi nullable.
2. `programs`: tambah `period_start`, `period_end` sebagai date **nullable** (nilai lama tidak bisa dikembalikan).

Konsekuensi: nilai periode di database (termasuk database dev user) hilang permanen saat migrate.

## 2. Logika

**`app/Models/Program.php`**
- Hapus `period_start`, `period_end` dari `#[Fillable]` dan `casts()`.
- Hapus `syncSessions()`, `matchingDates()`, `checkInHasOpened()` beserta import yang jadi tidak terpakai.
- `nextOpenBroadcastDate(CarbonInterface $from): ?CarbonImmutable` → ganti jadi `nextOpenBroadcastDate(CarbonInterface $from): CarbonImmutable` (tidak pernah null lagi): hari pertama ≥ awal hari `$from` yang ISO weekday-nya `day_of_week` dan belum punya sesi program ini dengan `session_date` itu dan status ≠ `Cancelled`. Satu query untuk semua `session_date` mendatang program ini (status ≠ Cancelled), lalu maju per minggu sampai ketemu tanggal kosong — selalu berhenti karena jumlah sesi terbatas.

**`app/Http/Controllers/auth/hr/ProgramController.php`**
- `syncCrewAndSessions()` → `syncCrew()`: hanya `crew()->sync(...)`, tanpa menyentuh sesi. PHPDoc disesuaikan.

**`app/Http/Requests/auth/hr/ProgramRequest.php`**
- Hapus rule, attribute, message, dan field `period_start`/`period_end`.

**`app/Http/Controllers/auth/hr/SesiController.php`**
- `store()`: `'regular_date' => <session_date tervalidasi>` (bukan `null`).
- Hapus semua penulisan `is_customized`.
- Urutan kotak "Perlu dijadwalkan ulang" tetap `orderBy('regular_date')`.

**`app/Http/Requests/auth/hr/SesiRequest.php`**
- `sessionAttributes()` tidak lagi mengembalikan `is_customized`.

**`app/Models/BroadcastSession.php`**, **`database/factories/BroadcastSessionFactory.php`**
- Hapus `is_customized` dari Fillable, casts, dan definisi factory. Factory `regular_date` tetap sama dengan `session_date` default-nya.

**`database/factories/ProgramFactory.php`**, **`database/seeders/Gen7ProgramSeeder.php`**
- Hapus `period_start`/`period_end` dan konstanta `PERIOD_START`/`PERIOD_END`.

## 3. Tampilan dan copy

| Tempat | Perubahan |
|---|---|
| `hr/program/_form.blade.php` | Hapus isian "Periode mulai" dan "Periode selesai". |
| `hr/program/index.blade.php` | Hapus kolom "Periode". Deskripsi jadi: `Template program siaran: hari, jam, dan kru. Dipakai sebagai isian awal saat menambah sesi.` |
| `hr/absensi/index.blade.php` | Hapus kolom "Periode". |
| `hr/absensi/program.blade.php` | Hapus label `tambahan` di header kolom tanggal. |
| `hr/sesi/index.blade.php` | Deskripsi jadi: `Sesi siaran per program. Tambah sesi, ubah jadwal atau kru, tunda, atau batalkan per sesi.` Kotak "Perlu dijadwalkan ulang": selalu `semula {D, d M Y}` dari `regular_date` (label `sesi tambahan` dihapus, termasuk di teks konfirmasi Batalkan → `Batalkan sesi {program} yang semula {d/m/Y}?`). |
| `hr/sesi/create.blade.php` | Deskripsi jadi: `Jam dan kru diisi dari program. Kalau ada yang kamu ubah, muncul pengingat di bawah isiannya.` |
| `hr/sesi/edit.blade.php` | Subjudul: nama program, lalu `(semula {D, d M Y})` **hanya** kalau sesi ditunda atau `session_date` ≠ `regular_date`. |

## 4. Tes

- **Hapus:** `tests/Feature/Models/ProgramSyncSessionsTest.php` (disetujui user).
- **Tulis ulang di `tests/Feature/auth/hr/ProgramTest.php`:**
  - `lets hr create a program and generates its sessions` → `lets hr create a program without creating sessions` (assert `BroadcastSession::count() === 0`).
  - `updates a program and resyncs upcoming sessions` → `updates a program without touching its sessions` (sesi yang ada tetap dengan jam/kru lamanya).
  - `validates times, period and crew roles with indonesian messages` → hilangkan bagian periode.
  - Semua payload program tanpa `period_start`/`period_end`.
- **`tests/Feature/auth/hr/SesiTest.php`:** tes yang memakai `is_customized`, `regular_date => null`, atau `assertSee('sesi tambahan')` disesuaikan; tambah tes bahwa `sesi.store` mengisi `regular_date` = `session_date`; tes kotak ditunda menampilkan `semula …`; tes subjudul edit tidak menampilkan `semula` untuk sesi yang tanggalnya tidak berubah. Tes `leaves the add session date empty when the program period has no open broadcast day` dihapus (tidak ada periode lagi; tanggal default selalu terisi), dan tes tanggal default tidak lagi mengatur `period_end`.
- **`tests/Feature/Models/ProgramNextOpenBroadcastDateTest.php`:** ganti tes berbasis periode dengan: hari berikutnya yang kosong, hari ini dihitung kalau kosong, cancelled & program lain tidak menghalangi, melompati beberapa minggu yang terisi berturut-turut.
- **`tests/Feature/Models/AbsensiSchemaTest.php`:** hapus cek cast `is_customized`; tambah tes `Schema::hasColumn` bahwa `programs.period_start`, `programs.period_end`, `broadcast_sessions.is_customized` tidak ada.
- Tes lain yang mengisi `period_*` atau `is_customized` (mis. `AbsensiTest`, `RoleAccessTest`, `DatabaseSeederTest`) disesuaikan seperlunya.

## Di luar cakupan

- Pengganti otomatisasi (scheduler, "N minggu ke depan") — tidak dibuat.
- Membuat banyak sesi sekaligus — tidak dibuat.
- Mengubah alur Tunda/Batalkan/Absensi selain copy di atas.

## Aturan proyek yang berlaku

- Buat file dengan `php artisan make:...`, jalankan `vendor/bin/pint --dirty --format agent`, tes Pest tersempit, lalu minta user menjalankan `php artisan test --compact`.
- Commit atas nama user, tanpa `Co-Authored-By`/sebutan AI; `docs/superpowers` tidak di-commit tanpa izin.
