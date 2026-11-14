<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('seeder membuat tiga akun dengan password dari config', function () {
    config(['seed.passwords' => ['admin' => 'a-rahasia', 'hr' => 'h-rahasia', 'panitia' => 'p-rahasia']]);
    $this->seed();

    expect(User::pluck('username')->sort()->values()->all())->toBe(['admin', 'hr', 'panitia']);
    $hr = User::firstWhere('username', 'hr');
    expect($hr->role)->toBe(UserRole::Hr)
        ->and(Hash::check('h-rahasia', $hr->password))->toBeTrue();
});

test('seeder aman dijalankan dua kali', function () {
    $this->seed();
    $this->seed();
    expect(User::count())->toBe(3);
});

test('tanpa password di config, lingkungan testing memakai password bawaan', function () {
    config(['seed.passwords' => ['admin' => null, 'hr' => null, 'panitia' => null]]);
    $this->seed();
    expect(Hash::check('password', User::firstWhere('username', 'admin')->password))->toBeTrue();
});

test('seeder berhenti di production bila password kosong', function () {
    config(['seed.passwords' => ['admin' => null, 'hr' => null, 'panitia' => null]]);
    app()->detectEnvironment(fn () => 'production');
    expect(fn () => app(DatabaseSeeder::class)->run())->toThrow(RuntimeException::class, 'SEED_ADMIN_PASSWORD');
});
