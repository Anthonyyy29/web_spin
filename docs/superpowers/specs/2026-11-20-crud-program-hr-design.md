# Desain: HR mengelola program (CRUD) di menu yang sama dengan admin

## Tujuan dan alasan

Program (tabel `contents`, segmen podcast) hanya bisa dibuat dan diubah admin. HR hanya bisa menugaskan crew ke program yang sudah ada. Pemilik ingin HR punya fitur yang persis sama dengan admin untuk program (tambah, ubah, hapus, beserta episode), supaya HR tidak bergantung pada admin. Program tetap satu tabel dengan podcast: perubahan HR terlihat di situs publik `/listen`.

## Keputusan pemilik

- Tetap satu tabel (`contents`).
- Fitur "Program" HR yang sekarang (halaman daftar checkbox crew) diganti oleh fitur baru.
- Penugasan crew ke program (`content_crew`) pindah ke form tambah/ubah program yang sama.

## Tujuan

- HR dan admin memakai menu Podcast yang sama: program, episode, unggah gambar, catatan audit.
- Crew per program disimpan lewat form yang sama, tanpa halaman terpisah.
- Tap RFID tetap memetakan sesi lewat `content_crew` (tidak berubah).

## Bukan tujuan

- Tidak memisahkan program HR dari podcast publik.
- Tidak mengubah API (`api/*`), halaman publik, atau menu admin lain (crew, news, sponsor).
- Tidak mengubah skema database.
- Tidak memperbaiki temuan audit lain (hapus via GET, galat validasi, sesi tumpang tindih); itu pekerjaan terpisah.

## Anti-tujuan

Penugasan crew hilang atau tidak tersimpan sehingga semua tap menjadi tamu; HR terkunci dari menu baru; program terhapus diam-diam bersama sesi dan absensinya.

## Pendekatan yang dipertimbangkan

- **A. Satu menu Podcast dipakai admin dan HR (dipilih).** Grup rute podcast ke `role:admin,hr` (middleware sudah menerima banyak peran), HR diberi item menu. Tanpa controller atau view yang digandakan.
- **B. Gabung ke halaman Program HR dengan controller baru.** Satu tempat dengan istilah HR, tetapi menggandakan alur simpan/unggah dan tidak mencakup episode.
- Salinan penuh ditolak: menggandakan kode dan bertentangan dengan satu tabel.

Pemilik memilih A, dengan penugasan crew ditambahkan ke form yang sama dan halaman Program lama diganti.

## Rancangan

- **Rute:** pindahkan grup `podcast` di `routes/web.php` dari `role:admin` ke `role:admin,hr`. Hapus rute `absensi/program` dan `absensi/program/update`.
- **Menu HR:** item "Program" di `layouts/navmenus/hr.blade.php` menuju `/dashboard/podcast`, aktif juga saat `navmenu` bernilai `Podcast` atau `Episode`.
- **Form program (`admin/podcast.blade.php`):** bagian "Crew program ini" berisi **tiga dropdown multi-pilih, satu per jabatan**: Announcer, Sound Operator, Human Resource (Opsi 2, dipilih pemilik). Tiap dropdown hanya berisi crew dengan jabatan itu; yang terpilih tampil sebagai chip. Saat ubah, pilihan terisi dari crew program itu. Gambaran: file Figma <https://www.figma.com/design/wb4eVA3kNlY61bFppYVPjV> (frame A, B, B2; frame C = Opsi 1 yang tidak dipilih). Nama dan jabatan di Figma hanya contoh.
- **Jabatan yang boleh ditugaskan:** konstanta di `Crew` (mis. `Crew::PROGRAM_ROLES` = Announcer, Sound Operator, Human Resource), dipakai view dan validasi.
- **Simpan (`PodcastController`):** setelah program tersimpan, sinkronkan crew dari ketiga dropdown. Validasi: tiap id harus ada di `crews` **dan** berjabatan salah satu dari tiga di atas (tolak selain itu, karena form bisa dipalsukan). Penugasan yang sudah ada untuk crew berjabatan lain (data seeder/lama) **tidak boleh terlepas diam-diam**: sinkronisasi hanya memengaruhi crew ketiga jabatan itu. `ImageCrudController::save` perlu titik ikut setelah simpan, atau `PodcastController` menimpanya; pilihan teknisnya diserahkan ke pelaksana.
- **Dropdown tanpa dependensi baru:** Bootstrap (dropdown berisi centang) plus JS statis kecil untuk chip, sesuai larangan menambah dependensi.
- **Hapus program:** ditolak dengan pesan galat bila program masih punya sesi siaran (lihat keputusan penulis).
- **Dibersihkan:** `Admin\Absensi\ProgramController`, `admin/absensi/program.blade.php`.

## Keputusan penulis (belum disetujui pemilik, mohon dikoreksi)

1. Admin ikut melihat dropdown crew di form yang sama (satu form tanpa percabangan peran).
2. Menu HR berlabel "Program"; menu admin tetap "Podcast".
3. Menghapus program yang punya sesi ditolak. Alasan: `broadcast_sessions.content_id` bersifat `cascadeOnDelete`, jadi sesi terhapus dan absensinya menjadi tamu. Pesannya menyuruh menghapus sesi lebih dulu.
4. `AbsensiProgramTest` diganti dengan tes untuk form baru (perlu izin pemilik karena menghapus berkas tes).
5. `AdminTest` "menu admin hanya untuk admin": `/dashboard/podcast` dikeluarkan dari daftar yang ditolak untuk HR; `crew`, `news`, `sponsor` tetap 403 untuk HR.

## Tes yang dibutuhkan

- HR membuka `/dashboard/podcast` dan episode (200); `crew`, `news`, `sponsor` tetap 403 untuk HR.
- HR membuat program dengan gambar dan crew; `content_crew` terisi; ubah program menyinkronkan (menambah dan melepas crew).
- Crew berjabatan di luar tiga jabatan ditolak validasi; penugasan lama crew berjabatan lain tidak terlepas saat simpan.
- Hapus program tanpa sesi berhasil; dengan sesi ditolak dan sesi serta absensi utuh.
- `/dashboard/absensi/program` tidak ada lagi (404); tap RFID tetap memetakan sesi lewat crew yang ditugaskan dari form baru.
- Peran lain/tamu tetap ditolak.

## Diserahkan ke pelaksana

Bentuk titik ikut di `ImageCrudController`; cara mengisi centang crew saat modal ubah dibuka (data attribute atau JSON); gaya daftar checkbox di modal (kolom, tinggi gulir); teks pesan.
