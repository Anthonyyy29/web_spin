# Catatan

Hal yang sudah diputuskan tetapi belum dikerjakan.

## URL publik memakai ID biasa (belum dikerjakan)

Dicatat 2026-11-15. Keputusan pemilik proyek: nanti URL publik memakai ID biasa,
misalnya `/news/details/5` dan `/listen/content/3`, bukan token samaran seperti di
repo lama (`generateToken()` / `decodeToken()` di `BaseController`).

Alasan: token itu hanya penyamaran, bukan keamanan, dan tidak sesuai konvensi Laravel.

Yang perlu dilakukan saat dikerjakan:
- Hapus method `encodeId` / `decodeId` di `app/Http/Controllers/Controller.php`.
- Ganti pemakaiannya di `HomeController`, `ListenController`, dan `NewsController`
  dengan ID asli (route model binding bila cocok).
- Sesuaikan tes.

Sampai saat itu, jangan menambah pemakaian `encodeId` / `decodeId` di kode baru.
