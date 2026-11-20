# Konteks aplikasi SPIN

Ringkasan untuk coding agent supaya tidak perlu membaca seluruh kode. Diperbarui 2026-11-20, branch `crud-program-hr`.

## Apa ini

Website Spin The Radio (radio kampus Universitas Esa Unggul Bekasi) dalam Laravel. Menggantikan seluruhnya repo CodeIgniter 4 `samuelmahesasinulingga/radio-web-new` (folder `Radio Project/`). Untuk sekarang **meniru repo lama**: URL, alur, pesan, dan rupa. Absensi RFID hanya satu fitur di dalamnya. Situs publik akan dirombak nanti dengan Tailwind; sekarang Bootstrap.

## Stack

- Laravel 13, PHP 8.5, Blade biasa (tanpa Livewire/Inertia), MySQL lewat Sail (`compose.yaml`).
- Tampilan: AdminLTE 4 (statis di `public/assets/dashboard/`), Bootstrap 5.3, SweetAlert2, Chart.js dari CDN. **Tidak ada Vite/Tailwind/npm**, tidak ada langkah build.
- Excel: `openspout/openspout`. Tes: Pest (SQLite memori). Format: Pint.
- Zona waktu `Asia/Jakarta`.

## Perintah

```
sail up -d
sail artisan migrate:fresh --seed                   # dua akun
sail artisan db:seed --class=SampleContentSeeder    # data contoh
php artisan test --compact                          # suite penuh (jumlah tes berubah; jalankan untuk melihat)
vendor/bin/pint --format agent
```

`CrewSeeder` mengisi `extra_role` tiga orang: Syavirni Dwi Amalia (Sekretaris) = Sound Operator; Noval Rasyah Kurnia Putra dan Shafa Dhiya Raihanah (Bendahara) = Announcer; baris seeder boleh punya elemen keempat opsional untuk jabatan tambahan. Menjalankan ulang `db:seed` menimpa `role`, `image`, dan `extra_role` anggota tersebut (dan penugasan program lewat `ProgramCrewSeeder`), jadi jangan dijalankan pada data live.

Login: `admin`, `hr`, password `password` (ditulis langsung di `database/seeders/DatabaseSeeder.php`; ganti sebelum production).

## Peran dan akses

`users.role` di-cast ke enum `App\Enums\UserRole` (Admin, Hr). Dua akun bersama, bukan per orang. Middleware `role:<peran>` (`EnsureUserHasRole`, 403) membatasi: crew/news/sponsor hanya admin, absensi hanya hr, **podcast dipakai bersama admin dan hr** (`role:admin,hr`; di menu HR bernama "Program", `/dashboard/podcast`). `GET /dashboard` menampilkan isi sesuai peran (`DashboardController`: `admin()`, `hr()`; peran lain 403).

## Rute (`routes/web.php`)

| Area | Path |
|---|---|
| Publik | `/` → `/home`, `/home`, `/crew`, `/listen`, `/listen/search`, `/listen/content/{token}`, `/news`, `/news/category/{c}`, `/news/details/{token}`, `/aboutus`, `POST /aboutus/sendmail` |
| Auth | `/login`, `POST /login/checklogin`, `/dashboard/logout` (GET, sama dengan repo lama) |
| Admin | `/dashboard/{crew,news,sponsor}` (+ `save`, `delete/{id}`, `filter/…`) |
| Admin + HR | `/dashboard/podcast` (+ `save`, `delete/{id}`, `filter/…`, episode di `podcast/episode/…`) |
| HR | `/dashboard/absensi/{history,history/all,history/byUid/{rfid},export-excel,crew,crew/simpan,crew/update,crew/delete/{rfid},tap,sesi,sesi/simpan,sesi/update,sesi/delete/{id}}`; riwayat dan ekspor menerima `?jenis=sesi|tamu` (default sesi) |
| API | `GET api/podcastlist`, `GET api/checklatestpodcast/{segmen}`, `POST api/uploadpodcast` (middleware `api.token`) |
| Alat RFID | `GET|POST /tap-absen` (tanpa CSRF, dikecualikan di `bootstrap/app.php`) |

Seluruh grup `/dashboard` memakai `auth` + `actioncheck` (`PreventRapidPosts`: POST kedua < 1 detik ditolak, pesan `Too many actions`).

## Kode penting

- `app/Http/Controllers/Controller.php`: basis untuk helper bersama: `storeUpload`/`deleteUpload` (ke `public/assets/uploads[/sub]`; nilai kolom `image` berupa path relatif, mis. `crew/abc.png`, `program/abc.png`; `ImageCrudController::$uploadFolder` menentukan subfolder tiap menu), `downloadExcel`, dan `encodeId`/`decodeId` (penyamar ID URL publik, **akan dihapus**, lihat `docs/catatan.md`).
- `Admin/ImageCrudController`: dasar crew/news/sponsor/podcast (validasi gambar, simpan, hapus berkas, catat `AuditLog`). Subclass hanya mengisi `modelClass()`, `auditName()`, `fieldRules()`, `prepare()`, dan opsional hook `afterSave(Model, Request)` (dipakai `PodcastController` untuk sinkron `crew_ids[]`: hanya crew yang `Crew::canJoinProgram()`; crew terpasang yang tidak memenuhi syarat tetap saat simpan; validasi menolak crew lain dan array bersarang; id ganda diterima dan disimpan sekali). `PodcastController::delete` menolak program yang masih punya sesi (`Program masih punya sesi siaran. Hapus sesinya dulu di menu Sesi.`). Form di `admin/podcast.blade.php` punya tiga dropdown crew (satu per jabatan) dengan chip: crew masuk dropdown bila jabatan utama ATAU tambahannya itu jabatan dropdown (orang dengan dua jabatan program muncul di dua dropdown; id dideduplikasi, centang dan chip saling mencerminkan). Form Crew admin punya "Jabatan tambahan (opsional)": satu per crew, anggota `Crew::ROLES`, tidak boleh sama dengan jabatan utama (`different:role`).
- `AuthController`: batas 5 gagal / 60 detik per IP, catat `AdminHistory::recordLogin`, pesan kilat `login berhasil` / `login gagal` / `Terlalu banyak percobaan. Coba lagi nanti.` / `Anda telah logout.`.
- Middleware: `role`, `actioncheck`, `api.token` (`VerifyApiToken`, header `Authorization` = `API_ACCESS_TOKEN`, kosong = tolak semua).
- Model berguna: `Crew::ROLES` (jabatan berurut + warna grafik), `Crew::PROGRAM_ROLES` (Announcer, Sound Operator, Human Resource), `Crew::canJoinProgram()` (scope: `role` ATAU `extra_role` termasuk `PROGRAM_ROLES`), `Crew::sortedByRole()`, `News::CATEGORIES`, `News::descriptionRows()`, `News::extractInstagramReelId()`, `Episode::extractSpotifyEpisodeId()`, trait `CountsByWeekday` (grafik mingguan), `AuditLog::record()`, `AbsensiLog::withCrew()`, `AbsensiLog::withSession()`, `BroadcastSession::findForTap()` (sesi hari ini untuk tap; null = tamu). Aturan tap: sebelum jam mulai = tamu; mulai–selesai = Hadir; setelah selesai sampai `LATE_WINDOW_MINUTES` (60) menit = Terlambat; lewat itu = tamu. Bila beberapa sesi cocok, sesi yang sedang berjalan (Hadir) didahulukan atas yang Terlambat, lalu jam mulai terdekat. Form sesi (`admin/absensi/sesi.blade.php`): tombol "Apply waktu default" mengisi Jam Mulai/Jam Selesai (bukan tanggal) dari jam default program terpilih; nonaktif + keterangan bila program tanpa jam default; tanpa isi otomatis.

## Tabel

`users`, `sessions`, `admin_history`, `audit_logs`, `crews`, `contents` (segmen podcast; `jam_mulai_default`/`jam_selesai_default` time nullable = jam default sesi, diisi di form program), `episodes` (`id_content` → contents), `news`, `sponsors`, `rfid_crews` (PK `rfid_uid` string, `crew_id`), `absensi_logs` (`session_id` nullable, null = tamu, `nullOnDelete`; unik `rfid_uid`+`session_id`), `broadcast_sessions` (`content_id`, `tanggal`, `jam_mulai`, `jam_selesai`), `content_crew` (penugasan crew ke program). `crews.extra_role` nullable = jabatan tambahan, hanya dipakai untuk penugasan program; situs publik, grafik dashboard, absensi divisi, dan filter crew admin tetap memakai `role` (utama). Tanpa soft delete (repo lama punya `deleted_at`, tidak dibawa).

## View (`resources/views`)

- Publik: `home`, `listen`, `listen-segment`, `news*`, `crew`, `about-us`, `layouts/{navbar,footer,news-header}`.
- Dashboard: layout `components/layouts/dashboard.blade.php` (`<x-layouts.dashboard :navmenu="…">`), menu per peran `layouts/navmenus/{admin,hr}`, halaman di `admin/`, `admin/absensi/`, grafik di `charts/`.
- Hasil port mekanis dari PHP CodeIgniter; sebagian view masih bergaya lama (array akses `$x['kunci']`, skrip inline).
- Konfigurasi eksternal di `config/services.php`: `podcast_api`, `contact`. Variabel `.env`: `API_ACCESS_TOKEN`, `CONTACT_MAIL_TO`.

## Tes (`tests/Feature`)

Login/logout/seeder/middleware/layout (bagian 1), `SitusPublikTest`, `AdminTest`, `AbsensiTest`, `BroadcastSessionTest`, `AbsensiSesiTest`, `ProgramHrTest` (HR kelola program, penugasan crew, penjaga hapus), `AbsensiSesiCrudTest`, `AbsensiRiwayatTest`. Tes yang mengunggah berkas membersihkan file di `public/assets/uploads` lewat `afterEach`.

## Aturan kerja proyek

- Balas dan beri pertanyaan dalam Bahasa Indonesia; tanya, jangan berasumsi.
- Commit atas nama pengguna saja, **tanpa** Co-Authored-By atau sebutan AI.
- Jangan membuat folder dasar baru selain `docs/` tanpa izin (sempat melanggar dengan `app/Support`, sudah dihapus).
- Jangan query MySQL dev untuk pengecekan; pakai SQLite (`DB_CONNECTION=sqlite` sebagai env asli, `php artisan serve` mengabaikannya, pakai `php -S` dari `public/` dengan `vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`).
- Nilai seeder ditulis langsung, tanpa env/config/enum.
- Ikuti `CLAUDE.md` (Laravel Boost): `artisan make:`, Pint, Pest, jangan ubah dependensi atau hapus tes tanpa izin.

## Perbedaan yang disengaja dari repo lama

Password di-hash (repo lama teks polos); akses dibatasi per peran; `/listen/search` langsung menampilkan hasil (tanpa redirect flash); tautan di Excel berupa teks biasa; video 44 MB dan `contoh.jpg` tidak disalin.

## Status dan sisa pekerjaan

Bagian 1–5 selesai dan lulus tes; halaman diperiksa lewat HTTP, belum di browser. Belum:
1. **Bagian 6**: impor data `radio_web.sql` dan berkas unggahan lama (baru 5 gambar tersalin).
2. Ganti `encodeId`/`decodeId` ke ID biasa (`docs/catatan.md`).
3. Pemeriksaan visual di browser; kemungkinan perbaikan view hasil port.
4. Commit (perubahan setelah seeder belum di-commit) dan keputusan push/PR.
5. `docs/superpowers/specs/2026-11-14-fondasi-port-radio-web-design.md` masih menyebut password dari `.env` (usang).

Dokumen terkait: `docs/catatan.md`, `docs/referensi-absensi-rfid-radio.md`, `docs/superpowers/` (spec dan rencana bagian 1).
