# Desain: penugasan crew dipindah dari program ke sesi

Menggantikan bagian penugasan crew di `docs/superpowers/specs/2026-11-20-crud-program-hr-design.md` (crew per program lewat `content_crew`). Bagian lain spec itu (HR mengelola program, jabatan tambahan, jam default) tetap berlaku.

## Tujuan dan alasan

Crew yang bertugas ditentukan per sesi siaran, bukan per program. Pemilik memutuskan tidak ada daftar crew bawaan di program: crew hanya dipilih di sesi, dan tap RFID dicocokkan ke sesi lewat daftar crew sesi itu.

## Keputusan pemilik

- **Opsi B:** crew hanya per sesi, tanpa bawaan dari program (form program kehilangan bagian crew).
- **Data lama dibuang:** tabel `content_crew` dihapus tanpa disalin; sesi yang sudah ada mulai tanpa crew, jadi tap untuk sesi itu menjadi tamu sampai HR mengisi crew-nya.

## Cakupan

1. Tabel penugasan baru sesi↔crew dan relasinya; `content_crew`, `Content::crews()`, `Crew::contents()` dihapus.
2. `BroadcastSession::findForTap` mencari sesi hari itu yang memuat crew tersebut.
3. Tiga dropdown crew (Announcer, Sound Operator, Human Resource; aturan jabatan utama atau tambahan yang sudah ada) pindah dari form program ke modal Tambah dan Ubah sesi.
4. Daftar sesi menampilkan crew tiap sesi.
5. Form program, `PodcastController`, dan seeder dibersihkan dari penugasan crew.

## Bukan tujuan

- Tidak ada penyalinan crew dari program ke sesi, tidak ada migrasi data lama, tidak ada penyalinan sesi.
- Aturan waktu tap tidak berubah (sebelum mulai = tamu; mulai sampai selesai = Hadir; setelah selesai sampai +60 menit = Terlambat; sesudahnya tamu; sesi hari tap saja).
- Log absensi tidak berubah; mengubah crew sesi tidak mengubah log yang sudah ada.
- Jabatan tambahan (`extra_role`), jam default program, tombol "Apply waktu default", dan penjaga hapus program tidak berubah.

## Anti-tujuan

Tap crew yang bertugas menjadi tamu karena daftar crew sesi tidak tersimpan; crew yang tidak berhak (jabatan di luar tiga jabatan) bisa dimasukkan lewat permintaan palsu; dua tempat penyimpan crew yang bisa saling bertentangan.

## Rancangan

- **Skema:** migrasi membuat `broadcast_session_crew` (`broadcast_session_id` dan `crew_id`, keduanya foreign key `cascadeOnDelete`, primary key gabungan) dan menghapus `content_crew`. `down()` membuat `content_crew` kosong dan menghapus tabel baru (isi lama tidak kembali). Penghapusan data ini disetujui pemilik.
- **Model:** `BroadcastSession::crews(): BelongsToMany`; `Crew::broadcastSessions(): BelongsToMany`. `Content::crews()` dan `Crew::contents()` dihapus.
- **Penamaan (keputusan penulis):** `Crew::PROGRAM_ROLES` menjadi `Crew::SESSION_ROLES` dan scope `Crew::canJoinProgram()` menjadi `Crew::canJoinSession()`, karena penugasan kini per sesi. Nilai dan semantik tidak berubah.
- **Aturan sinkronisasi (dipindah ke model):** `BroadcastSession::syncCrews(array $crewIds): void` menyimpan crew terpilih lalu mempertahankan crew sesi itu yang sudah ditugaskan tetapi tidak lagi berhak (tidak lolos `canJoinSession`), memakai `unique()` sehingga id ganda tersimpan sekali; id dipaksa integer.
- **Validasi (`SesiController::validated`):** tambah `crew_ids` (array) dan `crew_ids.*` (integer, ada di `crews` dan lolos `canJoinSession`, yaitu `role` atau `extra_role` termasuk `Crew::SESSION_ROLES`). Tanpa `distinct`. `simpan` dan `update` memanggil `syncCrews` setelah sesi tersimpan. Pembatasan tanggal sesi yang sudah punya absensi tidak berubah; crew boleh diubah di sesi mana pun.
- **`findForTap`:** menyaring sesi hari itu dengan `whereHas('crews', ...)` untuk crew yang mengetuk; sisanya (jendela waktu, prioritas, pemilihan terdekat) tetap.
- **View sesi:** modal Tambah dan Ubah memakai satu partial (`admin/absensi/_crew-dropdowns.blade.php`) berparameter awalan id; dropdown berisi centang, chip, dan cerminan antar dropdown untuk crew di dua jabatan (perilaku yang sama seperti di form program sekarang, JS dipindah ke `sesi.blade.php`, id kotak centang memuat awalan modal agar unik). Tombol Edit membawa id crew sesi untuk mengisi modal Ubah; modal Tambah mulai kosong. Tabel sesi menampilkan kolom "Crew" (nama dipisah koma; kosong bila belum ada).
- **Form program:** bagian crew, badge "N crew ditugaskan", `data-crews`, dan JS crew dihapus dari `admin/podcast.blade.php`. `PodcastController`: aturan `crew_ids`, `prepare()`, `afterSave()` override, dan `crewByRole` dihapus; `index()` tidak lagi memuat `crews`. Hook `afterSave` di `ImageCrudController` tidak punya pemakai lagi sehingga dihapus (keputusan penulis; dipasang untuk penugasan crew program saja).
- **Seeder:** `ProgramCrewSeeder` dihapus dan dikeluarkan dari `DatabaseSeeder`; `DatabaseSeederTest` tidak lagi menegaskan 31 penugasan atau crew K-TIME; asersi jabatan tambahan tiga orang tetap (memakai `canJoinSession`).
- **Dokumen:** `docs/konteks-aplikasi.md` diperbarui (tabel, relasi, aturan tap, form sesi/program, seeder).

## Pendekatan yang dipertimbangkan

- **Crew per sesi saja (dipilih):** satu tempat penyimpan, tap sederhana.
- **Bawaan di program disalin ke sesi (opsi A):** ditolak pemilik; dua tempat penyimpan dan kerja salin.
- **Menyalin data lama ke sesi yang ada:** ditolak pemilik (mulai kosong).

## Tes yang dibutuhkan

- Menambah sesi dengan `crew_ids` menyimpan crew sesi; mengubah menyinkronkan (menambah dan melepas); form tanpa `crew_ids` pada Ubah melepas crew yang berhak dan mempertahankan crew yang tidak lagi berhak; id ganda tersimpan sekali.
- `crew_ids` berisi crew berjabatan di luar tiga jabatan, id tak ada, atau array bersarang ditolak (sesi tidak tersimpan/berubah); crew berjabatan tambahan program diterima.
- Tap: crew yang ditugaskan ke sesi hari ini dan mengetuk di jendela waktu tercatat di sesi itu; crew yang tidak ditugaskan ke sesi mana pun, meski program sama, menjadi tamu; dua sesi hari ini dengan crew sama memakai aturan prioritas yang ada; semua tes aturan waktu yang ada tetap lulus setelah `sessionFor`/`siapkanSesiHariIni` menugaskan crew ke sesi.
- Halaman sesi memuat kolom crew dan dropdown dengan crew yang berhak; menghapus sesi menghapus baris penugasannya; form program tidak memuat dropdown crew dan tidak menerima/menyimpan `crew_ids`.
- Migrasi: `content_crew` tidak ada, `broadcast_session_crew` ada, `down()` bisa dijalankan.

## Diserahkan ke pelaksana

Nama dan bentuk partial dan JS, urutan nama crew di sel tabel, teks kosong, gaya dropdown di modal, cara mengisi modal Ubah (atribut data atau JSON), bentuk kueri `findForTap` selama aturan di atas terpenuhi.
