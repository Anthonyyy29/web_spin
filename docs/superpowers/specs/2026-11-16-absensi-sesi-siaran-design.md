# Absensi per Sesi Siaran — Design

Tanggal: 2026-11-16 · Status: menunggu persetujuan user.

## Tujuan dan alasan

Absensi RFID sekarang hanya mencatat "kartu X hadir tanggal Y" (satu baris per kartu per hari, status selalu `Hadir`). User ingin absensi lebih rapi: terkait ke **program siaran** dan **sesinya**. Crew per program tetap, tetapi jadwal sering berubah dan bisa diundur lebih dari seminggu, jadi jadwal tidak bisa berupa pola mingguan. Jadwal dicatat sebagai **daftar sesi** (tanggal + jam) yang diubah HR kapan saja.

## Goals

- HR mengelola sesi siaran per program: tambah, ubah tanggal/jam (termasuk diundur berminggu-minggu), hapus.
- Tap kartu dicocokkan ke sesi program crew itu berdasarkan tanggal dan jam. Absen berarti "hadir di sesi X".
- Status `Terlambat` terhitung dari jam mulai sesi.
- Tap yang tidak cocok dengan sesi mana pun dicatat sebagai **tamu** dan ditampilkan terpisah dari absensi sesi.
- Riwayat, ekspor Excel, dan dashboard HR mengenal sesi dan memisahkan tamu.

## Non-goals

- Tidak ada pembuatan sesi otomatis atau pola berulang (jadwal terlalu sering berubah).
- Tidak ada status `Pulang` (tidak diminta).
- Tidak mengubah alur alat RFID/AutoHotKey dan tidak menambah autentikasi `/tap-absen` (di luar permintaan; tetap tercatat sebagai kelemahan di `docs/referensi-absensi-rfid-radio.md`).
- Tidak mengubah situs publik.

## Anti-goals

- Tap crew yang sah tidak boleh hilang atau error hanya karena tidak ada sesi: harus tetap tercatat (sebagai tamu).
- Satu kartu tidak boleh punya dua catatan absen untuk sesi yang sama.

## Constraints

- Laravel 13, Blade, AdminLTE/Bootstrap, tanpa dependensi baru, tanpa folder dasar baru.
- Pakai tabel `contents` yang sudah ada sebagai "program" (satu daftar program saja). **Asumsi saya**: program siaran = baris `contents`; kalau salah, desain ini perlu diubah.
- Tes Pest (SQLite memori); `vendor/bin/pint --dirty --format agent` setelah mengubah PHP.

## Pendekatan yang dipertimbangkan

- **A. Satu tabel log, `session_id` boleh kosong (dipilih user).** Kosong berarti tamu. Satu alur simpan, satu riwayat, satu ekspor.
- B. Tabel `absensi_tamu` terpisah. Dua alur dan dua ekspor untuk hasil yang sama. Ditolak.
- C. Tamu sebagai nilai `status`. Mencampur arti kolom. Ditolak.

## Rancangan

### Data

- `broadcast_sessions`: `id`, `content_id` (→ contents, cascade delete), `tanggal` (date), `jam_mulai` (time), `jam_selesai` (time), indeks pada (`tanggal`).
- `content_crew` (pivot): `content_id`, `crew_id`, unik pasangan. Satu crew boleh di banyak program.
- `absensi_logs`: tambah `session_id` nullable (→ broadcast_sessions, null on delete → log jadi tamu). Ubah kunci unik dari (`rfid_uid`, `tanggal`) menjadi (`rfid_uid`, `session_id`) untuk absen sesi. Tamu: satu per kartu per hari, ditegakkan di logika tap (kunci unik dengan kolom null tidak menjaga ini).
- Migrasi baru (bukan edit migrasi lama), dengan `down()`. Log lama tanpa sesi otomatis menjadi tamu.
- Status enum tetap; tamu memakai `status` = `Hadir` dan dibedakan oleh `session_id` null (tidak menambah nilai enum).

### Logika tap (`AbsensiController::tap`)

1. UID kosong / kartu tidak terdaftar: sama seperti sekarang.
2. Ambil sesi hari ini milik program-program crew pemilik kartu. Kandidat cocok bila waktu tap berada antara `jam_mulai − 60 menit` dan `jam_selesai`. Bila lebih dari satu, ambil yang `jam_mulai`-nya paling dekat dengan waktu tap.
3. Ada sesi cocok: sudah ada log (kartu, sesi) → `warning` "Anda sudah absen di sesi ini". Belum: simpan dengan `status` `Terlambat` bila waktu tap > `jam_mulai`, selain itu `Hadir`.
4. Tidak ada sesi cocok: catat tamu (`session_id` null). Sudah ada tamu hari ini untuk kartu itu → `warning` "Anda sudah absen hari ini".
5. Respons JSON menambah nama program/sesi, atau penanda tamu, agar layar tap bisa menampilkannya.

Angka 60 menit dan "tanpa toleransi terlambat" adalah pilihan saya, mudah diubah (satu konstanta di tempat logika itu berada).

### Layar HR

- Menu **Sesi**: daftar sesi mendatang/riwayat, tambah dan ubah sesi (program, tanggal, jam mulai/selesai), hapus.
- Penugasan crew ke program: di layar yang paling cocok (kelola kru per program), bentuk persisnya diserahkan ke pembangun.
- **Riwayat**: tab "Sesi" (dengan kolom program dan jam sesi) dan tab "Tamu". Ekspor Excel ikut dua bagian itu.
- **Dashboard**: grafik status dan tren dihitung dari absensi sesi saja (tamu tidak masuk); grafik divisi tetap. Tamu hari ini ditampilkan terpisah.

## Diserahkan ke pembangun

- Bentuk layar penugasan crew ke program dan tata letak tab.
- Nama route/kelas, pemisahan query ke model/scope selama tidak menambah lapisan baru yang tidak perlu.
- Validasi form sesi (jam selesai setelah jam mulai, program ada).

## Pilihan saya yang perlu dicek user

1. Program = tabel `contents`.
2. Jendela cocok: 60 menit sebelum jam mulai sampai jam selesai.
3. Terlambat = tap setelah jam mulai, tanpa toleransi.
4. Beberapa sesi cocok → ambil jam mulai terdekat.
5. Log lama menjadi tamu.
6. Tamu tidak masuk grafik status/tren.

## Risiko

- Migrasi mengubah kunci unik `absensi_logs`; dicoba di SQLite dulu, bukan MySQL dev.
- Bila `contents` bukan daftar program siaran sebenarnya, penugasan crew dan sesi perlu tabel program sendiri.
