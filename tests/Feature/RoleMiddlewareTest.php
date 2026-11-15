<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', 'auth', 'role:hr'])->get('/_t/hr', fn () => 'ok');
    Route::middleware(['web', 'auth', 'role:admin,hr'])->get('/_t/staff', fn () => 'ok');
});

test('peran yang cocok lolos, lainnya 403', function () {
    $hr = User::factory()->role('hr')->create();
    $panitia = User::factory()->role('panitia')->create();

    $this->actingAs($hr)->get('/_t/hr')->assertOk();
    $this->actingAs($hr)->get('/_t/staff')->assertOk();
    $this->actingAs($panitia)->get('/_t/hr')->assertForbidden();
    $this->actingAs($panitia)->get('/_t/staff')->assertForbidden();
});

test('peran tak dikenal di database ditolak 403, bukan 500', function () {
    $user = User::factory()->create();
    DB::table('users')->where('id', $user->id)->update(['role' => 'lainnya']);

    $this->actingAs(User::find($user->id))->get('/_t/hr')->assertForbidden();
});
