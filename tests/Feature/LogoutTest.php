<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('logout mengakhiri sesi', function () {
    $this->actingAs(User::factory()->create())->get('/dashboard/logout')
        ->assertRedirect('/login')->assertSessionHas('success', 'Anda telah logout.');
    $this->assertGuest();
});

test('tamu yang membuka logout dialihkan ke login', function () {
    $this->get('/dashboard/logout')->assertRedirect('/login');
});

test('rute logout bernama logout', function () {
    expect(route('logout', absolute: false))->toBe('/dashboard/logout');
});
