<?php

use App\Models\BroadcastSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it("shows only the signed in announcer's schedules", function () {
    $announcer = User::factory()->announcer()->create();
    $otherAnnouncer = User::factory()->announcer()->create();

    BroadcastSchedule::factory()->create([
        'user_id' => $announcer->id,
        'program_title' => 'Program Milik Sendiri',
    ]);
    BroadcastSchedule::factory()->create([
        'user_id' => $otherAnnouncer->id,
        'program_title' => 'Program Milik Orang Lain',
    ]);

    $this->actingAs($announcer)
        ->get(route('announcer.jadwalSiaran'))
        ->assertOk()
        ->assertSee('Program Milik Sendiri')
        ->assertDontSee('Program Milik Orang Lain');
});

it('forbids hr from the announcer schedule page', function () {
    $hr = User::factory()->hr()->create();

    $this->actingAs($hr)
        ->get(route('announcer.jadwalSiaran'))
        ->assertForbidden();
});
