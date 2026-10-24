<?php

use App\Enums\CrewPosition;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets each kadiv into their own area', function (string $role, string $url) {
    $this->actingAs(User::factory()->role($role)->create())->get($url)->assertOk();
})->with([
    ['kadiv_human_resource', '/hr/dashboard'],
    ['kadiv_announcer', '/announcer/dashboard'],
    ['kadiv_sound_operator', '/so/sesi'],
]);

it('keeps a role without an area out of every area', function (string $url) {
    $this->actingAs(User::factory()->role('creative')->create())->get($url)->assertForbidden();
})->with(['/hr/dashboard', '/announcer/dashboard', '/so/sesi']);

it('redirects each kadiv to their area after login', function (string $role, string $routeName) {
    $user = User::factory()->role($role)->create(['password' => 'secret-pass']);

    $this->post(route('login'), ['username' => $user->username, 'password' => 'secret-pass'])
        ->assertRedirect(route($routeName));
})->with([
    ['kadiv_human_resource', 'hr.dashboard'],
    ['kadiv_announcer', 'announcer.dashboard'],
    ['kadiv_sound_operator', 'so.sesi.index'],
]);

it('lets a kadiv announcer check in from the announcer sessions page', function () {
    $kadiv = User::factory()->role('kadiv_announcer')->create();
    $program = Program::factory()->create(['name' => 'Pagi Ceria']);
    $session = BroadcastSession::factory()->for($program)->create();
    $session->crew()->attach($kadiv->id, ['position' => CrewPosition::Announcer->value]);
    $this->travelTo('2026-10-26 07:20:00');

    $this->actingAs($kadiv)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSee('Pagi Ceria');

    $this->actingAs($kadiv)
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertSessionHas('status', 'Check-in tercatat: Hadir 07:20.');
});

it('offers kadiv crew in the program form and rejects other roles', function () {
    $this->travelTo('2026-10-24 10:00:00');
    $hr = User::factory()->hr()->create();
    $kadivAnnouncer = User::factory()->role('kadiv_announcer')->create(['username' => 'kadiv-announcer']);
    $kadivSo = User::factory()->role('kadiv_sound_operator')->create(['username' => 'kadiv-so']);
    $creative = User::factory()->role('creative')->create(['username' => 'anak-creative']);

    $payload = fn (array $overrides): array => array_merge([
        'name' => 'Pagi Ceria',
        'day_of_week' => 1,
        'crewcall_time' => '07:30',
        'tapping_start' => '08:00',
        'tapping_end' => '09:30',
        'period_start' => '2026-10-26',
        'period_end' => '2026-11-16',
    ], $overrides);

    $this->actingAs($hr)
        ->get(route('program.create'))
        ->assertOk()
        ->assertSee('kadiv-announcer')
        ->assertSee('kadiv-so')
        ->assertDontSee('anak-creative');

    $this->actingAs($hr)
        ->post(route('program.store'), $payload(['announcer_ids' => [$creative->id]]))
        ->assertSessionHasErrors('announcer_ids.0');

    $this->actingAs($hr)
        ->post(route('program.store'), $payload(['announcer_ids' => [$kadivAnnouncer->id], 'so_ids' => [$kadivSo->id]]))
        ->assertRedirect(route('program.index'))
        ->assertSessionHasNoErrors();

    $program = Program::where('name', 'Pagi Ceria')->sole();

    expect($program->crew->firstWhere('id', $kadivSo->id)->pivot->position)->toBe('sound_operator')
        ->and($program->crew->firstWhere('id', $kadivAnnouncer->id)->pivot->position)->toBe('announcer');
});
