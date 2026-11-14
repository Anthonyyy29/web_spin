# Role per Jabatan & Halaman Profil Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Pindahkan role user ke tabel `roles` (satu role per jabatan Gen 7), beri akun ke semua anggota, dan tambahkan halaman Profil untuk crew/HR serta halaman "Fitur belum tersedia" untuk jabatan lain.

**Architecture:** Tabel `roles` (diisi migration) direferensikan `users.role_id`. Dua enum memegang pemetaan role → kegunaan: `CrewPosition` (role mana yang boleh jadi crew announcer/SO) dan enum baru `Area` (role mana yang masuk area hr/announcer/so, plus layout, prefix route, dan halaman awal). Akses route memakai satu middleware berparameter `role:`.

**Tech Stack:** Laravel 13.36, PHP 8.5, Pest, Blade + Tailwind v4 (Vite), SQLite in-memory untuk tes.

**Spec:** `docs/superpowers/specs/2026-10-24-role-jabatan-profil-design.md`

## Global Constraints

- Commit atas nama git user yang ada (ezra), **tanpa** trailer Co-Authored-By atau penyebutan Claude/AI. Jangan pernah `git add docs/` atau `.superpowers/`.
- Jangan jalankan migration ke MySQL milik user. Verifikasi migration hanya di file SQLite sementara di scratchpad.
- Jangan membuat folder dasar baru selain yang sudah ada. Jangan menambah dependency.
- Teks UI dan pesan validasi berbahasa Indonesia, sentence case, mengikuti form yang sudah ada.
- URL `/hr`, `/announcer`, `/so`, nama route `hr.*`/`announcer.*`/`so.*`, dan folder `auth/hr`, `auth/so` tidak diganti.
- Nilai role (`roles.name`) dan label persis seperti tabel di spec bagian 1. Posisi crew: `announcer`, `sound_operator`.
- Setelah mengubah file PHP: `vendor/bin/pint --dirty --format agent`.
- Jalankan tes dengan `php artisan test --compact <path>`; seluruh suite di akhir tiap task.
- Gunakan `php artisan make:* --no-interaction` untuk file baru (migration, model, middleware, request, controller, test).

## Review Focus

1. Kadiv Announcer yang ditugaskan di sesi harus bisa check-in lewat `/announcer/sesi` dengan layout announcer — dites di Task 1.
2. Username dengan tanda kurung (`Noval Rasyah Kurnia Putra (Announcer)`) harus bisa login — dites di Task 3.
3. Menyimpan profil tanpa mengubah username (username sama dengan miliknya sendiri) tidak boleh ditolak "sudah dipakai" — dites di Task 4.
4. Menyimpan profil tanpa memilih foto baru tidak boleh menghapus foto lama — dites di Task 4.
5. Crew program/sesi yang tersimpan `so` sebelum migration tetap tampil sebagai SO setelah migration, dan rollback mengembalikannya — diverifikasi manual di Task 1 Step 9.

---

### Task 1: Tabel roles, role_id, posisi sound_operator, middleware role

Perubahan skema ini memutus semua pembaca kolom `users.role`, jadi semua pembaca diganti dalam task yang sama supaya suite tetap hijau.

**Files:**
- Create: `database/migrations/<timestamp>_create_roles_table_and_move_users_to_role_id.php` (via `php artisan make:migration create_roles_table_and_move_users_to_role_id`)
- Create: `app/Models/Role.php`
- Create: `app/Enums/Area.php`
- Create: `app/Http/Middleware/EnsureUserHasRole.php`
- Delete: `app/Http/Middleware/isHr.php`, `isAnnouncer.php`, `isSo.php`
- Modify: `app/Models/User.php`, `app/Enums/CrewPosition.php`, `database/factories/UserFactory.php`, `bootstrap/app.php`, `routes/web.php`
- Modify: `app/Http/Controllers/UserController.php` (redirect login)
- Modify: `app/Http/Controllers/auth/crew/SesiSayaController.php`
- Modify: `app/Http/Requests/auth/hr/Concerns/ValidatesCrewAssignment.php`
- Modify: `app/Http/Controllers/auth/hr/ProgramController.php` (baris 39, 84, 143-144), `SesiController.php` (210-212), `AbsensiController.php` (101, 123, 164, 371-376, 389-393)
- Modify: `resources/views/auth/hr/absensi/people.blade.php:49`, `resources/views/auth/hr/sesi/index.blade.php:85`
- Modify: `database/seeders/DatabaseSeeder.php`, `database/seeders/Gen7MemberSeeder.php` (hanya agar memakai `role_id`; isi anggota diganti di Task 3)
- Test: `tests/Feature/auth/RoleAccessTest.php` (baru); sesuaikan `tests/Feature/auth/crew/CheckInTest.php`, `tests/Feature/auth/hr/ProgramTest.php`, `SesiTest.php`, `AbsensiTest.php`, `tests/Feature/Models/AbsensiSchemaTest.php`, `ProgramSyncSessionsTest.php`, `tests/Feature/DatabaseSeederTest.php`

**Interfaces:**
- Produces:
  - `App\Models\Role` — fillable `name`, `label`; `users(): HasMany`.
  - `User::role(): BelongsTo` (property `$user->role` sekarang model `Role`); fillable `username`, `password`, `role_id`, `photo_path`.
  - `User::crewPosition(): ?CrewPosition`, `User::area(): ?Area`.
  - Scope `User::withRoles(list<string> $names)` (atribut `#[Scope]`) — filter user yang `role.name` ada di daftar.
  - `CrewPosition::So->value === 'sound_operator'`; `CrewPosition::roleNames(): list<string>`; `CrewPosition::forRoleName(string $name): ?self`.
  - `enum Area: string { Hr = 'hr'; Announcer = 'announcer'; So = 'so'; }` dengan `roleNames(): list<string>`, `layout(): string` (`'auth.'.$this->value.'.layout'`), `homeRoute(): string` (`hr.dashboard`, `announcer.dashboard`, `so.sesi.index`), `static forRoleName(string $name): ?self`.
  - Middleware alias `role` → `EnsureUserHasRole::handle(Request $request, Closure $next, string ...$roleNames): Response`, 403 jika belum login atau `role.name` tidak ada di daftar.
  - `UserFactory::role(string $name): static`; state `hr()`, `announcer()`, `so()` memakai `human_resource`, `announcer`, `sound_operator`; default `announcer`.

Pemetaan role (persis):

| Enum case | roleNames() |
|---|---|
| `CrewPosition::Announcer`, `Area::Announcer` | `kadiv_announcer`, `announcer` |
| `CrewPosition::So`, `Area::So` | `kadiv_sound_operator`, `sound_operator` |
| `Area::Hr` | `kadiv_human_resource`, `human_resource` |

- [ ] **Step 1: Tulis tes akses yang gagal** di `tests/Feature/auth/RoleAccessTest.php` (`php artisan make:test --pest auth/RoleAccessTest`, `uses(RefreshDatabase::class)`):

```php
it('lets each kadiv into their own area', function (string $role, string $url) {
    $this->actingAs(User::factory()->role($role)->create())->get($url)->assertOk();
})->with([
    ['kadiv_human_resource', '/hr/dashboard'],
    ['kadiv_announcer', '/announcer/dashboard'],
    ['kadiv_sound_operator', '/so/sesi'],
]);

it('keeps a role without an area out of every area', function (string $url) {
    $this->actingAs(User::factory()->role('creative')->create())->get($url)->assertForbidden();
})->with(['/hr/dashboard', '/announcer/dashboard', '/so/sesi']);

it('redirects each kadiv to their area after login', function (string $role, string $routeName) {
    $user = User::factory()->role($role)->create(['password' => 'secret-pass']);

    $this->post(route('login'), ['username' => $user->username, 'password' => 'secret-pass'])
        ->assertRedirect(route($routeName));
})->with([
    ['kadiv_human_resource', 'hr.dashboard'],
    ['kadiv_announcer', 'announcer.dashboard'],
    ['kadiv_sound_operator', 'so.sesi.index'],
]);

it('lets a kadiv announcer check in from the announcer sessions page', function () {
    // Program + BroadcastSession factory seperti di CheckInTest; attach kadiv dengan position CrewPosition::Announcer->value.
    // travelTo 07:20 di hari sesi (sama dengan tes 'lets an announcer check in on time').
    // GET route('announcer.sesi.index') → assertOk, assertSee nama program.
    // POST route('announcer.sesi.checkIn', $session) → assertSessionHas('status', 'Check-in tercatat: Hadir 07:20.').
});

it('offers kadiv crew in the program form and rejects other roles', function () {
    // hr = User::factory()->hr(); kadivAnnouncer = role('kadiv_announcer'); kadivSo = role('kadiv_sound_operator'); creative = role('creative').
    // GET route('program.create') sebagai hr → assertSee username kadivAnnouncer dan kadivSo, assertDontSee username creative.
    // POST route('program.store') dengan payload valid (salin dari ProgramTest) tapi announcer_ids = [creative->id]
    //   → assertSessionHasErrors('announcer_ids.0').
    // POST yang sama dengan announcer_ids = [kadivAnnouncer->id], so_ids = [kadivSo->id]
    //   → sukses; program->crew pivot position kadivSo === 'sound_operator'.
});
```

Isi dua tes terakhir dengan factory/payload yang sudah dipakai di `CheckInTest` dan `ProgramTest`; assertion-nya yang di komentar.

- [ ] **Step 2: Jalankan untuk melihatnya gagal**

Run: `php artisan test --compact tests/Feature/auth/RoleAccessTest.php`
Expected: FAIL (`role()` tidak ada di factory / kolom `role_id` tidak ada).

- [ ] **Step 3: Migration** — `up()` berurutan, sesuai spec bagian 1 "Migration data lama":
  1. `Schema::create('roles')`: `id`, `string('name')->unique()`, `string('label')`, `timestamps()`; insert 25 baris (name/label persis tabel spec) dengan `created_at`/`updated_at` = `now()`.
  2. Gagal lebih dulu bila ada `users.role` di luar `announcer`, `so`, `hr`: `throw new RuntimeException('Role user tidak dikenal: '.implode(', ', $unknown))`.
  3. `foreignId('role_id')->nullable()->after('password')->constrained()->restrictOnDelete()`; isi dengan pemetaan `announcer→announcer`, `so→sound_operator`, `hr→human_resource`; lalu `->nullable(false)->change()`; `dropColumn('role')`.
  4. `string('photo_path')->nullable()->after('role_id')`.
  5. `program_crew.position` dan `session_crew.position`: `so` → `sound_operator`.

  `down()` membalik: posisi `sound_operator → so`; tambah `string('role')->nullable()`, isi dari `roles.name` (`announcer→announcer`, `sound_operator→so`, `human_resource→hr`; nama lain → `RuntimeException('Role tidak bisa dikembalikan: ...')` sebelum menyentuh apa pun), jadikan tidak nullable; `dropColumn('photo_path')`; `dropConstrainedForeignId('role_id')`; `dropIfExists('roles')`.

  Isi (pembaruan data) memakai `DB::table(...)`, bukan model, supaya migration tidak bergantung pada kode model.

- [ ] **Step 4: Model, enum, middleware, factory** — terapkan Interfaces di atas. `bootstrap/app.php`: `$middleware->alias(['role' => EnsureUserHasRole::class]);`. `routes/web.php`: grup hr `['auth', 'role:kadiv_human_resource,human_resource']`, announcer `['auth', 'role:kadiv_announcer,announcer']`, so `['auth', 'role:kadiv_sound_operator,sound_operator']`; hapus import `isHr`/`isAnnouncer`/`isSo` dan hapus ketiga file middleware itu. `UserFactory` mengambil `role_id` dengan `Role::where('name', $name)->value('id')` di dalam closure (tabel `roles` sudah terisi oleh migration di setiap tes).

- [ ] **Step 5: Ganti semua pembaca `role` dan `'so'`**
  - `UserController::login`: redirect ke `route($user->area()->homeRoute())` bila `area()` tidak null; sementara (sampai Task 2) role tanpa area → `redirect('/')`. Cabang `admin` dihapus.
  - `SesiSayaController::index`: `'layout' => $user->area()->layout()`, `'routePrefix' => $user->area()->value` (route `/announcer` dan `/so` menjamin area tidak null).
  - `ValidatesCrewAssignment::crewRules`: `Rule::exists('users', 'id')->whereIn('role_id', <id role untuk CrewPosition::X->roleNames()>)` — ambil id via `Role::whereIn('name', ...)->pluck('id')->all()`.
  - `ProgramController` / `SesiController` `formData()`: `User::withRoles(CrewPosition::Announcer->roleNames())` dan `...So->roleNames()`; literal `'so'` di baris 39, 84, 210 → `CrewPosition::So->value`; `'announcer'` → `CrewPosition::Announcer->value`.
  - `AbsensiController`: `positionOrder()` match pada `CrewPosition::Announcer->value` / `CrewPosition::So->value`; `summaryRows()` ambil `User::withRoles([...Announcer->roleNames(), ...So->roleNames()])->with('role')`, urutkan di koleksi dengan `[positionOrder($user->crewPosition()?->value), username]` (pengganti `orderBy('role')`); kolom "Role" di CSV per orang (baris 123) dan `people.blade.php:49` → `$user->crewPosition()->label()`.
  - `resources/views/auth/hr/sesi/index.blade.php:85`: `'so'` → `\App\Enums\CrewPosition::So->value`.
  - `DatabaseSeeder` & `Gen7MemberSeeder`: isi `role_id` lewat `Role::where('name', ...)`; key `MEMBERS` jadi `announcer`, `sound_operator`, `human_resource`; akun `HR` memakai `human_resource`. (Pemisahan Kadiv dan anggota lain di Task 3.)

- [ ] **Step 6: Sesuaikan tes lama**
  - `CheckInTest.php:20`: `['position' => $user->crewPosition()->value]`.
  - Semua `'position' => 'so'` / `wherePivot('position', 'so')` / `->toBe('so')` di SesiTest, AbsensiTest, AbsensiSchemaTest, ProgramSyncSessionsTest, ProgramTest → `'sound_operator'`.
  - `AbsensiTest.php:19`: closure `crewUser` memakai `User::factory()->role($role)`, dengan nilai `'so'` di pemanggil (baris 45, 334, dst.) diganti `'sound_operator'`.
  - `DatabaseSeederTest`: hitung lewat `User::withRoles([...])`; nilai yang sama dengan sekarang (17 announcer, 11 sound_operator, 8 human_resource).

- [ ] **Step 7: Jalankan tes task**

Run: `php artisan test --compact tests/Feature/auth/RoleAccessTest.php`
Expected: PASS

- [ ] **Step 8: Jalankan seluruh suite + pint**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact`
Expected: semua PASS; `grep -rn "->role !==\|->role ===\|where('role'\|whereIn('role'" app` tidak menghasilkan apa-apa.

- [ ] **Step 9: Verifikasi migration data lama di SQLite sementara** (scratchpad, bukan MySQL):

```bash
DB=<scratchpad>/role-migration.sqlite; rm -f $DB; touch $DB
export DB_CONNECTION=sqlite DB_DATABASE=$DB
php artisan migrate --no-interaction
php artisan migrate:rollback --step=1 --no-interaction
sqlite3 $DB "insert into users(username,password,role,created_at,updated_at) values ('a','x','announcer',datetime(),datetime()),('s','x','so',datetime(),datetime()),('h','x','hr',datetime(),datetime());"
# + satu program, satu program_crew & session_crew dengan position 'so' untuk user 's'
php artisan migrate --no-interaction
sqlite3 $DB "select u.username, r.name from users u join roles r on r.id=u.role_id; select position from program_crew; select position from session_crew;"
php artisan migrate:rollback --step=1 --no-interaction
sqlite3 $DB "select username, role from users; select position from program_crew; select position from session_crew;"
```

Expected: setelah migrate → `a|announcer`, `s|sound_operator`, `h|human_resource`, posisi `sound_operator`; setelah rollback → `a|announcer`, `s|so`, `h|hr`, posisi `so`. Tambah satu baris user `role='admin'` lalu migrate → gagal dengan `Role user tidak dikenal: admin`. Hapus file scratch setelahnya.

- [ ] **Step 10: Commit**

```bash
git add app bootstrap database routes resources tests
git status --short   # pastikan tidak ada docs/ atau .superpowers/
git commit -m "feat: pindahkan role ke tabel roles dan middleware role"
```

---

### Task 2: Halaman "Fitur belum tersedia" dan label jabatan di sidebar

**Files:**
- Create: `resources/views/auth/belum-tersedia.blade.php`
- Modify: `routes/web.php`, `app/Http/Controllers/UserController.php`
- Modify: `resources/views/auth/layout.blade.php`, `resources/views/auth/hr/layout.blade.php`, `resources/views/auth/announcer/layout.blade.php`, `resources/views/auth/so/layout.blade.php`
- Test: `tests/Feature/auth/RoleAccessTest.php`

**Interfaces:**
- Consumes: `User::area()`, `Area::homeRoute()`, `$user->role->label` (Task 1).
- Produces: route `GET /belum-tersedia` bernama `unavailable` (middleware `auth`), dirender lewat `Route::view`.

- [ ] **Step 1: Tulis tes yang gagal** di `RoleAccessTest.php`:

```php
it('sends a role without an area to the unavailable page after login', function () {
    $user = User::factory()->role('creative')->create(['password' => 'secret-pass']);

    $this->post(route('login'), ['username' => $user->username, 'password' => 'secret-pass'])
        ->assertRedirect(route('unavailable'));
});

it('shows the unavailable page with the job title and a logout button', function () {
    $user = User::factory()->role('kadiv_creative')->create();

    $this->actingAs($user)->get(route('unavailable'))
        ->assertOk()
        ->assertSee($user->username)
        ->assertSee('Kadiv Creative')
        ->assertSee('Fitur untuk jabatanmu belum tersedia. Silakan keluar.')
        ->assertSee(route('logout'), false)
        ->assertSee('Keluar');
});

it('requires login for the unavailable page', function () {
    $this->get(route('unavailable'))->assertRedirect(route('login'));
});

it('shows the job title label in the sidebar', function () {
    $this->actingAs(User::factory()->role('kadiv_announcer')->create())
        ->get('/announcer/dashboard')
        ->assertSee('Kadiv Announcer');
});
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/RoleAccessTest.php`
Expected: FAIL (`Route [unavailable] not defined`).

- [ ] **Step 3: Implementasi**
  - `routes/web.php`: `Route::view('/belum-tersedia', 'auth.belum-tersedia')->middleware('auth')->name('unavailable');`
  - `UserController::login`: role tanpa area → `redirect()->route('unavailable')` (ganti `redirect('/')` dari Task 1).
  - `belum-tersedia.blade.php`: dokumen HTML sendiri (bukan `@extends('auth.layout')`, karena tanpa nav), `@vite` sama dengan `auth/layout.blade.php`, `<title>Fitur belum tersedia - Spin The Radio</title>`, latar `bg-canvas`, kartu putih di tengah berisi logo (`images/logo.png`), username, label jabatan, kalimat persis `Fitur untuk jabatanmu belum tersedia. Silakan keluar.`, dan form POST `logout` dengan tombol utama `Keluar` (gaya tombol `bg-signal-strong` seperti di `absensi/program.blade.php`).
  - `auth/layout.blade.php:23`: `@yield('role')` → `{{ Auth::user()->role->label }}`; hapus `@section('role', ...)` dari ketiga layout area.

- [ ] **Step 4: Jalankan tes**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact`
Expected: semua PASS.

- [ ] **Step 5: Commit**

```bash
git add routes app resources tests
git commit -m "feat: halaman fitur belum tersedia untuk jabatan tanpa area"
```

---

### Task 3: Seeder semua anggota Gen 7

**Files:**
- Modify: `database/seeders/Gen7MemberSeeder.php`, `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/DatabaseSeederTest.php`

**Interfaces:**
- Consumes: tabel `roles` (Task 1).
- Produces: `Gen7MemberSeeder::DEFAULT_PASSWORD = '1234'` (tetap); `MEMBERS` bertipe `array<string, list<string>>` dengan key `roles.name`.

Isi `MEMBERS` (persis; urutan nama dalam satu role bebas):

| role | username |
|---|---|
| `gm` | Nurul Afifah Azzahra |
| `vgm` | Fitria Attira |
| `sekretaris` | Vitroh Maviroh; Syavirni Dwi Amalia (Sekretaris) |
| `finance` | Shafa Dhiya Raihanah (Finance); Noval Rasyah Kurnia Putra (Finance) |
| `head_of_podcast` | Allea De Chaelyn Tomo Lawa |
| `kadiv_announcer` | Ivanda Leona Putri Vabita |
| `announcer` | Ashfaa Atsqya Fernando; Belva Vanya Aulia; Kahlil Al Fathir; Kimberly Valencia; Lalia Alina; Nail Khairi Nur Hadi; Nisrina Ihda Jamaliah; Noval Rasyah Kurnia Putra (Announcer); Priscilla Christiana Nahadin; Rael Nathania Wibowo; Saskia Oktaviani; Shafa Dhiya Raihanah (Announcer); Sheriel Zelda Fitriani Cholid; Shezza Nadyra Hergianto; Shiren Rizkia fahira; Yariza Nafisha Hamzah |
| `kadiv_sound_operator` | Salwa Syafira |
| `sound_operator` | Ahmad Faris Firta Budiman; Audrey Geovanni Rionald Sinaga; Bimo Septiansyah; Dhini Aulya; Fatyanheiza Putri Cahyadi; Meirina Kartika Rahmah; Raka Desnoel Sr; Syavirni Dwi Amalia (Sound Operator); Yohanes Dimas Restu Putra; Zahra Dinda Rahma Dani |
| `kadiv_human_resource` | Satriani |
| `human_resource` | Anggraini Dwina Putri; Azza Nabila Assaida; Dewi Novita Sari; Feby Kumala; Keisha Ann Aida Rahman; Yoga Nanda Saputra |
| `head_of_public_relation` | Nayla Agis Maulida |
| `kadiv_partnership` | Putri Nabila |
| `partnership` | Azzahra Putri Anastasya; Calysta Jauza Faris; Divta Dewani Sunandar; Shabrina Hilmi Amanu |
| `kadiv_media_relation` | Najla Safira Balqis |
| `media_relation` | Fahri Sohail Risay; Rizma Ayu Revalina; Salwa Azzahra Zein; Zafira Jua Santika |
| `head_of_social_media` | Miftah Faradilah |
| `kadiv_creative` | Syalloma Zidna Azzahra |
| `creative` | Farah Syifa Annisa Ramadhani; Ghistyanata Utomo; Khaira Meizahra; Nur Annissa; Salsabila Putri Azhari; Sayla Ahmad Sumantri Ownie |
| `kadiv_video_editor` | Lidya Damayanti |
| `video_editor` | Abdul Zani; Andhika Efran Nurdiansah; Farhan Athallah Shalfianas; Inas Nahiza; Keyro Haqqu Hemiyari; Shofie Queen Hillul Latifah |
| `kadiv_design_and_publishing` | Chelsea Alya Mukhbita Widyadhana |
| `design_and_publishing` | Aldelvine Zahyan Annurin; Anisya Nurfaidah; Calvien Agustian Tacazily; Devie Damayanti; Fathin Nayla Putri; Luthfia Malika; Shevia Dwi Lestari; Zahra Aulia Nafizza; Zhafira Ramadhani |
| `kadiv_web_development` | Adi Ezra Anthoni |
| `web_development` | Bimo Rajendra Fassah; Rafdi Muliawan; Rayya Mahira |

Docblock `MEMBERS` diperbarui: anggota Gen 7 per role; orang berjabatan ganda punya satu akun per jabatan dengan label jabatan dalam kurung.

- [ ] **Step 1: Tulis ulang tes seeder yang gagal** di `DatabaseSeederTest.php`:

```php
it('seeds one account per gen 7 member job plus the hr account', function () {
    $this->seed();

    $counts = User::query()->join('roles', 'roles.id', '=', 'users.role_id')
        ->selectRaw('roles.name, count(*) as total')->groupBy('roles.name')->pluck('total', 'roles.name')->all();

    expect(User::count())->toBe(83)
        ->and($counts)->toEqual([ /* 25 pasangan role => jumlah persis tabel spec bagian 5; human_resource => 7 */ ])
        ->and(User::where('username', 'HR')->sole()->role->name)->toBe('human_resource')
        ->and(Program::count())->toBe(0);
});

it('gives members with two jobs one account per job', function () {
    $this->seed();

    expect(User::where('username', 'Noval Rasyah Kurnia Putra (Announcer)')->sole()->role->name)->toBe('announcer')
        ->and(User::where('username', 'Noval Rasyah Kurnia Putra (Finance)')->sole()->role->name)->toBe('finance')
        ->and(User::where('username', 'Shafa Dhiya Raihanah (Announcer)')->sole()->role->name)->toBe('announcer')
        ->and(User::where('username', 'Shafa Dhiya Raihanah (Finance)')->sole()->role->name)->toBe('finance')
        ->and(User::where('username', 'Syavirni Dwi Amalia (Sound Operator)')->sole()->role->name)->toBe('sound_operator')
        ->and(User::where('username', 'Syavirni Dwi Amalia (Sekretaris)')->sole()->role->name)->toBe('sekretaris')
        ->and(User::where('username', 'Noval Rasyah Kurnia Putra')->exists())->toBeFalse();
});

it('lets a member log in with a username that has a job suffix', function () {
    $this->seed();

    $this->post(route('login'), ['username' => 'Noval Rasyah Kurnia Putra (Announcer)', 'password' => Gen7MemberSeeder::DEFAULT_PASSWORD])
        ->assertRedirect(route('announcer.dashboard'));
});
```

Tes login Priscilla yang sudah ada dipertahankan. Array `$counts` ditulis lengkap dari tabel spec bagian 5.

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/DatabaseSeederTest.php`
Expected: FAIL (jumlah akun 36, bukan 83).

- [ ] **Step 3: Implementasi** — isi `MEMBERS` dari tabel di atas. `run()` mengambil `Role::pluck('id', 'name')` sekali, lalu `User::create` per nama dengan `role_id` dari peta itu.

- [ ] **Step 4: Jalankan tes**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact`
Expected: semua PASS.

- [ ] **Step 5: Commit**

```bash
git add database/seeders tests/Feature/DatabaseSeederTest.php
git commit -m "feat: seeder akun semua anggota gen 7 per jabatan"
```

---

### Task 4: Halaman Profil — data diri dan foto

**Files:**
- Create: `app/Http/Controllers/auth/ProfilController.php`
- Create: `app/Http/Requests/auth/ProfilRequest.php`
- Create: `resources/views/auth/profil/edit.blade.php`
- Create: `resources/views/auth/partials/avatar.blade.php`
- Modify: `routes/web.php`, `resources/views/auth/layout.blade.php`, `resources/views/auth/hr/layout.blade.php`, `resources/views/auth/announcer/layout.blade.php`, `resources/views/auth/so/layout.blade.php`
- Test: `tests/Feature/auth/ProfilTest.php` (`php artisan make:test --pest auth/ProfilTest`)

**Interfaces:**
- Consumes: `User::area()`, `Area::layout()`, `$user->role->label` (Task 1).
- Produces:
  - Grup route `['auth', 'role:kadiv_announcer,announcer,kadiv_sound_operator,sound_operator,kadiv_human_resource,human_resource']`: `GET /profil` → `ProfilController@edit` (`profil.edit`), `PUT /profil` → `ProfilController@update` (`profil.update`). Task 5 menambah `PUT /profil/password` (`profil.password`) ke grup yang sama.
  - View `auth.profil.edit` menerima `user` dan `layout` (`$user->area()->layout()`), lalu `@extends($layout)`.
  - Partial `auth.partials.avatar` dengan parameter `$user` dan `$size` (kelas Tailwind ukuran, mis. `size-8`, `size-20`): `<img>` dari `Storage::url($user->photo_path)` bila ada, selain itu lingkaran berisi huruf pertama username (huruf besar).

Aturan `ProfilRequest`:
- `photo`: `nullable`, `image`, `mimes:jpg,jpeg,png,webp`, `max:2048`.
- `username`: `required`, `string`, `max:255`, `Rule::unique('users', 'username')->ignore($this->user())`.
- Atribut: `photo` → `foto profil`, `username` → `username`. Pesan Indonesia untuk `required`, `unique` ("Username ini sudah dipakai."), `image`/`mimes` ("Foto profil harus berupa gambar JPG, PNG, atau WebP."), `max` ("Foto profil maksimal 2 MB.").

- [ ] **Step 1: Tulis tes yang gagal** di `ProfilTest.php` (`uses(RefreshDatabase::class)`; `beforeEach` → `Storage::fake('public')`, `$this->user = User::factory()->announcer()->create(['username' => 'Ani Lestari', 'password' => 'lama'])`):

```php
it('shows the profile page in the area layout with the job title', function (string $role, string $navRoute) {
    $user = User::factory()->role($role)->create();

    $this->actingAs($user)->get(route('profil.edit'))
        ->assertOk()
        ->assertSee($user->role->label)
        ->assertSee(route($navRoute), false);
})->with([
    ['kadiv_human_resource', 'hr.dashboard'],
    ['announcer', 'announcer.sesi.index'],
    ['kadiv_sound_operator', 'so.sesi.index'],
]);

it('forbids the profile page for roles without an area', function () {
    $this->actingAs(User::factory()->role('creative')->create())->get(route('profil.edit'))->assertForbidden();
});

it('updates the username', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari Putri'])
        ->assertRedirect(route('profil.edit'))
        ->assertSessionHas('status', 'Profil disimpan.');

    expect($this->user->fresh()->username)->toBe('Ani Lestari Putri');
});

it('keeps the own username valid when it is unchanged', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari'])
        ->assertSessionHasNoErrors();
});

it('rejects a username that belongs to someone else', function () {
    User::factory()->create(['username' => 'Budi']);

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Budi'])
        ->assertSessionHasErrors(['username' => 'Username ini sudah dipakai.']);
});

it('stores a new photo and deletes the old one', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('a.jpg')]);
    $oldPath = $this->user->fresh()->photo_path;

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('b.png')]);
    $newPath = $this->user->fresh()->photo_path;

    expect($oldPath)->toStartWith('profile-photos/')->and($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});

it('keeps the photo when saving without a new one', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('a.jpg')]);
    $path = $this->user->fresh()->photo_path;

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari Putri']);

    expect($this->user->fresh()->photo_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

it('rejects a photo that is not an image', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors(['photo' => 'Foto profil harus berupa gambar JPG, PNG, atau WebP.']);
});

it('ignores a role change sent with the profile form', function () {
    $hrRoleId = Role::where('name', 'human_resource')->value('id');

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'role_id' => $hrRoleId]);

    expect($this->user->fresh()->role->name)->toBe('announcer');
});
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/ProfilTest.php`
Expected: FAIL (`Route [profil.edit] not defined`).

- [ ] **Step 3: Implementasi**
  - `ProfilController::edit(Request $request): View` dan `update(ProfilRequest $request): RedirectResponse`. `update` hanya menulis `username` dan, bila ada file, `photo_path` (`$request->file('photo')->store('profile-photos', 'public')`, lalu hapus path lama dari disk `public` bila ada). Redirect ke `profil.edit` dengan `status` `Profil disimpan.`.
  - View: judul `Profil`; pesan status lewat `@include('auth.partials.status')`; form `enctype="multipart/form-data"`, `@method('PUT')`, berisi avatar `size-20` + input file `photo` (label "Foto profil", bantuan "JPG, PNG, atau WebP. Maksimal 2 MB."), input `username` (label "Username", bantuan "Nama ini dipakai untuk login."), jabatan sebagai teks biasa (label "Jabatan", bukan input), tombol `Simpan profil`. Gaya field dan error mengikuti `absensi/program.blade.php` (`$fieldClass`, `aria-invalid`, `aria-describedby`).
  - Sidebar `auth/layout.blade.php`: avatar `size-8` di sebelah username di blok bawah.
  - Nav: tambah `@include('auth.partials.nav-link', ['label' => 'Profil', 'route' => 'profil.edit', 'active' => 'profil.*'])` di akhir nav ketiga layout area. Di `announcer/layout.blade.php`, item pertama yang sekarang berlabel `Profile` dan mengarah ke `announcer.dashboard` diganti label `Dashboard`, supaya tidak ada dua menu profil.

- [ ] **Step 4: Jalankan tes**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact`
Expected: semua PASS.

- [ ] **Step 5: Commit**

```bash
git add app routes resources tests
git commit -m "feat: halaman profil untuk ubah username dan foto"
```

---

### Task 5: Ganti password di halaman Profil

**Files:**
- Create: `app/Http/Requests/auth/PasswordRequest.php`
- Modify: `app/Http/Controllers/auth/ProfilController.php`, `routes/web.php`, `resources/views/auth/profil/edit.blade.php`
- Test: `tests/Feature/auth/ProfilTest.php`

**Interfaces:**
- Consumes: grup route dan view Profil dari Task 4.
- Produces: `PUT /profil/password` → `ProfilController@updatePassword(PasswordRequest $request): RedirectResponse`, route `profil.password`.

Aturan `PasswordRequest` (`protected $errorBag = 'password';`):
- `current_password`: `required`, `current_password`. Pesan: "Password saat ini salah."
- `password`: `required`, `string`, `confirmed` (tanpa minimal panjang). Pesan `confirmed`: "Konfirmasi password baru tidak cocok."
- Atribut: `current_password` → `password saat ini`, `password` → `password baru`.

- [ ] **Step 1: Tulis tes yang gagal** di `ProfilTest.php` (memakai `$this->user` dari `beforeEach` Task 4, password `lama`):

```php
it('changes the password and lets the user log in with it', function () {
    $this->actingAs($this->user)->put(route('profil.password'), [
        'current_password' => 'lama', 'password' => 'baru', 'password_confirmation' => 'baru',
    ])->assertRedirect(route('profil.edit'))->assertSessionHas('status', 'Password diganti.');

    auth()->logout();
    $this->post(route('login'), ['username' => 'Ani Lestari', 'password' => 'baru'])->assertRedirect(route('announcer.dashboard'));
});

it('rejects a wrong current password only in the password form', function () {
    $this->actingAs($this->user)->put(route('profil.password'), [
        'current_password' => 'salah', 'password' => 'baru', 'password_confirmation' => 'baru',
    ])->assertSessionHasErrorsIn('password', ['current_password' => 'Password saat ini salah.']);

    expect(session('errors')->getBag('default')->isEmpty())->toBeTrue();
    expect(Hash::check('lama', $this->user->fresh()->password))->toBeTrue();
});

it('rejects a new password that does not match its confirmation', function () {
    $this->actingAs($this->user)->put(route('profil.password'), [
        'current_password' => 'lama', 'password' => 'baru', 'password_confirmation' => 'beda',
    ])->assertSessionHasErrorsIn('password', ['password' => 'Konfirmasi password baru tidak cocok.']);
});

it('accepts a short new password', function () {
    $this->actingAs($this->user)->put(route('profil.password'), [
        'current_password' => 'lama', 'password' => '1', 'password_confirmation' => '1',
    ])->assertSessionHasNoErrors();
});
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test --compact tests/Feature/auth/ProfilTest.php`
Expected: FAIL (`Route [profil.password] not defined`).

- [ ] **Step 3: Implementasi** — `updatePassword` menyimpan `password` (cast `hashed` di model yang meng-hash), redirect `profil.edit` dengan `status` `Password diganti.`. Sesi lain tidak diputus. View: form kedua di bawah data diri, judul bagian "Ganti password", tiga input `type="password"` (`current_password` "Password saat ini", `password` "Password baru", `password_confirmation` "Konfirmasi password baru") dengan `autocomplete` `current-password`/`new-password`, error dibaca dari bag `password` (`@error('current_password', 'password')`), tombol `Ganti password`. Form data diri tetap membaca bag default.

- [ ] **Step 4: Jalankan tes**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact`
Expected: semua PASS.

- [ ] **Step 5: Commit**

```bash
git add app routes resources tests
git commit -m "feat: ganti password di halaman profil"
```

---

## Setelah semua task

Minta user menjalankan di mesinnya: `sail artisan migrate`, `sail artisan storage:link` (sekali), `sail artisan migrate:fresh --seed` bila ingin data Gen 7 lengkap, dan `sail artisan test --compact`.
