<?php

use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-10-24 10:00:00');

    $this->hr = User::factory()->hr()->create();
    $this->announcer = User::factory()->announcer()->create();
    $this->so = User::factory()->so()->create();
    $this->program = Program::factory()->create(['name' => 'Pagi Ceria']);

    $this->payload = fn (array $overrides = []): array => array_merge([
        'session_date' => '2026-10-28',
        'crewcall_time' => '09:00',
        'tapping_start' => '09:30',
        'tapping_end' => '11:00',
        'announcer_ids' => [$this->announcer->id],
        'so_ids' => [$this->so->id],
    ], $overrides);

    $this->sessionWithCrew = function (array $attributes = [], $announcer = null): BroadcastSession {
        $session = BroadcastSession::factory()->for($this->program)->create($attributes);
        $session->crew()->attach($announcer?->id ?? $this->announcer->id, ['position' => 'announcer']);

        return $session;
    };
});

it('lists postponed sessions in the reschedule box', function () {
    $postponed = ($this->sessionWithCrew)(['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);
    ($this->sessionWithCrew)(['session_date' => '2026-10-26']);
    ($this->sessionWithCrew)(['session_date' => '2026-10-27', 'status' => SessionStatus::Cancelled]);

    $this->actingAs($this->hr)
        ->get(route('sesi.index'))
        ->assertOk()
        ->assertSeeInOrder(['Perlu dijadwalkan ulang', 'Pagi Ceria', route('sesi.edit', $postponed)])
        ->assertSee(route('sesi.cancel', $postponed))
        ->assertSee('26 Okt 2026')
        ->assertSee('Batal')
        ->assertSee('Terjadwal');
});

it('marks today in the session table', function () {
    ($this->sessionWithCrew)(['session_date' => '2026-10-24']);

    $this->actingAs($this->hr)->get(route('sesi.index'))->assertOk()->assertSee('Hari ini');
});

it('does not list postponed sessions in the dated table', function () {
    ($this->sessionWithCrew)(['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);

    $response = $this->actingAs($this->hr)->get(route('sesi.index'))->assertOk();

    expect($response->viewData('sessions')->total())->toBe(0)
        ->and($response->viewData('postponedSessions'))->toHaveCount(1);
});

it('filters sessions by program', function () {
    $other = Program::factory()->create(['name' => 'Sore Santai']);
    ($this->sessionWithCrew)(['session_date' => '2026-10-26']);
    BroadcastSession::factory()->for($other)->create(['session_date' => '2026-11-02']);
    BroadcastSession::factory()->for($other)->postponed()->create();

    $response = $this->actingAs($this->hr)
        ->get(route('sesi.index', ['program' => $this->program->id]))
        ->assertOk();

    expect($response->viewData('sessions')->pluck('program_id')->unique()->all())->toBe([$this->program->id])
        ->and($response->viewData('sessions')->total())->toBe(1)
        ->and($response->viewData('postponedSessions'))->toHaveCount(0);
});

it('shows the edit form prefilled for a scheduled session', function () {
    $session = ($this->sessionWithCrew)(['session_date' => '2026-10-26']);

    $this->actingAs($this->hr)
        ->get(route('sesi.edit', $session))
        ->assertOk()
        ->assertSee('value="2026-10-26"', false)
        ->assertSee('value="07:30"', false)
        ->assertSee('value="08:00"', false)
        ->assertSee('value="09:30"', false);
});

it('shows empty date and times in the edit form of a postponed session', function () {
    $session = BroadcastSession::factory()->for($this->program)->postponed()->create();
    $session->crew()->attach($this->announcer->id, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->get(route('sesi.edit', $session))
        ->assertOk()
        ->assertDontSee('value="2026-10-26"', false)
        ->assertSee('name="session_date" value=""', false)
        ->assertSee('value="'.$this->announcer->id.'" checked', false);
});

it('updates a session and syncs its crew', function () {
    $session = ($this->sessionWithCrew)();
    $attendance = Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)())
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHas('status');

    $session->refresh();

    expect($session->session_date->toDateString())->toBe('2026-10-28')
        ->and($session->crewcall_time)->toBe('09:00:00')
        ->and($session->tapping_start)->toBe('09:30:00')
        ->and($session->tapping_end)->toBe('11:00:00')
        ->and($session->status)->toBe(SessionStatus::Scheduled)
        ->and($session->is_customized)->toBeTrue()
        ->and($session->crew()->wherePivot('position', 'so')->pluck('users.id')->all())->toBe([$this->so->id])
        ->and(Attendance::whereKey($attendance->id)->exists())->toBeTrue();
});

it('postpones a session and clears its date and times', function () {
    $session = ($this->sessionWithCrew)();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)
        ->post(route('sesi.postpone', $session))
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHas('status');

    $session->refresh();

    expect($session->status)->toBe(SessionStatus::Postponed)
        ->and($session->session_date)->toBeNull()
        ->and($session->crewcall_time)->toBeNull()
        ->and($session->tapping_start)->toBeNull()
        ->and($session->tapping_end)->toBeNull()
        ->and($session->is_customized)->toBeTrue()
        ->and($session->attendances()->count())->toBe(1);
});

it('reschedules a postponed session', function () {
    $session = ($this->sessionWithCrew)(['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)(['session_date' => '2026-11-04']))
        ->assertRedirect(route('sesi.index'));

    $session->refresh();

    expect($session->status)->toBe(SessionStatus::Scheduled)
        ->and($session->session_date->toDateString())->toBe('2026-11-04')
        ->and($session->is_customized)->toBeTrue();
});

it('cancels a session', function () {
    $session = ($this->sessionWithCrew)();

    $this->actingAs($this->hr)
        ->post(route('sesi.cancel', $session))
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHas('status');

    $session->refresh();

    expect($session->status)->toBe(SessionStatus::Cancelled)
        ->and($session->is_customized)->toBeTrue()
        ->and($session->session_date)->not->toBeNull();
});

it('cancels a postponed session', function () {
    $session = ($this->sessionWithCrew)(['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);

    $this->actingAs($this->hr)
        ->post(route('sesi.cancel', $session))
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHas('status');

    expect($session->refresh()->status)->toBe(SessionStatus::Cancelled)
        ->and($session->session_date)->toBeNull()
        ->and($session->is_customized)->toBeTrue();
});

it('rejects postponing a session that is not scheduled', function () {
    $cancelled = ($this->sessionWithCrew)(['status' => SessionStatus::Cancelled, 'session_date' => '2026-10-26']);
    $postponed = ($this->sessionWithCrew)(['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);

    foreach ([$cancelled, $postponed] as $session) {
        $this->actingAs($this->hr)
            ->post(route('sesi.postpone', $session))
            ->assertRedirect(route('sesi.index'))
            ->assertSessionHasErrors(['sesi' => 'Sesi ini tidak bisa ditunda.']);
    }

    expect($cancelled->refresh()->status)->toBe(SessionStatus::Cancelled)
        ->and($cancelled->session_date->toDateString())->toBe('2026-10-26')
        ->and($cancelled->is_customized)->toBeFalse()
        ->and($postponed->refresh()->status)->toBe(SessionStatus::Postponed);
});

it('rejects cancelling an already cancelled session', function () {
    $session = ($this->sessionWithCrew)(['status' => SessionStatus::Cancelled]);

    $this->actingAs($this->hr)
        ->post(route('sesi.cancel', $session))
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHasErrors(['sesi' => 'Sesi ini tidak bisa dibatalkan.']);

    expect($session->refresh()->is_customized)->toBeFalse();
});

it('shows the rejection message on the session list', function () {
    $session = ($this->sessionWithCrew)(['status' => SessionStatus::Cancelled]);

    $this->actingAs($this->hr)
        ->followingRedirects()
        ->post(route('sesi.cancel', $session))
        ->assertOk()
        ->assertSee('Sesi ini tidak bisa dibatalkan.');
});

it('rejects a crew member booked in an overlapping session', function () {
    $other = Program::factory()->create();
    $booked = BroadcastSession::factory()->for($other)->create([
        'session_date' => '2026-10-28',
        'crewcall_time' => '09:00:00',
        'tapping_start' => '10:00:00',
        'tapping_end' => '12:00:00',
    ]);
    $booked->crew()->attach($this->announcer->id, ['position' => 'announcer']);

    $session = ($this->sessionWithCrew)(['session_date' => '2026-10-26'], $this->so);

    $this->actingAs($this->hr)
        ->from(route('sesi.edit', $session))
        ->put(route('sesi.update', $session), ($this->payload)(['tapping_start' => '09:30', 'tapping_end' => '11:00']))
        ->assertRedirect(route('sesi.edit', $session))
        ->assertSessionHasErrors(['tapping_start' => 'Bentrok dengan sesi lain milik '.$this->announcer->username.'.']);

    expect($session->refresh()->session_date->toDateString())->toBe('2026-10-26');
});

it('allows back to back sessions and ignores cancelled or other day sessions', function () {
    $other = Program::factory()->create();
    foreach ([
        ['session_date' => '2026-10-28', 'tapping_start' => '08:00:00', 'tapping_end' => '09:30:00'],
        ['session_date' => '2026-10-28', 'tapping_start' => '10:00:00', 'tapping_end' => '12:00:00', 'status' => SessionStatus::Cancelled],
        ['session_date' => '2026-10-29', 'tapping_start' => '10:00:00', 'tapping_end' => '12:00:00'],
    ] as $attributes) {
        BroadcastSession::factory()->for($other)->create($attributes)
            ->crew()->attach($this->announcer->id, ['position' => 'announcer']);
    }

    $session = ($this->sessionWithCrew)(['session_date' => '2026-10-26'], $this->so);

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)(['tapping_start' => '09:30', 'tapping_end' => '11:00']))
        ->assertSessionHasNoErrors();
});

it('does not clash a session with itself', function () {
    $session = ($this->sessionWithCrew)(['session_date' => '2026-10-28']);

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)(['session_date' => '2026-10-28', 'crewcall_time' => '07:30', 'tapping_start' => '08:00', 'tapping_end' => '09:00']))
        ->assertSessionHasNoErrors();
});

it('validates formats, time order and crew', function () {
    $session = ($this->sessionWithCrew)();

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)([
            'session_date' => '28/10/2026',
            'crewcall_time' => '10:00',
            'tapping_start' => '09:30',
            'tapping_end' => '09:00',
            'announcer_ids' => [],
        ]))
        ->assertSessionHasErrors(['session_date', 'tapping_start', 'tapping_end', 'announcer_ids']);

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)(['so_ids' => [$this->announcer->id]]))
        ->assertSessionHasErrors('so_ids.0');
});

it('forbids non hr users from session routes', function () {
    $session = ($this->sessionWithCrew)();

    foreach ([$this->announcer, $this->so] as $user) {
        $this->actingAs($user)->get(route('sesi.index'))->assertForbidden();
        $this->actingAs($user)->get(route('sesi.edit', $session))->assertForbidden();
        $this->actingAs($user)->put(route('sesi.update', $session), ($this->payload)())->assertForbidden();
        $this->actingAs($user)->post(route('sesi.postpone', $session))->assertForbidden();
        $this->actingAs($user)->post(route('sesi.cancel', $session))->assertForbidden();
    }

    expect($session->refresh()->status)->toBe(SessionStatus::Scheduled);
});
