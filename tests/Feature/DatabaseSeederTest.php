<?php

use App\Models\Crew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('seeder membuat dua akun yang bisa dipakai login', function (string $username, string $role) {
    $this->seed();

    $user = User::firstWhere('username', $username);
    expect($user->role)->toBe($role)
        ->and($user->getRawOriginal('password'))->not->toBe('password')
        ->and(Hash::check('password', $user->password))->toBeTrue();

    $this->post('/login/checklogin', ['username' => $username, 'password' => 'password'])
        ->assertRedirect('/dashboard');
})->with([
    ['admin', 'admin'],
    ['hr', 'hr'],
]);

test('seeder aman dijalankan dua kali', function () {
    $this->seed();
    $this->seed();

    expect(User::count())->toBe(2);
});

test('seeder crew memuat anggota Gen 7 dengan jabatan dan nama yang valid', function () {
    $this->seed();
    $this->seed();

    expect(Crew::count())->toBe(79)
        ->and(Crew::whereNotIn('role', array_keys(Crew::ROLES))->count())->toBe(0)
        ->and(Crew::all()->every(fn (Crew $crew): bool => mb_strlen($crew->name) <= 30))->toBeTrue()
        ->and(Crew::sortedByRole()->first()->name)->toBe('Nurul Afifah Azzahra');
});
