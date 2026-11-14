# Referensi Arsitektur Absensi RFID (repo radio-web-new)

> Catatan hasil reverse-engineering repo `samuelmahesasinulingga/radio-web-new`
> (CodeIgniter 4). Dipakai sebagai acuan untuk mengimplementasikan absensi
> berbasis tapping RFID ke proyek SPIN (Laravel). Tanggal catat: 2026-11-08.

## Prinsip desain

**Absensi hanya sah lewat tapping kartu RFID — tidak ada input manual lain.**
Satu anggota = satu kartu = satu UID. Semua pencatatan absen berangkat dari UID kartu.

## Rantai alur (end-to-end)

```
Kartu RFID
  → Reader USB-HID (reader dibaca OS sebagai keyboard)
  → AutoHotKey (script di PC: menampung/buffer UID, kirim hanya saat window web absensi aktif)
  → diketik + {Enter} ke <input id="rfid_uid" autofocus> di halaman absensi
  → JS fetch POST JSON {rfid_uid} ke endpoint tap
  → Controller tap(): validasi + simpan ke log
  → DB
```

Catatan penting: **buffer data ADA DI SISI PC (AutoHotKey), bukan di web.**
Web tidak punya antrean/polling/websocket. "Data ditahan dulu, pas web dibuka
baru masuk" = perilaku AHK yang hanya `Send` UID saat window absensi aktif
(`#IfWinActive`).

## Model data (3 tabel)

| Tabel          | Fungsi                       | Kolom inti                                                  |
| -------------- | ---------------------------- | ---------------------------------------------------------- |
| `crew`         | Master anggota               | `id`, `name`, `role` (divisi)                              |
| `rfid_crews`   | Pemetaan kartu → anggota     | `rfid_uid` (PK, varchar), `crew_id` → `crew.id`            |
| `absensi_logs` | Log absen                    | `rfid_uid`, `tanggal` (date), `jam_masuk` (time), `status` |

- Relasi: `absensi_logs.rfid_uid → rfid_crews.rfid_uid → crew.id`.
- `status` = enum(`Hadir`, `Terlambat`, `Pulang`), **tetapi kode selalu simpan `'Hadir'`** (logika terlambat/pulang belum ada).
- Granularitas **per hari** (`tanggal`), bukan per sesi.

## Endpoint & logika inti

Route: `POST|GET /tap-absen` → `Admin\DashboardController::tap()`

Langkah `tap()`:

1. Ambil `rfid_uid` dari GET / POST form / JSON body (3 jalur, buat fleksibel + testing).
2. UID kosong → `{status: error, "UID tidak terbaca"}`.
3. Cari UID di `rfid_crews` (join `crew`). Tidak ada → `{error, "Kartu tidak terdaftar"}`.
4. Cek sudah ada baris `rfid_uid` + `tanggal = hari ini` → `{warning, "Anda sudah absen hari ini"}` (dedup 1x/hari).
5. Simpan `absensi_logs`: `tanggal = today`, `jam_masuk = now`, `status = 'Hadir'`.
6. Balikin `{success, nama, jam}`.

Sisi admin:
- `absensi/crew` → kelola pemetaan kartu ↔ anggota (simpan/update/delete).
- `absensi/history` → riwayat per-UID / semua / hari ini, + **export Excel** (`absensi/export-excel`).

## Kelemahan yang harus ditutup saat implementasi ulang

1. **Endpoint tanpa autentikasi** — siapa pun yang tahu UID bisa POST dan absen atas nama orang lain. Tidak ada token/signature dari alat.
2. **Field masih bisa diketik manual** — selama ada text input terbuka, keyboard biasa bisa isi UID. Belum benar-benar "tapping-only".
3. **Status selalu `Hadir`** — tidak ada hitung keterlambatan, tidak ada jam pulang/checkout.
4. **Satu titik gagal** — bergantung penuh pada PC + reader + script AHK; alat mati = absen mati.
5. **Granular per hari** — tidak kenal sesi/shift; satu hari beberapa siaran tidak terbedakan.

## Pemetaan ke proyek SPIN (Laravel)

SPIN sudah berbasis **sesi**, bukan harian. Yang relevan:

- `App\Models\Attendance` — `broadcast_session_id`, `user_id`, `checked_in_at`, `status`, `note`.
- `App\Enums\AttendanceStatus` — `Hadir`, `Terlambat`, `Izin`, `Sakit`, `TidakHadir`, `Belum`.
- `App\Models\BroadcastSession` — sesi siaran.

Perbedaan kunci radio vs SPIN:

| Aspek        | Radio (`absensi_logs`)        | SPIN (`Attendance`)                                |
| ------------ | ----------------------------- | -------------------------------------------------- |
| Kunci orang  | `rfid_uid`                    | `user_id`                                          |
| Granularitas | per hari (`tanggal`)          | per sesi (`broadcast_session_id`)                  |
| Waktu        | `jam_masuk`                   | `checked_in_at`                                    |
| Status       | enum, tapi hardcode `Hadir`   | enum lengkap, bisa Hadir/Terlambat/dll             |

Langkah implementasi RFID ke SPIN (gambaran, belum dikerjakan):

1. **Tabel pemetaan kartu** baru, mis. `rfid_cards(rfid_uid PK, user_id)` — setara `rfid_crews`.
2. **Endpoint tap** (`POST /tap-absen`) yang:
   - resolve `rfid_uid → user_id` (kartu tak terdaftar → tolak),
   - tentukan **sesi aktif** saat ini (BroadcastSession yang sedang berjalan),
   - buat/update `Attendance` untuk (user, sesi) dengan `checked_in_at = now`,
   - tentukan `status` = `Hadir`/`Terlambat` dari jam mulai sesi (bukan hardcode).
3. **Keamanan** (perbaiki kelemahan di atas):
   - lindungi endpoint dengan token/signature dari alat (bukan andalkan field fokus),
   - kalau tetap pakai field, buat `readonly` + hanya diisi programatik agar benar-benar tapping-only.
4. Manfaatkan yang sudah ada: `AbsensiController`, `AttendanceSpreadsheet` (ekspor), enum status.

## Sumber

Repo: https://github.com/samuelmahesasinulingga/radio-web-new (folder `Radio Project/`).
File inti: `app/Controllers/Admin/DashboardController.php` (`tap()`),
`app/Models/Absensi/*`, `app/Views/admin/absensi/index.php`, `radio_web.sql`.
