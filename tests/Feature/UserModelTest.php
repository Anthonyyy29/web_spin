<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('user menyimpan username, peran, dan password ter-hash', function () {
    $user = User::factory()->role('hr')->create(['username' => 'hr', 'password' => 'rahasia']);

    expect($user->fresh()->role)->toBe('hr')
        ->and($user->getRawOriginal('password'))->not->toBe('rahasia')
        ->and(Hash::check('rahasia', $user->password))->toBeTrue();
});

test('username harus unik', function () {
    User::factory()->create(['username' => 'admin']);
    expect(fn () => User::factory()->create(['username' => 'admin']))
        ->toThrow(QueryException::class);
});

test('skema hanya memuat tabel yang dipakai', function () {
    expect(Schema::hasColumns('users', ['username', 'password', 'role', 'remember_token']))->toBeTrue()
        ->and(Schema::hasColumn('users', 'email'))->toBeFalse()
        ->and(Schema::hasTable('sessions'))->toBeTrue()
        ->and(Schema::hasTable('password_reset_tokens'))->toBeFalse();
});
