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

## Revisi 2026-11-20: jabatan tambahan opsional (usulan pemilik)

Temuan setelah implementasi: crew berjabatan ganda disimpan dengan jabatan utamanya (mis. Syavirni = Sekretaris, Noval dan Shafa = Bendahara), jadi tidak muncul di dropdown tiga jabatan dan penugasan lamanya tidak bisa dikelola. Pemilik memilih menambah **satu jabatan tambahan opsional** pada crew.

- **Skema:** migrasi menambah `crews.extra_role` (string 30, nullable). `Crew::$fillable` memuatnya. Jabatan utama (`role`) tidak berubah.
- **Form Crew admin (`admin/crew.blade.php`, `Admin\CrewController`):** pilihan "Jabatan tambahan (opsional)" berisi kosong plus `Crew::ROLES`. Aturan: `nullable`, `in:` jabatan di `Crew::ROLES`, `different:role`. Modal ubah mengisi nilainya. Kartu crew admin menampilkan jabatan tambahan bila ada (cara tampil diserahkan ke pelaksana).
- **Dropdown program:** crew masuk ke grup sebuah jabatan bila `role` **atau** `extra_role` sama dengan jabatan itu. Orang berjabatan utama Announcer dan tambahan Sound Operator muncul di kedua grup. Tiga dropdown tidak berubah.
- **Validasi `crew_ids.*` dan sinkronisasi:** "berhak ditugaskan" berarti `role` atau `extra_role` termasuk `Crew::PROGRAM_ROLES`. Crew yang tidak memenuhi itu tetap dipertahankan saat simpan (aturan lama).
- **Tidak berubah:** situs publik, grafik jabatan, kolom divisi di riwayat/ekspor/grafik absensi, filter menu Crew tetap memakai jabatan utama. Jabatan tambahan hanya untuk penugasan program.
- **Seeder (nilai dari pemilik, 2026-11-20):** `CrewSeeder` mengisi `extra_role` untuk tepat tiga orang berjabatan ganda: Syavirni Dwi Amalia (utama Sekretaris) = `Sound Operator`; Noval Rasyah Kurnia Putra (utama Bendahara) = `Announcer`; Shafa Dhiya Raihanah (utama Bendahara) = `Announcer`. Data proyek (spec role-jabatan-profil 2026-10-24) menyebut hanya tiga orang itu yang ganda; anggota ganda lain diisi admin lewat form Crew bila ada. Tidak ada jabatan baru "Lainnya". `ProgramCrewSeeder` tidak berubah.
- **Crew di dua dropdown:** orang yang jabatan utama dan tambahannya sama-sama termasuk tiga jabatan program muncul di dua dropdown; centang di satu dropdown mencerminkan centang di dropdown lain bernilai sama, dan id ganda pada permintaan tidak ditolak, hanya disimpan sekali (aturan `distinct` dihapus; `unique()` di sinkronisasi menjamin satu baris).
- **Tes:** crew berjabatan tambahan muncul di grup dropdown yang benar dan bisa disimpan; jabatan utama bukan tiga jabatan tapi tambahan cocok diterima validasi; `extra_role` sama dengan `role` atau di luar daftar ditolak di form Crew; seeder mengisi `extra_role` untuk tiga orang itu; grafik dan situs publik tidak terpengaruh.
- **Keputusan penulis:** tidak ada pencabutan penugasan otomatis bila jabatan tambahan dihapus dari crew (penugasan tetap; terlihat sebagai "jabatan lain" tak terkelola seperti sebelumnya, tetapi admin bisa mengisi ulang). Batas satu jabatan tambahan.

## Tes yang dibutuhkan

- HR membuka `/dashboard/podcast` dan episode (200); `crew`, `news`, `sponsor` tetap 403 untuk HR.
- HR membuat program dengan gambar dan crew; `content_crew` terisi; ubah program menyinkronkan (menambah dan melepas crew).
- Crew berjabatan di luar tiga jabatan ditolak validasi; penugasan lama crew berjabatan lain tidak terlepas saat simpan.
- Hapus program tanpa sesi berhasil; dengan sesi ditolak dan sesi serta absensi utuh.
- `/dashboard/absensi/program` tidak ada lagi (404); tap RFID tetap memetakan sesi lewat crew yang ditugaskan dari form baru.
- Peran lain/tamu tetap ditolak.

## Diserahkan ke pelaksana

Bentuk titik ikut di `ImageCrudController`; cara mengisi centang crew saat modal ubah dibuka (data attribute atau JSON); gaya daftar checkbox di modal (kolom, tinggi gulir); teks pesan.
