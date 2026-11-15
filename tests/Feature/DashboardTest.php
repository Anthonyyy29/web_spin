<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tamu dialihkan ke login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('tiap peran melihat dashboard miliknya', function (string $role, string $teks) {
    $this->actingAs(User::factory()->role($role)->create())->get('/dashboard')
        ->assertOk()
        ->assertSee('<title>Admin | Dashboard</title>', false)
        ->assertSee($teks);
})->with([
    ['admin', 'Total Podcast'],
    ['hr', 'Tap Kartu Absensi'],
]);

test('peran tak dikenal ditolak 403 di dashboard', function () {
    $this->actingAs(User::factory()->role('tamu')->create())->get('/dashboard')->assertForbidden();
});

test('alur penuh: login, dashboard, logout', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    $this->post('/login/checklogin', ['username' => 'admin', 'password' => 'rahasia'])->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertOk();
    $this->get('/dashboard/logout')->assertRedirect('/login');
    $this->get('/dashboard')->assertRedirect('/login');
});
