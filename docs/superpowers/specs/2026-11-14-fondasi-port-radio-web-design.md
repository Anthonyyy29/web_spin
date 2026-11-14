# Fondasi port radio-web ke Laravel — Design

Tanggal: 2026-11-14
Status: menunggu persetujuan

## Latar belakang dan maksud

SPIN dibangun berpusat pada absensi crewcall (program → sesi siaran → check-in).
Arah itu keliru: yang dibutuhkan adalah **website Spin The Radio secara utuh**, dan
absensi hanya salah satu menu di dalamnya.

Acuannya repo `samuelmahesasinulingga/radio-web-new` (CodeIgniter 4, folder
`Radio Project`). Versi Laravel ini **menggantikan website itu seluruhnya** dan
untuk sekarang **meniru isi, alur, dan rupanya**. Perombakan tampilan situs publik
adalah pekerjaan terpisah di kemudian hari.

Dokumen ini hanya merancang **bagian 1 (fondasi)**. Bagian lain dirancang
satu per satu saat gilirannya.

## Peta seluruh pekerjaan

| # | Bagian | Isi |
|---|---|---|
| 1 | **Fondasi** (dokumen ini) | Branch baru, login 3 peran, kerangka dashboard |
| 2 | Absensi (HR) | Tap RFID, tabel hari ini + grafik, riwayat + ekspor Excel, kartu ↔ crew |
| 3 | Admin | Crew, podcast + episode, news, sponsor, ringkasan, log login & audit |
| 4 | Situs publik | Home, Listen, News, Crew, About Us |
| 5 | Lomba | Pendaftaran, pengumpulan karya, dashboard panitia |
| 6 | Pindah data lama | Dari `radio_web.sql` dan file unggahan |

## Tujuan bagian 1

- Titik mulai yang bersih untuk kelima bagian berikutnya.
- Tiga akun (admin, hr, panitia) bisa login dan logout, password di-hash.
- Setelah login, tiap peran masuk ke dashboard dengan kerangka AdminLTE yang
  rupanya sama dengan repo lama.
- Ada cara baku membatasi halaman per peran, siap dipakai bagian 2–5.

## Bukan tujuan

- Fitur apa pun di dalam dashboard (absensi, crew, podcast, dst.).
- Situs publik dan lomba.
- Audit log dan tampilan riwayat login: masuk bagian 3 (pencatatan login-nya
  sendiri sudah di bagian ini, lihat Rancangan).
- Halaman profil / ganti password: repo lama hanya punya tautan "Profile" yang
  tidak berfungsi; tautannya ditiru apa adanya.
- Akun per orang. Tetap tiga akun bersama seperti repo lama.

## Yang tidak boleh terjadi

- Password tersimpan atau ter-commit dalam teks polos (terjadi di repo lama).
- Peran yang satu bisa membuka halaman peran lain.
- Kode atau tabel program/sesi/crewcall ikut terbawa ke branch baru.

## Pendekatan

Tiga cara dipertimbangkan:

1. **Branch baru dari `main`** — mulai dari Laravel bersih; `feat_login` dibiarkan
   sebagai arsip; yang berguna dibawa satu per satu. Migrasi bersih.
2. Lanjut di `feat_login`, hapus yang lama pelan-pelan — hasil akhir sama, kerja
   lebih banyak, riwayat migrasi kusut.
3. Repo baru — kehilangan remote dan pengaturan tanpa keuntungan atas cara 1.

**Dipilih: cara 1**, karena hampir tidak ada kode lama yang ikut terpakai.

Untuk tampilan, tiga cara dipertimbangkan: (a) AdminLTE + Bootstrap persis repo
lama, (b) semua ditulis ulang dengan Tailwind, (c) campur per area.
**Dipilih: (c)** — dashboard AdminLTE; situs publik disalin dengan Bootstrap
sekarang dan baru memakai Tailwind saat dirombak nanti. Aman karena kedua area
memakai layout terpisah dan tidak pernah dimuat di halaman yang sama.

## Keputusan pemilik proyek

- Menggantikan website lama seluruhnya; rupa ditiru dulu.
- Cara 1 (branch baru dari `main`).
- Tampilan (c): AdminLTE untuk dashboard, Tailwind baru nanti di situs publik.
- Stack: Laravel 13 / PHP 8.5, MySQL, Blade biasa, Bootstrap 5.3 + Chart.js +
  SweetAlert2 dari CDN, auth bawaan Laravel, openspout untuk Excel, Pest, Docker.
- **Ikuti repo lama dulu untuk semuanya**: alamat URL, alur, pesan, dan perilaku
  disamakan dengan repo lama. Satu-satunya pengecualian adalah penyimpanan
  password (lihat Data), karena menyimpan teks polos adalah lubang keamanan dan
  tidak terlihat bedanya oleh pengguna.
- Alasan menyukai Tailwind: kode Blade lebih ringkas. Maka potongan AdminLTE yang
  berulang dibungkus jadi komponen Blade supaya view tetap ringkas.

## Rancangan

### Branch dan yang dibawa dari `feat_login`

Branch `port-radio-web` dibuat dari `main`. Dibawa dari `feat_login`:

- `compose.yaml` (Docker).
- Dependensi `openspout/openspout` (dipakai di bagian 2).
- `public/images/logo.png`.
- Perubahan `config/app.php` dan `bootstrap/app.php` yang bukan milik fitur lama
  (mis. zona waktu); yang milik fitur lama ditinggal.
- `docs/` (design lama tetap sebagai catatan; `docs/design.md` diberi keterangan
  bahwa ia menggambarkan tampilan lama yang sudah tidak dipakai).

Tailwind dan Vite dicabut (`package.json`, `vite.config.js`, `resources/css`,
`resources/js`). Tidak ada langkah build. Keduanya dipasang lagi saat perombakan
situs publik.

### Data

Tabel `users` (menggantikan migrasi `users` bawaan starter):

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint | |
| `username` | string(30), unik | dipakai untuk login |
| `password` | string | di-hash (cast `hashed`) |
| `role` | string(10) | `admin`, `hr`, `panitia` |
| `remember_token`, timestamps | | |

Nama tabel memakai konvensi Laravel (`users`, bukan `user` seperti repo lama);
pemetaannya diurus di bagian 6. Password di-hash, berbeda dari repo lama yang
menyimpan teks polos.

Tabel `admin_history`: `ip_address`, `last_login`, `device`, timestamps
(sama dengan repo lama).

Tabel `sessions` bawaan starter dipertahankan (login memakai session database);
tabel `password_reset_tokens` dibuang karena tidak ada fitur lupa password.

Enum `App\Enums\UserRole` dengan kasus `Admin`, `Hr`, `Panitia`; kolom `role`
di-cast ke enum ini.

Seeder membuat tiga akun. Password diambil dari `.env`
(`SEED_ADMIN_PASSWORD`, `SEED_HR_PASSWORD`, `SEED_PANITIA_PASSWORD`); kalau
kosong di luar lingkungan lokal/testing, seeder berhenti dengan pesan jelas.
`.env.example` hanya memuat nama variabelnya.

### Rute

| Metode | Path | Nama | Keterangan |
|---|---|---|---|
| GET | `/` | — | Alihkan ke `/login` (jadi `/home` di bagian 4) |
| GET | `/login` | `login` | Form login; yang sudah login dialihkan ke dashboard |
| POST | `/login/checklogin` | `login.check` | Maksimal 5 percobaan gagal per 60 detik |
| GET | `/dashboard/logout` | `logout` | Sama dengan repo lama (GET) |
| GET | `/dashboard` | `dashboard` | Wajib login |

Pesan kilat mengikuti repo lama: "login berhasil", "login gagal",
"Terlalu banyak percobaan. Coba lagi nanti.", "Anda telah logout.".

Seluruh grup `/dashboard` juga memakai penolak POST beruntun seperti
`ActionValidator` lama: POST kedua dalam 1 detik dari sesi yang sama ditolak
dengan pesan "Too many actions".

Setiap login berhasil dicatat ke tabel `admin_history` (alamat IP, waktu login
terakhir, jenis perangkat) dengan aturan yang sama dengan `AdminHistoryModel`
lama. Tampilannya baru muncul di dashboard admin pada bagian 3.

Middleware `role` (mis. `role:hr`, `role:admin,hr`) menolak dengan 403 bila
peran tidak cocok. Bagian 1 belum punya rute khusus peran selain untuk tes;
middleware disiapkan dan diuji agar bagian 2–5 tinggal memakai.

### Tampilan

- **Login**: menyalin rupa `login.php` lama (Bootstrap dari CDN + `login.css`),
  isian username dan password, pesan gagal/berhasil.
- **Kerangka dashboard**: satu layout Blade yang menyalin `layout/dashboard.php`
  lama: bilah atas (tautan Home, menu pengguna berisi nama peran, Profile, dan
  Sign out),
  sidebar bermerek "Spin Radio Admin" dengan tautan Dashboard, lalu menu sesuai
  peran. Aset `adminlte.min.css` dan `adminlte.min.js` disalin ke
  `public/assets/dashboard/`.
- **Menu sidebar per peran** hanya memuat fitur yang sudah ada. Di bagian 1
  isinya baru "Dashboard"; tiap bagian berikutnya menambah menunya sendiri.
- **Halaman dashboard** di bagian 1: halaman kosong berjudul "Dashboard" dengan
  sapaan sesuai peran. Bagian 2, 3, dan 5 menggantinya dengan isi sebenarnya
  untuk peran masing-masing.

Teks antarmuka mengikuti repo lama (campuran Inggris/Indonesia apa adanya).

### Tes (Pest, feature)

- Tiap peran bisa login dan mendarat di `/dashboard`.
- Password salah ditolak; percobaan keenam dalam semenit ditolak.
- Login berhasil menambah/memperbarui catatan `admin_history`.
- POST kedua dalam 1 detik di grup dashboard ditolak.
- Tamu yang membuka `/dashboard` dialihkan ke `/login`.
- Middleware `role` meloloskan peran yang cocok dan memberi 403 pada yang lain.
- Logout mengakhiri sesi.
- Seeder menghasilkan tiga akun dengan password ter-hash.

## Diserahkan ke pembangun

- Nama kelas controller, middleware, dan komponen Blade.
- Cara persis membawa file dari `feat_login` (checkout per file atau cherry-pick).
- Potongan mana yang layak jadi komponen Blade di bagian ini (minimal layout
  dashboard dan pesan kilat).
