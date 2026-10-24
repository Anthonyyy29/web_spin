<?php

use App\Models\Program;
use App\Models\User;
use Database\Seeders\Gen7MemberSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds one account per gen 7 member job plus the hr account', function () {
    $this->seed();

    $counts = User::query()->join('roles', 'roles.id', '=', 'users.role_id')
        ->selectRaw('roles.name, count(*) as total')->groupBy('roles.name')->pluck('total', 'roles.name')->all();

    expect(User::count())->toBe(83)
        ->and($counts)->toEqual([
            'gm' => 1,
            'vgm' => 1,
            'sekretaris' => 2,
            'finance' => 2,
            'head_of_podcast' => 1,
            'kadiv_announcer' => 1,
            'announcer' => 16,
            'kadiv_sound_operator' => 1,
            'sound_operator' => 10,
            'kadiv_human_resource' => 1,
            'human_resource' => 7,
            'head_of_public_relation' => 1,
            'kadiv_partnership' => 1,
            'partnership' => 4,
            'kadiv_media_relation' => 1,
            'media_relation' => 4,
            'head_of_social_media' => 1,
            'kadiv_creative' => 1,
            'creative' => 6,
            'kadiv_video_editor' => 1,
            'video_editor' => 6,
            'kadiv_design_and_publishing' => 1,
            'design_and_publishing' => 9,
            'kadiv_web_development' => 1,
            'web_development' => 3,
        ])
        ->and(User::where('username', 'HR')->sole()->role->name)->toBe('human_resource')
        ->and(Program::count())->toBe(0);
});

it('gives members with two jobs one account per job', function () {
    $this->seed();

    expect(User::where('username', 'Noval Rasyah Kurnia Putra (Announcer)')->sole()->role->name)->toBe('announcer')
        ->and(User::where('username', 'Noval Rasyah Kurnia Putra (Finance)')->sole()->role->name)->toBe('finance')
        ->and(User::where('username', 'Shafa Dhiya Raihanah (Announcer)')->sole()->role->name)->toBe('announcer')
        ->and(User::where('username', 'Shafa Dhiya Raihanah (Finance)')->sole()->role->name)->toBe('finance')
        ->and(User::where('username', 'Syavirni Dwi Amalia (Sound Operator)')->sole()->role->name)->toBe('sound_operator')
        ->and(User::where('username', 'Syavirni Dwi Amalia (Sekretaris)')->sole()->role->name)->toBe('sekretaris')
        ->and(User::where('username', 'Noval Rasyah Kurnia Putra')->exists())->toBeFalse();
});

it('lets a seeded member log in with the full name and the default password', function () {
    $this->seed();

    $this->post(route('login'), ['username' => 'Priscilla Christiana Nahadin', 'password' => Gen7MemberSeeder::DEFAULT_PASSWORD])
        ->assertRedirect(route('announcer.dashboard'));

    $this->assertAuthenticatedAs(User::where('username', 'Priscilla Christiana Nahadin')->sole());
});

it('lets a member log in with a username that has a job suffix', function () {
    $this->seed();

    $this->post(route('login'), ['username' => 'Noval Rasyah Kurnia Putra (Announcer)', 'password' => Gen7MemberSeeder::DEFAULT_PASSWORD])
        ->assertRedirect(route('announcer.dashboard'));
});
