# Ekspor Absensi ke Excel + Rekap Per Orang per Sesi — Design

Tanggal: 2026-10-29 · Bagian 1 (isi file Excel) disetujui di chat; user lalu meminta plan. Bagian 2–3 di bawah adalah keputusan Claude yang ditandai **(perlu dicek)**.

## Tujuan

1. Tiga ekspor absensi HR berubah dari CSV menjadi Excel (.xlsx): satu program, per orang, satu sesi.
2. Rekap **Per orang** (`/absensi/orang`) berubah dari total saja menjadi matriks orang × sesi (semua program) dengan total di kanan — di halaman web **dan** di Excel.
3. Halaman **Per program** (`/absensi`) tidak berubah dan tetap tanpa ekspor.

## Keputusan user

- Semua ekspor jadi Excel; nomor 1 (Per program) dibiarkan.
- Isi sel status: **kata lengkap + warna**.
- Rekap Per orang: **satu kolom per sesi, header 2 baris** — baris atas tanggal (digabung bila ada beberapa sesi di hari itu), baris bawah nama program.
- Tampilan per sesi dipakai di **halaman + Excel**.
- Library: **openspout/openspout** (v5, mendukung PHP 8.5).

## Asumsi (belum dibantah user)

- Tombol "Ekspor CSV" diganti "Ekspor Excel"; CSV tidak disediakan lagi. Nama route dan URL tetap.
- Per orang tetap memuat semua user ber-role announcer/SO (termasuk yang tidak bertugas di rentang itu: sel "–", total 0). Orang tanpa role announcer/SO tidak masuk, seperti sekarang.
- Per orang memuat sesi terjadwal bertanggal di rentang itu, termasuk yang check-in-nya belum ditutup (status "Belum"); total hanya dari sesi yang sudah ditutup — sama dengan matriks satu program.

## 1. Isi file Excel (disetujui)

Satu sheet per file:

1. Baris 1: judul (tebal, ukuran 14). Baris 2 kosong. Tabel mulai baris 3.
2. Header tebal dengan latar `E2E5E9`. Baris header dan kolom nama dibekukan (freeze).
3. Warna sel status (latar / tulisan):

| Status | Latar | Tulisan |
|---|---|---|
| Hadir | `DCFCE7` | `166534` |
| Terlambat | `FEF3C7` | `92400E` |
| Izin, Sakit | `DBEAFE` | `1E40AF` |
| Tidak hadir | `FEE2E2` | `B42318`, tebal |
| Belum | `F4F5F7` | `5F6875` |
| – (bukan kru sesi itu) | tanpa warna | – |

4. Urutan kolom total di semua rekap: `Hadir, Terlambat, Izin, Sakit, Tidak hadir`.
5. Nama file sama dengan sekarang, ekstensi `.xlsx`.
6. Teks dari pengguna (username, catatan, nama program) selalu ditulis sebagai teks, tidak pernah sebagai rumus.

Judul:

| Ekspor | Judul |
|---|---|
| Satu program | `Absensi {program} · {rentang}`; rentang = `semua tanggal`, `1 Okt 2026 – 20 Okt 2026`, `mulai 1 Okt 2026`, atau `sampai 20 Okt 2026` |
| Per orang | `Rekap absensi per orang · 1 Okt 2026 – 26 Okt 2026` |
| Satu sesi | `Absensi {program} · Senin, 26 Okt 2026`, atau `Absensi {program} · ditunda` |

Bentuk tabel:

- **Satu program:** header 1 baris `Username, Posisi, {tanggal d/m/Y …}, Hadir, Terlambat, Izin, Sakit, Tidak hadir`; bukan kru sesi itu = `–`; tanpa posisi = `-`. Freeze 2 kolom.
- **Per orang:** header 2 baris. Baris atas `Username, Role, {tanggal d/m/Y per kelompok tanggal}, Hadir … Tidak hadir`; baris bawah nama program per sesi. Username, Role, dan kelima total digabung vertikal 2 baris; tanggal digabung horizontal bila kelompoknya > 1 sesi. Freeze 2 kolom.
- **Satu sesi:** kolom tetap `Program, Tanggal, Crewcall, Username, Posisi, Status, Jam check-in, Catatan`; hanya sel Status yang berwarna. Tanpa freeze kolom.

## 2. Halaman Per orang (perlu dicek)

- Deskripsi: `Status tiap announcer dan SO per sesi dari semua program. Total di kanan hanya menghitung sesi yang check-in-nya sudah ditutup. Klik nama program untuk membuka absensi sesi itu.`
- Tabel: header 2 baris seperti Excel. Baris atas: Username dan Role (`rowspan=2`, Username sticky kiri), satu `th` per tanggal (`colspan` = jumlah sesi hari itu, format `d M`), kelima total (`rowspan=2`). Baris bawah: nama program per sesi, ditautkan ke halaman absensi sesi itu.
- Sel status memakai gaya yang sama dengan halaman satu program (diambil ke partial bersama).
- Filter tanggal, default rentang, dan validasi tetap.
- Bila tidak ada sesi di rentang itu: tabel tetap tampil (hanya Username, Role, total) dan di bawahnya `Belum ada sesi terjadwal di rentang tanggal ini.`

## 3. Kode (perlu dicek)

- Dependensi baru `openspout/openspout:^5.13`.
- Kelas baru `app/Support/AttendanceSpreadsheet.php` (folder `app/Support` baru, di dalam `app/`) yang menulis satu tabel ke response unduhan .xlsx: judul, header, isi, gabungan sel header, freeze, lebar kolom, warna status.
- `AbsensiController`: logika baris matriks dipakai bersama oleh satu program dan per orang; `summaryRows`, `csvDownload`, `writeCsvRow` dihapus.

## Di luar cakupan

- Halaman Per program (`/absensi`) dan halaman satu sesi di web tidak berubah selain label tombol ekspor.
- Ekspor beberapa sheet, grafik, rumus total di Excel.

## Aturan proyek yang berlaku

- Buat file dengan `php artisan make:...`, jalankan `vendor/bin/pint --dirty --format agent`, tes Pest tersempit, lalu minta user menjalankan `php artisan test --compact`.
- Pengecekan manual hanya di SQLite cermin, tidak ke MySQL dev.
- Commit atas nama user, tanpa `Co-Authored-By`/sebutan AI; `docs/superpowers` tidak di-commit tanpa izin.
