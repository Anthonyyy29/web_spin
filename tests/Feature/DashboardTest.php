<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tamu dialihkan ke login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('tiap peran melihat dashboard dengan sapaan perannya', function (UserRole $role, string $nama) {
    $this->actingAs(User::factory()->role($role)->create())->get('/dashboard')
        ->assertOk()
        ->assertSee('<title>Admin | Dashboard</title>', false)
        ->assertSee("Selamat datang, {$nama}.");
})->with([[UserRole::Admin, 'Admin'], [UserRole::Hr, 'Hr'], [UserRole::Panitia, 'Panitia']]);

test('alur penuh: login, dashboard, logout', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    $this->post('/login/checklogin', ['username' => 'admin', 'password' => 'rahasia'])->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertOk();
    $this->get('/dashboard/logout')->assertRedirect('/login');
    $this->get('/dashboard')->assertRedirect('/login');
});
