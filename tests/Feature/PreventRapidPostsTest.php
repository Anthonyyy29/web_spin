<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', 'auth', 'actioncheck'])->group(function () {
        Route::post('/_t/post', fn () => 'ok');
        Route::get('/_t/get', fn () => 'ok');
    });
    $this->actingAs(User::factory()->create());
    $this->freezeTime();
});

test('post kedua dalam satu detik ditolak, sesudahnya diterima lagi', function () {
    $this->post('/_t/post')->assertOk();
    $this->from('/dashboard')->post('/_t/post')
        ->assertRedirect('/dashboard')->assertSessionHas('error', 'Too many actions');

    $this->travel(1)->seconds();
    $this->post('/_t/post')->assertOk();
});

test('get tidak dibatasi', function () {
    $this->post('/_t/post')->assertOk();
    $this->get('/_t/get')->assertOk();
    $this->get('/_t/get')->assertOk();
});
