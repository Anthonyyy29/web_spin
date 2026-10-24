<?php

use App\Models\Program;
use App\Models\User;
use Database\Seeders\Gen7MemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the hr account and the gen 7 members without example data', function () {
    $this->seed();

    expect(User::withRoles(['announcer'])->count())->toBe(17)
        ->and(User::withRoles(['sound_operator'])->count())->toBe(11)
        ->and(User::withRoles(['human_resource'])->count())->toBe(8)
        ->and(User::where('username', 'HR')->sole()->role->name)->toBe('human_resource')
        ->and(User::where('username', 'announcer2')->exists())->toBeFalse()
        ->and(Program::count())->toBe(0);
});

it('lets a seeded member log in with the full name and the default password', function () {
    $this->seed();

    $this->post(route('login'), ['username' => 'Priscilla Christiana Nahadin', 'password' => Gen7MemberSeeder::DEFAULT_PASSWORD])
        ->assertRedirect(route('announcer.dashboard'));

    $this->assertAuthenticatedAs(User::where('username', 'Priscilla Christiana Nahadin')->sole());
});
