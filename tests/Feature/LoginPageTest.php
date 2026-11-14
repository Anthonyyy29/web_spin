<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tamu melihat form login', function () {
    $this->get('/login')->assertOk()
        ->assertSee('action="'.url('/login/checklogin').'"', false)
        ->assertSee('name="username"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="_token"', false)
        ->assertSee('assets/css/login.css', false);
});

test('pesan kilat tampil dan di-escape', function () {
    $this->withSession(['error' => 'login gagal'])->get('/login')->assertSee('login gagal');
    $this->withSession(['error' => '<script>x</script>'])->get('/login')
        ->assertDontSee('<script>x</script>', false);
});

test('yang sudah login dialihkan ke dashboard', function () {
    $this->actingAs(User::factory()->create())->get('/login')
        ->assertRedirect('/dashboard')->assertSessionHas('success', 'login berhasil');
});
