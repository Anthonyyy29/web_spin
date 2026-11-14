# Absensi Crewcall — Design

Tanggal: 2026-10-24
Sumber: diskusi dengan user + dokumen "Program Radio Gen 7 Ganjil" (attendance schedule Spin The Radio FM).

## Tujuan

Mencatat kehadiran announcer dan sound operator (SO) di setiap sesi program siaran, dengan patokan jam **crewcall**. HR mengelola program mingguan dan sesinya, termasuk reschedule, serta melihat dan mengekspor rekap absensi.

Di luar cakupan versi ini: absensi tapping dan jadwal upload (despub), kru selain announcer dan SO, manajemen akun (akun dibuat lewat seeder), dan ekspor `.xlsx`.

## Istilah

- **Program:** template mingguan, misalnya K-Time setiap Senin dengan crewcall 07.30 dan tapping 08.00–09.30. Berlaku untuk satu periode (tanggal mulai sampai selesai).
- **Sesi:** satu kejadian program di tanggal tertentu. Absensi menempel ke sesi.
- **Kru:** announcer dan SO yang bertugas di program atau sesi.

## Keputusan

1. Program + Sesi **menggantikan** fitur Jadwal Siaran lama. Tabel `broadcast_schedules` beserta model, factory, controller, request, view, dan tesnya dihapus. Belum ada data produksi.
2. Role baru `so` (sound operator) dengan login dan area sendiri `/so`. Announcer tetap di `/announcer`.
3. Zona waktu aplikasi diubah ke `Asia/Jakarta` (`config/app.php`).
4. Saat program disimpan, sesi untuk setiap tanggal yang harinya cocok di dalam periode **langsung dibuat**. Tidak ada cron.
5. Mengubah program hanya menimpa **sesi mendatang yang belum diubah manual** (`is_customized = false` dan tanggal ≥ hari ini). Sesi yang sudah lewat dan sesi yang diubah manual tidak tersentuh.
6. Sesi bisa:
   - **Diubah:** tanggal, jam, dan kru.
   - **Ditunda:** tanggal dan jam dikosongkan, status `postponed`, lalu muncul di "Perlu dijadwalkan ulang" sampai HR mengisi jadwal baru. Tidak ada batas berapa minggu ke depan.
   - **Dibatalkan:** status `cancelled`.
   - Ketiganya menyetel `is_customized = true`. Sesi pengganti berjalan berdampingan dengan sesi reguler minggu itu.
7. Announcer dan SO **check-in sendiri**:
   - Jendela check-in: dari `crewcall − 15 menit` sampai `tapping_start` (tidak inklusif).
   - **Hadir** jika check-in ≤ `crewcall + 5 menit` dalam menit penuh. Contoh crewcall 07.30: Hadir sampai 07.35:59, Terlambat mulai 07.36:00.
   - **Terlambat** jika setelah itu dan masih di dalam jendela.
   - **Tidak hadir:** tidak ada baris absensi dan jendela sudah ditutup. Status ini dihitung, tidak disimpan.
8. HR bisa mengubah status seseorang di satu sesi menjadi **Izin** atau **Sakit** dengan catatan, atau mengembalikannya. HR tidak bisa mengisi atau mengubah jam check-in.
9. Rekap HR: per sesi, per orang dalam satu periode (rentang tanggal), dan ekspor **CSV** untuk kedua rekap.

## Struktur data

| Tabel | Kolom | Catatan |
|---|---|---|
| `programs` | `id`, `name`, `day_of_week` (1=Senin … 7=Minggu, ISO), `crewcall_time`, `tapping_start`, `tapping_end`, `period_start`, `period_end`, timestamps | |
| `program_crew` | `program_id`, `user_id`, `position` (`announcer`\|`so`) | unik (`program_id`, `user_id`) |
| `broadcast_sessions` | `id`, `program_id`, `session_date`?, `crewcall_time`?, `tapping_start`?, `tapping_end`?, `status` (`scheduled`\|`postponed`\|`cancelled`), `is_customized` (bool), timestamps | kolom bertanda `?` boleh null hanya saat `postponed` |
| `session_crew` | `broadcast_session_id`, `user_id`, `position` | disalin dari `program_crew`; unik (`broadcast_session_id`, `user_id`) |
| `attendances` | `id`, `broadcast_session_id`, `user_id`, `checked_in_at`?, `status` (`hadir`\|`terlambat`\|`izin`\|`sakit`), `note`?, timestamps | unik (`broadcast_session_id`, `user_id`) |

FK: `program_crew` dan `session_crew` di-cascade saat parent dihapus. `attendances.broadcast_session_id` memakai restrict. Program yang sesinya sudah punya absensi tidak bisa dihapus.

Waktu disimpan sebagai `H:i:s`. Perbandingan jam mengikuti pola request Jadwal Siaran yang lama (normalisasi ke `H:i:s`).

"Selesai" tidak disimpan. Sesi dianggap lewat jika `session_date` + `tapping_end` < sekarang.

## Halaman

**HR** (`/hr`, middleware `auth` + `isHr`), menu sidebar: Dashboard, Program, Sesi, Absensi, Pengaturan (nonaktif).

1. **Program:** daftar, tambah, ubah, hapus.
   - Form: nama, hari, crewcall, tapping mulai/selesai, periode mulai/selesai, multi-pilih announcer, multi-pilih SO.
   - Validasi:
     - semua wajib diisi;
     - `period_end ≥ period_start`;
     - `crewcall < tapping_start < tapping_end`;
     - minimal 1 announcer;
     - announcer harus ber-role `announcer`, SO harus ber-role `so`;
     - orang yang sama tidak boleh muncul di dua posisi.
   - Simpan → buat sesi (Keputusan 4).
   - Ubah → timpa sesi yang memenuhi syarat (Keputusan 5). Ini mencakup jam, kru, serta penambahan atau penghapusan sesi jika hari atau periode berubah. Sesi lama yang tidak lagi cocok dihapus hanya jika belum di-customize dan belum punya absensi.
   - Hapus → ditolak dengan pesan jika ada absensi.
2. **Sesi:**
   - Daftar sesi, urut tanggal, dengan penanda "Hari ini". Bisa difilter per program.
   - Di atas daftar ada kotak "Perlu dijadwalkan ulang" berisi sesi `postponed`.
   - Aksi: Ubah (tanggal, jam, kru), Tunda, Batalkan.
   - Bentrok ditolak: orang yang sama di dua sesi `scheduled` dengan tapping tumpang tindih di tanggal yang sama.
3. **Absensi per sesi:** daftar kru sesi dengan status (Hadir, Terlambat, Tidak hadir, Izin, Sakit, atau Belum, jika jendela belum ditutup) dan jam check-in. HR bisa mengubah status ke Izin/Sakit + catatan, atau mengembalikannya.
4. **Absensi per orang:** untuk rentang tanggal yang dipilih HR (default: tanggal 1 bulan ini sampai hari ini), tiap announcer/SO beserta jumlah Hadir, Terlambat, Tidak hadir, Izin, Sakit. Hanya sesi `scheduled` yang jendelanya sudah ditutup yang dihitung.
5. **Ekspor CSV:** rekap per orang dan rekap per sesi, sesuai filter yang aktif. Dibuat dengan `response()->streamDownload` dan `fputcsv`, tanpa dependensi baru.

**Announcer** (`/announcer`, `auth` + `isAnnouncer`) dan **SO** (`/so`, `auth` + middleware baru `isSo`):

- Halaman **"Sesi saya"** berisi sesi mendatang dan sesi lewat di mana orang itu menjadi kru. Isinya program, tanggal, crewcall, tapping, dan status absensinya.
- Tombol **Check-in** hanya tampil aktif di dalam jendela. Setelah check-in, tampil status dan jamnya.
- Satu controller dan satu view partial dipakai bersama. Route bernama `announcer.sesi.*` dan `so.sesi.*`, masing-masing dengan layout role-nya.
- Menu "Jadwal Siaran" announcer diganti "Sesi saya". SO mendapat layout sendiri dengan menu "Sesi saya".

## Aturan server untuk check-in

Check-in (`POST`) ditolak dengan pesan berbahasa Indonesia jika:
- user bukan kru sesi itu (403);
- sesi tidak `scheduled`;
- sekarang di luar jendela;
- sudah ada baris absensi untuk user + sesi itu. Kunci unik juga mencegah klik ganda.

Status Hadir/Terlambat dihitung dari `now()` di server, bukan dari jam di perangkat.

## Seeder

Tambahkan contoh akun ber-role `so` dan beberapa announcer, plus satu program contoh beserta sesinya, agar fitur bisa dicoba lokal. Akun HR dan announcer yang sudah ada tetap.

## Pengujian (Pest, factory, SQLite in-memory)

- Pembuatan sesi: program Senin dengan periode 6 Okt–26 Jan menghasilkan 17 sesi dengan tanggal yang benar, dan kru tersalin.
- Ubah program: hanya sesi mendatang yang tidak di-customize yang berubah. Sesi lewat dan sesi yang di-customize tetap.
- Tunda (tanggal/jam null, muncul di daftar jadwal ulang), jadwalkan ulang, batal.
- Check-in, crewcall 07.30, tapping 08.00, dengan `travelTo`:
  - 07.14 ditolak;
  - 07.15 Hadir;
  - 07.35:59 Hadir;
  - 07.36 Terlambat;
  - 08.00 ditolak.
- Check-in ditolak untuk: bukan kru, sesi ditunda, sesi batal, check-in ganda.
- HR mengubah status ke Izin/Sakit dan mengembalikannya. HR tidak bisa mengisi jam.
- Rekap per orang menghitung Tidak hadir untuk sesi tertutup tanpa absensi, dan tidak menghitung sesi yang belum ditutup, ditunda, atau batal.
- Isi CSV: header dan baris sesuai data.
- Akses: SO tidak bisa ke `/hr` dan `/announcer`, announcer tidak bisa ke `/so`, HR tidak bisa check-in.
- Hapus program dengan absensi ditolak.
- Bentrok kru antar sesi ditolak.
