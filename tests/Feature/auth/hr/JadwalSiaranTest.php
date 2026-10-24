<?php

use App\Models\BroadcastSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->hr = User::factory()->hr()->create();
    $this->announcer = User::factory()->announcer()->create();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function schedulePayload(User $announcer, array $overrides = []): array
{
    return array_merge([
        'user_id' => $announcer->id,
        'program_title' => 'Pagi Ceria',
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00',
        'end_time' => '09:00',
        'notes' => 'Catatan',
    ], $overrides);
}

it('rejects a schedule that overlaps another for the same announcer and date', function () {
    BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
    ]);

    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer, [
            'start_time' => '08:30',
            'end_time' => '09:30',
        ]))
        ->assertSessionHasErrors([
            'start_time' => 'Jadwal bentrok dengan jadwal lain milik announcer ini.',
        ]);

    expect(BroadcastSchedule::count())->toBe(1);
});

it('rejects updating a schedule into another schedule\'s slot', function () {
    BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
    ]);
    $schedule = BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'broadcast_date' => '2026-11-01',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
    ]);

    $this->actingAs($this->hr)
        ->put(route('jadwalSiaran.update', $schedule), schedulePayload($this->announcer, [
            'start_time' => '08:30',
            'end_time' => '09:30',
        ]))
        ->assertSessionHasErrors('start_time');

    $schedule->refresh();

    expect($schedule->start_time)->toBe('10:00:00')
        ->and($schedule->end_time)->toBe('11:00:00');
});

it('shows validation messages in Indonesian', function () {
    $response = $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer, [
            'program_title' => '',
        ]));

    $response->assertSessionHasErrors('program_title');

    expect(session('errors')->first('program_title'))->toContain('judul program');
});

it('rejects a broadcast date that is not in Y-m-d format', function () {
    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer, [
            'broadcast_date' => '01/11/2026',
        ]))
        ->assertSessionHasErrors('broadcast_date');

    expect(BroadcastSchedule::count())->toBe(0);
});

it('allows adjacent schedules for the same announcer', function () {
    BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
    ]);

    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer, [
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]))
        ->assertSessionHasNoErrors();

    expect(BroadcastSchedule::count())->toBe(2);
});

it('allows the same time slot for a different announcer', function () {
    BroadcastSchedule::factory()->create([
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
    ]);

    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer))
        ->assertSessionHasNoErrors();

    expect(BroadcastSchedule::count())->toBe(2);
});

it('rejects end time that is not after start time', function () {
    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer, [
            'start_time' => '09:00',
            'end_time' => '08:00',
        ]))
        ->assertSessionHasErrors('end_time');
});

it('rejects user_id that is not an announcer', function () {
    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->hr))
        ->assertSessionHasErrors('user_id');
});

it('lets hr create a schedule', function () {
    $this->actingAs($this->hr)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer))
        ->assertRedirect(route('jadwalSiaran.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('broadcast_schedules', [
        'user_id' => $this->announcer->id,
        'program_title' => 'Pagi Ceria',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'notes' => 'Catatan',
    ]);
});

it('lists schedules with announcer username on the index', function () {
    BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'program_title' => 'Program Sore Santai',
    ]);

    $this->actingAs($this->hr)
        ->get(route('jadwalSiaran.index'))
        ->assertOk()
        ->assertSee('Program Sore Santai')
        ->assertSee($this->announcer->username);
});

it('lets hr update a schedule without conflicting with itself', function () {
    $schedule = BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
    ]);

    $this->actingAs($this->hr)
        ->put(route('jadwalSiaran.update', $schedule), schedulePayload($this->announcer, [
            'program_title' => 'Judul Baru',
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('jadwalSiaran.index'));

    expect($schedule->fresh()->program_title)->toBe('Judul Baru');
});

it('prefills the edit form with times the request accepts', function () {
    $schedule = BroadcastSchedule::factory()->create([
        'user_id' => $this->announcer->id,
        'broadcast_date' => '2026-11-01',
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
    ]);

    $this->actingAs($this->hr)
        ->get(route('jadwalSiaran.edit', $schedule))
        ->assertOk()
        ->assertSee('value="08:00"', false)
        ->assertSee('value="09:00"', false)
        ->assertSee('value="2026-11-01"', false);
});

it('lets hr delete a schedule', function () {
    $schedule = BroadcastSchedule::factory()->create();

    $this->actingAs($this->hr)
        ->delete(route('jadwalSiaran.destroy', $schedule))
        ->assertRedirect(route('jadwalSiaran.index'));

    $this->assertDatabaseMissing('broadcast_schedules', ['id' => $schedule->id]);
});

it('forbids announcer and redirects guest from hr schedule routes', function () {
    $this->actingAs($this->announcer)
        ->get(route('jadwalSiaran.index'))
        ->assertForbidden();

    $this->actingAs($this->announcer)
        ->post(route('jadwalSiaran.store'), schedulePayload($this->announcer))
        ->assertForbidden();

    auth()->logout();

    $this->get(route('jadwalSiaran.index'))
        ->assertRedirect(route('login'));
});
