<?php

use App\Models\Content;
use App\Models\Crew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

test('seeder mengisi crew tiap program dan aman dijalankan dua kali', function () {
    $this->seed();
    $this->seed();

    $kTime = Content::where('title', 'K-TIME')->first();

    expect(DB::table('content_crew')->count())->toBe(31)
        ->and($kTime->crews->pluck('name')->all())->toContain('Priscilla Christiana Nahadin', 'Dhini Aulya')
        ->and(Content::where('title', 'SPINVERSE')->first()->crews)->toHaveCount(0)
        ->and(Crew::where('name', 'Salwa Syafira')->first()->contents)->toHaveCount(2);
});

test('seeder crew memuat anggota Gen 7 dengan jabatan dan nama yang valid', function () {
    $this->seed();
    $this->seed();

    expect(Crew::count())->toBe(79)
        ->and(Crew::whereNotIn('role', array_keys(Crew::ROLES))->count())->toBe(0)
        ->and(Crew::all()->every(fn (Crew $crew): bool => mb_strlen($crew->name) <= 30))->toBeTrue()
        ->and(Crew::sortedByRole()->first()->name)->toBe('Nurul Afifah Azzahra');
});

test('seeder mengisi jabatan tambahan tiga anggota berjabatan ganda', function () {
    $this->seed();
    $this->seed();

    $nama = ['Syavirni Dwi Amalia', 'Noval Rasyah Kurnia Putra', 'Shafa Dhiya Raihanah'];

    expect(Crew::whereNotNull('extra_role')->pluck('extra_role', 'name')->all())->toEqualCanonicalizing([
        'Syavirni Dwi Amalia' => 'Sound Operator',
        'Noval Rasyah Kurnia Putra' => 'Announcer',
        'Shafa Dhiya Raihanah' => 'Announcer',
    ])
        ->and(Crew::canJoinSession()->whereIn('name', $nama)->count())->toBe(3)
        ->and(Crew::whereIn('name', $nama)->pluck('role', 'name')->all())->toEqualCanonicalizing([
            'Syavirni Dwi Amalia' => 'Sekretaris',
            'Noval Rasyah Kurnia Putra' => 'Bendahara',
            'Shafa Dhiya Raihanah' => 'Bendahara',
        ]);
});
