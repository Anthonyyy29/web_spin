<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('seeder membuat tiga akun yang bisa dipakai login', function (string $username, string $role) {
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
    ['panitia', 'panitia'],
]);

test('seeder aman dijalankan dua kali', function () {
    $this->seed();
    $this->seed();

    expect(User::count())->toBe(3);
});
