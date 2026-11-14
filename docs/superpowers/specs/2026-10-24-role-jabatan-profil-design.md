# Role per Jabatan & Halaman Profil — Design

Tanggal: 2026-10-24 · Branch: `feat_login`

## Tujuan

Semua anggota Gen 7 punya akun dengan role sesuai jabatannya, supaya fitur untuk divisi lain nanti tinggal memakai akun yang sudah ada. Crew siaran (Announcer, Sound Operator) dan HR — termasuk Kadiv-nya — mendapat halaman Profil untuk mengganti foto, username, dan password. Jabatan lain bisa login tetapi hanya melihat halaman "Fitur belum tersedia".

## Keputusan yang sudah disepakati

- Role = jabatan persis, disimpan di **tabel `roles`** (bukan enum). Tidak ada kolom jabatan terpisah.
- Sekretaris dan Finance **tanpa angka** (Sekretaris 1/2 → `sekretaris`, Finance 1/2 → `finance`).
- Nama role diperjelas: `so` → `sound_operator`, `hr` → `human_resource`; `announcer` tetap.
- Posisi crew juga diganti: `so` → `sound_operator`.
- URL (`/hr`, `/so`), nama route (`hr.*`, `so.*`), folder controller & view **tidak** diubah.
- Kadiv untuk sekarang punya akses yang sama dengan anggotanya. Hak khusus per role diatur setelah proyek ini selesai.
- Role `admin` dihapus.
- Orang dengan dua jabatan mendapat **dua akun terpisah**; username di kedua akun = nama lengkap + label jabatan dalam kurung.
- Profil hanya untuk 6 role crew/HR. Jabatan lain → halaman "Fitur belum tersedia" + tombol Keluar.
- Jabatan tidak bisa diubah pengguna sendiri.
- Password baru tanpa aturan panjang: wajib diisi dan cocok dengan konfirmasi.

## 1. Data

### Tabel `roles` (baru)

Kolom: `id`, `name` (string, unik, snake_case), `label` (string), timestamps.

Isi awal dimasukkan **di migration** (bukan seeder), karena data user lama harus langsung dipetakan. 25 baris (27 jabatan di CSV, Sekretaris 1/2 dan Finance 1/2 digabung), `name` → `label`:

| name | label |
|---|---|
| `gm` | GM |
| `vgm` | VGM |
| `sekretaris` | Sekretaris |
| `finance` | Finance |
| `head_of_podcast` | Head of Podcast |
| `kadiv_announcer` | Kadiv Announcer |
| `announcer` | Announcer |
| `kadiv_sound_operator` | Kadiv Sound Operator |
| `sound_operator` | Sound Operator |
| `kadiv_human_resource` | Kadiv Human Resources |
| `human_resource` | Human Resources |
| `head_of_public_relation` | Head of Public Relation |
| `kadiv_partnership` | Kadiv Partnership |
| `partnership` | Partnership |
| `kadiv_media_relation` | Kadiv Media Relation |
| `media_relation` | Media Relation |
| `head_of_social_media` | Head of Social Media |
| `kadiv_creative` | Kadiv Creative |
| `creative` | Creative |
| `kadiv_video_editor` | Kadiv Video Editor |
| `video_editor` | Video Editor |
| `kadiv_design_and_publishing` | Kadiv Design and Publishing |
| `design_and_publishing` | Design and Publishing |
| `kadiv_web_development` | Kadiv Web Development |
| `web_development` | Web Development |

(Label persis seperti kolom Jabatan di CSV, tanpa angka.)

### Tabel `users`

- Kolom teks `role` diganti `role_id` (foreign key ke `roles.id`, wajib, `restrictOnDelete`).
- Kolom baru `photo_path` (string, nullable).

### Migration data lama

Satu migration baru melakukan, berurutan:

1. Buat tabel `roles` dan isi 25 baris di atas.
2. Tambah `users.role_id` (nullable sementara), isi dari teks lama: `announcer` → `announcer`, `so` → `sound_operator`, `hr` → `human_resource`. Jika ada user dengan nilai lain (mis. `admin`), migration **gagal dengan pesan error** yang menyebut nilai tersebut — tidak ada akun yang diam-diam salah role.
3. Jadikan `role_id` wajib, tambah foreign key, hapus kolom `role`.
4. Tambah `users.photo_path`.
5. Ubah `program_crew.position` dan `session_crew.position` dari `so` ke `sound_operator`.

`down()` membalik semuanya: kembalikan kolom teks `role` dengan nilai lama (`sound_operator` → `so`, `human_resource` → `hr`, `announcer` → `announcer`; role lain → gagal dengan pesan error), posisi crew kembali ke `so`, hapus `photo_path`, `role_id`, dan tabel `roles`.

Migration diverifikasi di SQLite sementara (naik, isi data lama, turun). MySQL milik user tidak disentuh.

### Model

- `Role`: `name`, `label`; `users(): HasMany`.
- `User`: fillable menjadi `username`, `password`, `role_id`, `photo_path`; `role(): BelongsTo`.

## 2. Posisi crew & akses

### `CrewPosition`

- `So` bernilai `sound_operator` (sebelumnya `so`); label tetap.
- Setiap posisi tahu role mana yang boleh mengisinya:
  - Announcer → `kadiv_announcer`, `announcer`
  - Sound Operator → `kadiv_sound_operator`, `sound_operator`
- Kebalikannya juga tersedia: dari role pengguna, dapatkan posisinya (atau tidak ada).

Dipakai di: daftar pilihan crew pada form program & sesi, validasi crew (`ValidatesCrewAssignment`), dan rekap absensi (yang sekarang memakai `whereIn('role', ['announcer','so'])` dan `CrewPosition::from($user->role)`).

### Kelompok area

Tiga area aplikasi tetap: HR (`/hr`, layout `auth.hr.layout`), announcer (`/announcer`, layout `auth.announcer.layout`), SO (`/so`, layout `auth.so.layout`).

Pemetaan role → area ditulis di **satu tempat** dan dipakai oleh: redirect login, pemilihan layout & prefix route di "Sesi saya" (`SesiSayaController`, yang sekarang memakai `'auth.'.$role.'.layout'`), dan halaman Profil.

- `kadiv_human_resource`, `human_resource` → HR
- `kadiv_announcer`, `announcer` → announcer
- `kadiv_sound_operator`, `sound_operator` → SO
- role lain → tidak punya area

### Middleware `role`

Satu middleware berparameter menggantikan `isHr`, `isAnnouncer`, `isSo` (ketiganya dihapus):

- grup `/hr` → `role:kadiv_human_resource,human_resource`
- grup `/announcer` → `role:kadiv_announcer,announcer`
- grup `/so` → `role:kadiv_sound_operator,sound_operator`

Pengguna yang role-nya tidak ada di daftar → 403 (sama seperti sekarang). Memberi akses ke role lain nanti = menambah nama di route.

### Redirect login

Setelah login berhasil: area HR → `hr.dashboard`, announcer → `announcer.dashboard`, SO → `so.sesi.index`, role tanpa area → halaman "Fitur belum tersedia". Cabang `admin` dihapus.

### Tampilan role di sidebar

`@section('role')` di tiap layout (sekarang teks tetap "HR", "Announcer", "Sound operator") menampilkan label role pengguna, mis. "Kadiv Announcer".

## 3. Halaman Profil

Hanya untuk 6 role crew/HR. Semua route dijaga `role:` dengan keenam role tersebut.

| Method | URL | Fungsi |
|---|---|---|
| GET | `/profil` | Tampilkan halaman |
| PUT | `/profil` | Simpan data diri |
| PUT | `/profil/password` | Ganti password |

Halaman memakai layout area pengguna (HR → sidebar HR, dst.). Menu "Profil" ditambahkan di sidebar ketiga layout.

### Form 1 — Data diri (tombol "Simpan profil")

- **Foto profil**: opsional; `jpg`, `png`, `webp`; maks 2 MB. Disimpan di disk `public`, folder `profile-photos/`, path di `users.photo_path`. Foto lama dihapus dari storage saat diganti. Tanpa foto → huruf inisial nama. Foto (atau inisial) juga tampil kecil di sidebar di sebelah nama.
- **Username (nama lengkap)**: wajib, unik (kecuali dirinya sendiri), maks 255 karakter. Catatan di bawah field: nama ini dipakai untuk login.
- **Jabatan**: hanya tampil (label role), tidak bisa diubah.
- Berhasil → kembali ke Profil dengan pesan "Profil disimpan."

### Form 2 — Ganti password (tombol "Ganti password")

- Password saat ini (aturan `current_password`), password baru (wajib, `confirmed`, tanpa minimal panjang), konfirmasi password baru.
- Berhasil → kembali ke Profil dengan pesan "Password diganti." Sesi login lain tidak diputus.

Setiap form memakai error bag sendiri: error password tidak tampil di form data diri, dan sebaliknya. Pesan validasi berbahasa Indonesia, mengikuti form lain di aplikasi.

Setup sekali di mesin user: `php artisan storage:link`.

## 4. Halaman "Fitur belum tersedia"

- `GET /belum-tersedia`, untuk semua pengguna yang login.
- Halaman tanpa sidebar menu: logo, nama & jabatan pengguna, teks "Fitur untuk jabatanmu belum tersedia. Silakan keluar.", tombol Keluar.
- Role tanpa area diarahkan ke sini setelah login.

## 5. Seeder

- `DatabaseSeeder`: akun `HR`/`hr` dengan role `human_resource`, lalu `Gen7MemberSeeder`.
- `Gen7MemberSeeder` ditulis ulang untuk **semua 82 baris** CSV `Data Diri Gen 7 - Data Anggota.csv`, dikelompokkan per `name` role. Hanya kolom Nama lengkap dan Jabatan yang dipakai; kolom data pribadi lain (nomor, tanggal lahir, prodi, Instagram) tidak masuk repo.
- Username = nama lengkap persis seperti CSV, kecuali tiga orang berjabatan ganda:
  - `Noval Rasyah Kurnia Putra (Announcer)` dan `Noval Rasyah Kurnia Putra (Finance)`
  - `Shafa Dhiya Raihanah (Announcer)` dan `Shafa Dhiya Raihanah (Finance)`
  - `Syavirni Dwi Amalia (Sound Operator)` dan `Syavirni Dwi Amalia (Sekretaris)`
- Password semua akun `1234` (`Gen7MemberSeeder::DEFAULT_PASSWORD`).
- Total akun setelah seed: 83 (82 anggota + `HR`).
- Jumlah per role (82 anggota, Sekretaris 1/2 dan Finance 1/2 digabung):

  | role | jumlah | role | jumlah |
  |---|---|---|---|
  | `gm` | 1 | `kadiv_partnership` | 1 |
  | `vgm` | 1 | `partnership` | 4 |
  | `sekretaris` | 2 | `kadiv_media_relation` | 1 |
  | `finance` | 2 | `media_relation` | 4 |
  | `head_of_podcast` | 1 | `head_of_social_media` | 1 |
  | `kadiv_announcer` | 1 | `kadiv_creative` | 1 |
  | `announcer` | 16 | `creative` | 6 |
  | `kadiv_sound_operator` | 1 | `kadiv_video_editor` | 1 |
  | `sound_operator` | 10 | `video_editor` | 6 |
  | `kadiv_human_resource` | 1 | `kadiv_design_and_publishing` | 1 |
  | `human_resource` | 6 (+ akun `HR` = 7) | `design_and_publishing` | 9 |
  | `head_of_public_relation` | 1 | `kadiv_web_development` | 1 |
  | | | `web_development` | 3 |

## 6. Pengujian

### Penyesuaian tes lama

- `UserFactory`: default dan state `hr()`, `announcer()`, `so()` mengisi `role_id` dari tabel `roles` (`human_resource`, `announcer`, `sound_operator`). Tambah cara membuat user dengan role apa pun berdasarkan `name`.
- Tes yang menulis teks role langsung (`'role' => 'announcer'` dsb.) atau posisi `so` disesuaikan: CheckInTest, ProgramTest, SesiTest, AbsensiTest, DatabaseSeederTest.

### Tes baru

- **Akses**: `kadiv_announcer`, `kadiv_sound_operator`, `kadiv_human_resource` bisa membuka area masing-masing. Role `creative` mendapat 403 di `/hr/dashboard`, `/announcer/dashboard`, `/so/sesi`.
- **Login**: Kadiv tiap area diarahkan ke halaman area-nya; role `creative` diarahkan ke halaman "Fitur belum tersedia" dan halaman itu menampilkan tombol Keluar.
- **Crew**: `kadiv_announcer` bisa dipilih sebagai crew announcer di program; user `creative` ditolak validasi.
- **Sesi saya**: `kadiv_announcer` melihat "Sesi saya" dengan layout & route announcer.
- **Profil**:
  - ganti username berhasil; username milik orang lain ditolak.
  - upload foto (`Storage::fake('public')`) tersimpan dan `photo_path` terisi; foto lama terhapus saat diganti; file bukan gambar ditolak.
  - ganti password dengan password saat ini yang benar berhasil dan login dengan password baru bisa; password saat ini yang salah ditolak, error hanya di error bag password.
  - konfirmasi password tidak cocok ditolak.
  - role `creative` mendapat 403 di `/profil`.
- **Seeder**: total 83 akun, jumlah per role sesuai bagian 5, ketiga pasangan akun ganda ada, tidak ada username `Noval Rasyah Kurnia Putra` tanpa akhiran.

## Di luar lingkup

- Hak akses khusus per role (Kadiv vs anggota, GM/VGM) — setelah proyek ini.
- Fitur untuk divisi selain Announcer, Sound Operator, HR.
- Halaman kelola akun/role oleh HR.
- Mengganti URL, nama route, dan folder `hr`/`so`.
- Aturan kekuatan password, memutus sesi lain saat password diganti.
