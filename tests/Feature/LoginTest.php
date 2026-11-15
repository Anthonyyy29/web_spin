<?php

use App\Models\AdminHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function masuk(string $username = 'admin', string $password = 'rahasia')
{
    return test()->post('/login/checklogin', ['username' => $username, 'password' => $password]);
}

test('tiap peran bisa login', function (string $role) {
    User::factory()->role($role)->create(['username' => $role, 'password' => 'rahasia']);

    masuk($role)->assertRedirect('/dashboard')->assertSessionHas('success', 'login berhasil');
    $this->assertAuthenticated();
})->with(['admin', 'hr', 'panitia']);

test('password salah ditolak', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    masuk(password: 'salah')->assertRedirect('/login')->assertSessionHas('error', 'login gagal');
    $this->assertGuest();
    expect(AdminHistory::count())->toBe(0);
});

test('isian kosong atau aneh berakhir sebagai login gagal', function (array $isian) {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    $this->post('/login/checklogin', $isian)->assertRedirect('/login')->assertSessionHas('error', 'login gagal');
    $this->assertGuest();
})->with([
    'kosong' => [[]],
    'isian array' => [['username' => ['x'], 'password' => ['y']]],
]);

test('spasi di tepi username dipangkas sehingga login tetap berhasil', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    masuk(' admin ')->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

test('percobaan keenam dalam semenit ditolak lalu pulih setelah 60 detik', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);
    foreach (range(1, 5) as $i) {
        masuk(password: 'salah');
    }

    masuk()->assertRedirect('/login')
        ->assertSessionHas('error', 'Terlalu banyak percobaan. Coba lagi nanti.');
    $this->assertGuest();

    $this->travel(61)->seconds();
    masuk()->assertRedirect('/dashboard');
});

test('login berhasil menghapus hitungan percobaan gagal', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);
    foreach (range(1, 4) as $i) {
        masuk(password: 'salah');
    }
    masuk()->assertRedirect('/dashboard');
    auth()->logout();
    foreach (range(1, 4) as $i) {
        masuk(password: 'salah')->assertSessionHas('error', 'login gagal');
    }
});

test('id session berganti setelah login', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);
    $this->get('/login');
    $sebelum = session()->getId();

    $this->withCookie(config('session.cookie'), $sebelum);
    masuk()->assertRedirect('/dashboard');
    expect(session()->getId())->not->toBe($sebelum);
});

test('login berhasil mencatat admin_history', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);
    masuk();

    expect(AdminHistory::sole()->device)->toBe('Desktop');
});

test('login selalu menuju dashboard walau sebelumnya membuka url lain', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);
    $this->withSession(['url.intended' => '/tempat-lain']);

    masuk()->assertRedirect('/dashboard');
});
