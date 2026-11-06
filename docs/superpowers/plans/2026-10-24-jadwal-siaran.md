# Jadwal Siaran Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** HR mengelola (CRUD) jadwal siaran announcer; announcer melihat jadwalnya sendiri.

**Architecture:** `JadwalSiaranController` (resource, tanpa `show`) di sisi HR dengan satu Form Request untuk store/update yang memuat aturan bentrok. Sisi announcer: satu method baru di `AnnouncerController` yang membaca `auth()->user()->broadcastSchedules()`. Otorisasi sudah ditangani middleware `isHr` / `isAnnouncer` di group route.

**Tech Stack:** Laravel 13, Pest 5, Blade + Tailwind via Vite, MySQL (`.env`).

**Spec:** Jawaban user di percakapan 2026-10-24 (tidak ada dokumen spec).

## Global Constraints

- Tes memakai factory (`User::factory()`, `BroadcastSchedule::factory()`). Factory tidak dihapus.
- Kolom FK adalah `user_id` (bukan `announcer_id`). Relasi: `BroadcastSchedule::announcer()` dan `User::broadcastSchedules()`.
- Kolom tabel `broadcast_schedules`: `user_id`, `program_title`, `broadcast_date`, `start_time`, `end_time`, `notes` (nullable).
- Controller HR di namespace `App\Http\Controllers\auth\hr`; nama route resource tetap `jadwalSiaran.*` (tanpa prefix `hr.`).
- Layout tetap `auth.hr.layout` / `auth.announcer.layout` (Tailwind via `@vite`). Teks UI bahasa Indonesia.
- Pest dipakai untuk tes; jalankan `php artisan test --compact <file>`. Jalankan `vendor/bin/pint --dirty --format agent` setelah mengubah PHP.
- Role announcer = `users.role === 'announcer'`.

## Review Focus

- Jadwal bersebelahan (A 08:00–09:00, B 09:00–10:00, announcer & tanggal sama) harus **diizinkan**, bukan dianggap bentrok.
- Edit jadwal tanpa mengubah jam tidak boleh bentrok dengan dirinya sendiri.
- `end_time` sama dengan atau sebelum `start_time` harus ditolak.
- `user_id` yang bukan announcer (mis. user HR) atau tidak ada harus ditolak.
- Announcer tidak boleh melihat jadwal announcer lain; announcer/guest tidak boleh mengakses route HR.

---

### Task 1: Sesuaikan factory dengan skema

**Files:**
- Modify: `app/Models/User.php` (aktifkan kembali `use HasFactory, Notifiable;`)
- Modify: `database/factories/UserFactory.php`
- Modify: `database/factories/BroadcastScheduleFactory.php`
- Modify: `phpunit.xml` — tambah `<env name="DB_CONNECTION" value="sqlite"/>` dan ubah `DB_DATABASE` ke `:memory:` (tes pakai SQLite in-memory, bukan MySQL Sail)

**Interfaces:**
- Produces:
  - `UserFactory::definition()` → `username` (`fake()->unique()->userName()`), `password` (hash `password`, tetap memakai `static::$password`), `role` `announcer`. Hapus `name`, `email`, `email_verified_at`, `remember_token`, dan state `unverified()` (kolomnya tidak ada di tabel `users`).
  - State `UserFactory::hr(): static` (role `hr`) dan `UserFactory::announcer(): static` (role `announcer`).
  - `BroadcastScheduleFactory::definition()` → `user_id` `User::factory()->announcer()`, `program_title` `fake()->words(3, true)`, `broadcast_date` `fake()->date()`, `start_time` `08:00:00`, `end_time` `09:00:00`, `notes` `null`.

- [ ] **Step 1:** Implementasikan perubahan di atas.
- [ ] **Step 2:** Run: `php artisan tinker --execute 'echo json_encode(App\Models\User::factory()->hr()->make()->toArray()), PHP_EOL, json_encode(App\Models\BroadcastSchedule::factory()->make(["user_id" => 1])->toArray());'` — Expected: `role` `hr` dan atribut jadwal lengkap, tanpa error (`make()` tidak menyentuh database).
- [ ] **Step 3:** `vendor/bin/pint --dirty --format agent`.
- [ ] **Step 4: Commit** — `git add app/Models/User.php database/factories phpunit.xml && git commit -m "feat: factory user dan broadcast schedule"`

### Task 2: Form Request dengan validasi dan aturan bentrok

**Files:**
- Create: `app/Http/Requests/auth/hr/JadwalSiaranRequest.php` (via `php artisan make:request`)
- Test: `tests/Feature/auth/hr/JadwalSiaranTest.php`

**Interfaces:**
- Produces: `JadwalSiaranRequest` — `rules(): array`, `prepareForValidation(): void`, `after(): array`. Setelah validasi `validated()` berisi `user_id`, `program_title`, `broadcast_date`, `start_time`, `end_time` (format `H:i:s`), `notes`.
- Consumes: route parameter `jadwalSiaran` (model `BroadcastSchedule`, null saat create) untuk mengecualikan diri sendiri dari cek bentrok.

Aturan:
- `user_id`: required, `exists:users,id` dengan `where role = announcer`.
- `program_title`: required, string, max 255. `broadcast_date`: required, date. `notes`: nullable, string.
- `start_time`: required, `date_format:H:i`. `end_time`: required, `date_format:H:i`, `after:start_time`.
- `prepareForValidation` menormalkan jam ke `H:i:s` setelah validasi format agar perbandingan string dengan kolom `time` konsisten (jam bersebelahan tidak dianggap bentrok).
- `after()` menambah error pada `start_time` jika ada jadwal lain milik `user_id` yang sama, `broadcast_date` sama, dengan `existing.start_time < new.end_time AND existing.end_time > new.start_time`, mengecualikan id `jadwalSiaran` bila ada. Pesan: `Jadwal bentrok dengan jadwal lain milik announcer ini.`

- [ ] **Step 1: Tulis tes gagal** di `JadwalSiaranTest.php` (pakai `RefreshDatabase`, user HR via `User::factory()->hr()->create()`, jadwal lain via `BroadcastSchedule::factory()`, `actingAs`). Test (POST ke `route('jadwalSiaran.store')`):
  - `it rejects a schedule that overlaps another for the same announcer and date` → `assertSessionHasErrors('start_time')`
  - `it allows adjacent schedules for the same announcer` (08:00–09:00 lalu 09:00–10:00) → `assertSessionHasNoErrors()`
  - `it allows the same time slot for a different announcer`
  - `it rejects end time that is not after start time` → `assertSessionHasErrors('end_time')`
  - `it rejects user_id that is not an announcer` → `assertSessionHasErrors('user_id')`
- [ ] **Step 2:** Run: `php artisan test --compact tests/Feature/auth/hr/JadwalSiaranTest.php` — Expected: FAIL (store belum ada).
- [ ] **Step 3:** Implementasi `JadwalSiaranRequest` sesuai aturan di atas (`authorize()` return `true`; akses sudah dijaga middleware).
- [ ] **Step 4:** Tes ini baru lulus setelah Task 3 (store ada); lanjut ke Task 3, lalu jalankan ulang.

### Task 3: Controller HR + route + view CRUD

**Files:**
- Modify: `app/Http/Controllers/auth/hr/JadwalSiaranController.php`, `routes/web.php`, `resources/views/auth/hr/layout.blade.php` (link sidebar `Jadwal Siaran` → `route('jadwalSiaran.index')`)
- Modify: `resources/views/auth/hr/jadwalSiaran/index.blade.php`
- Create: `resources/views/auth/hr/jadwalSiaran/create.blade.php`, `edit.blade.php`, `_form.blade.php`
- Test: `tests/Feature/auth/hr/JadwalSiaranTest.php`

**Interfaces:**
- Consumes: `JadwalSiaranRequest` (Task 2).
- Produces (route name → method): `jadwalSiaran.index` → `index(): View` (variabel `$jadwalSiaran`, `BroadcastSchedule::with('announcer')->orderByDesc('broadcast_date')->orderBy('start_time')->paginate(15)`); `create(): View` (variabel `$announcers` = user role announcer urut `username`); `store(JadwalSiaranRequest): RedirectResponse`; `edit(BroadcastSchedule $jadwalSiaran): View`; `update(JadwalSiaranRequest, BroadcastSchedule $jadwalSiaran): RedirectResponse`; `destroy(BroadcastSchedule $jadwalSiaran): RedirectResponse`. Setelah store/update/destroy redirect ke `jadwalSiaran.index` dengan flash `status`.

- [ ] **Step 1: Tambah tes gagal** di `JadwalSiaranTest.php`:
  - `it lets hr create a schedule` → `assertRedirect(route('jadwalSiaran.index'))` + `assertDatabaseHas('broadcast_schedules', [...])`
  - `it lists schedules with announcer username on the index` → `assertSee` judul program dan username
  - `it lets hr update a schedule without conflicting with itself` (kirim jam yang sama) → tanpa error, data berubah
  - `it lets hr delete a schedule` → `assertDatabaseMissing`
  - `it forbids announcer and redirects guest from hr schedule routes` → announcer `assertForbidden()`, guest `assertRedirect(route('login'))`
- [ ] **Step 2:** Run tes — Expected: FAIL.
- [ ] **Step 3:** Ubah route menjadi `Route::resource('jadwalSiaran', JadwalSiaranController::class)->except('show');`. Implementasikan controller dengan signature di atas (hapus method `show`), view tabel (kolom: tanggal, jam, program, announcer, aksi edit/hapus; paginasi `{{ $jadwalSiaran->links() }}`; tombol "Tambah Jadwal"), form create/edit berbagi `_form.blade.php` (select announcer, input `date`, `time`, text, textarea; tampilkan error per field; `old()`), hapus lewat form `@method('DELETE')`. Tampilkan flash `status` di `index`.
- [ ] **Step 4:** Run: `php artisan test --compact tests/Feature/auth/hr/JadwalSiaranTest.php` — Expected: PASS (termasuk tes Task 2).
- [ ] **Step 5:** `npm run build` agar view ber-Tailwind tidak error manifest; `vendor/bin/pint --dirty --format agent`.
- [ ] **Step 6: Commit** — `git commit -m "feat: crud jadwal siaran hr"`

### Task 4: Halaman jadwal untuk announcer

**Files:**
- Modify: `app/Http/Controllers/auth/announcer/AnnouncerController.php`, `routes/web.php`, `resources/views/auth/announcer/layout.blade.php` (link sidebar)
- Create: `resources/views/auth/announcer/jadwalSiaran/index.blade.php`
- Test: `tests/Feature/auth/announcer/JadwalSiaranTest.php`

**Interfaces:**
- Produces: route `GET announcer/jadwalSiaran` bernama `announcer.jadwalSiaran` → `AnnouncerController::jadwalSiaran(): View` dengan variabel `$jadwalSiaran` = `auth()->user()->broadcastSchedules()->orderByDesc('broadcast_date')->orderBy('start_time')->paginate(15)`.

- [ ] **Step 1: Tes gagal:**
  - `it shows only the signed in announcer's schedules` → `assertSee` judul milik sendiri, `assertDontSee` judul milik announcer lain
  - `it forbids hr from the announcer schedule page` → `assertForbidden()`
- [ ] **Step 2:** Run tes — Expected: FAIL (route belum ada).
- [ ] **Step 3:** Tambah route di group announcer, method controller, view tabel baca-saja (tanggal, jam, program, catatan), dan ubah link sidebar `Jadwal Siaran`.
- [ ] **Step 4:** Run: `php artisan test --compact tests/Feature/auth` — Expected: PASS. Lalu `vendor/bin/pint --dirty --format agent`.
- [ ] **Step 5: Commit** — `git commit -m "feat: jadwal siaran announcer"`

---

## Self-Review

- Cakupan: HR CRUD (Task 3), tolak bentrok (Task 2), tabel + halaman create/edit terpisah tanpa show (Task 3), announcer lihat miliknya (Task 4), factory untuk tes (Task 1, dipakai Task 2–4).
- Konsistensi nama: `jadwalSiaran` (route/param/variabel), `JadwalSiaranRequest`, `user_id`.
- Review Focus: bersebelahan & edit-diri → Task 2/3; end ≤ start, non-announcer → Task 2; isolasi akses → Task 3/4.
