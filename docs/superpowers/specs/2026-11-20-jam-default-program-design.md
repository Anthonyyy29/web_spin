# Desain: jam default program, tombol "Apply waktu default", dan aturan tap baru

Melanjutkan `docs/superpowers/specs/2026-11-20-crud-program-hr-design.md` (program dikelola admin dan HR lewat menu podcast).

## Tujuan dan alasan

HR mengetik jam mulai dan selesai dari nol setiap membuat sesi, padahal tiap program biasanya siaran di jam yang sama. Pemilik ingin tiap program punya jam default, dan form sesi punya tombol "Apply waktu default" yang mengisi jam dari program yang dipilih. Pemilik juga menetapkan **aturan tap baru** untuk absensi sesi (menggantikan aturan sekarang).

## Cakupan

1. Dua kolom jam default pada program, diisi lewat form program (modal podcast).
2. Tombol "Apply waktu default" di modal Tambah dan Ubah sesi (`admin/absensi/sesi.blade.php`).
3. Aturan tap baru di `BroadcastSession` (`findForTap`, `statusAt`) beserta pemakainya di `AbsensiController::tap`.

## Bukan tujuan

- Jam default tidak tampil di situs publik; tidak ada jam per hari; sesi tidak dibuat otomatis.
- Tombol hanya mengisi jam (bukan tanggal), tidak mengisi otomatis saat program dipilih.
- Tidak mengubah log absensi yang sudah tercatat (tidak ada migrasi data atau penghitungan ulang status).
- Seeder tidak mengisi jam default (jam siaran tiap program tidak diketahui).

## Keputusan pemilik

- Ada tombol "Apply waktu default" (bukan isi otomatis).
- Jam default terdiri dari jam mulai dan jam selesai.
- **Aturan tap baru (menggantikan yang lama), untuk sesi berjam mulai M dan selesai S:**
  - tap sebelum M → **tamu** (tidak dikaitkan ke sesi);
  - tap dari M sampai S → **Hadir**;
  - tap setelah S → **Terlambat**.

## Aturan tap baru, rinci (keputusan penulis di luar kata-kata pemilik)

Pemilik belum menyebut batas akhir "Terlambat". Usul penulis, mohon dikoreksi:

- **Batas akhir Terlambat: S + 60 menit** (inklusif). Setelahnya tap menjadi tamu. Alasan: tanpa batas, tap apa pun di sisa hari bisa terkait ke sesi program itu. Angka 60 sama dengan konstanta jendela yang sudah dipakai kode sekarang (`TAP_WINDOW_MINUTES`). Konstanta itu diganti `LATE_WINDOW_MINUTES = 60` (jendela sebelum mulai dihapus).
- **Batas inklusif:** tap tepat M dan tepat S → Hadir; tepat S + 60 menit → Terlambat; sedetik setelahnya → tamu.
- **Sesi bersebelahan:** bila tap jatuh di jendela dua sesi program crew itu (mis. Terlambat untuk sesi A dan Hadir untuk sesi B), dipilih yang jam mulainya terdekat dengan waktu tap (aturan pemilihan yang sudah ada tetap dipakai). Tap di dalam [M, S] sebuah sesi tidak boleh kalah oleh jendela Terlambat sesi lain yang jam mulainya lebih jauh.
- **Sehari:** tetap hanya sesi bertanggal hari tap (sesi lewat tengah malam belum didukung, seperti sekarang).
- `statusAt` mengembalikan `Hadir` untuk M ≤ waktu ≤ S dan `Terlambat` sesudahnya. Pesan JSON tap tidak berubah (`Absen berhasil`, `Dicatat sebagai tamu`, `Anda sudah absen di sesi ini`, `Anda sudah absen hari ini`).
- Riwayat, ekspor, dan dashboard tidak berubah (status tetap `Hadir`/`Terlambat`/tamu).

## Rancangan jam default dan tombol

- **Skema:** migrasi menambah `contents.jam_mulai_default` dan `contents.jam_selesai_default` (time, nullable). `Content::$fillable` memuatnya.
- **Form program (`admin/podcast.blade.php`, `PodcastController`):** dua `input type="time"` berlabel "Jam mulai default" dan "Jam selesai default" (opsional). Validasi: `nullable`, `date_format:H:i`, `required_with` satu terhadap lainnya (keduanya terisi atau keduanya kosong), jam selesai `after:jam_mulai_default`. Tombol Edit membawa nilainya lewat atribut data; modal Create mengosongkannya.
- **Form sesi (`SesiController::index`, `sesi.blade.php`):** tiap `<option>` program membawa `data-mulai` dan `data-selesai` (format `H:i`, kosong bila belum ada). Tombol "Apply waktu default" di modal Tambah dan Ubah membaca opsi program yang terpilih dan mengisi Jam Mulai dan Jam Selesai; nonaktif dengan keterangan "Program ini belum punya jam default" bila kosong, dan mengikuti perubahan pilihan program. JS inline di berkas, tanpa dependensi baru, tanpa permintaan server tambahan.

## Pendekatan yang dipertimbangkan

- **Data atribut di opsi + JS kecil (dipilih):** tidak ada endpoint baru, jam selalu cocok dengan daftar yang sedang tampil.
- **Endpoint JSON yang mengembalikan jam program:** satu permintaan server per klik, rute baru, tanpa manfaat bagi daftar program sekecil ini.
- **Isi otomatis saat program dipilih:** ditolak pemilik (memakai tombol).

## Tes yang dibutuhkan

- Form program menyimpan dan mengosongkan jam default; validasi menolak hanya satu terisi dan jam selesai ≤ mulai; format salah ditolak.
- Halaman sesi memuat `data-mulai`/`data-selesai` tiap program dan tombol "Apply waktu default".
- Aturan tap baru: sebelum M = tamu (tanpa log sesi); tepat M dan tepat S = Hadir; S + 1 detik dan tepat S + 60 menit = Terlambat; S + 60 menit + 1 detik = tamu; dua sesi bersebelahan memilih sesi yang jam mulainya terdekat; tap bermikrodetik (perilaku yang sudah diuji) tetap memilih sesi benar; `statusAt` sesuai.
- Tes lama yang berasumsi jendela 60 menit sebelum mulai atau Terlambat mulai dari M (di `BroadcastSessionTest.php` dan `AbsensiSesiTest.php`, juga `ProgramHrTest.php` yang memakai tap jam 09:55) diperbarui ke aturan baru dengan alasan yang jelas di pesan commit.

## Diserahkan ke pelaksana

Nama dan penempatan atribut/JS tombol, teks keterangan nonaktif, gaya input jam di modal program, bentuk kueri pemilihan sesi (selama batas dan urutan pemilihan di atas terpenuhi).
