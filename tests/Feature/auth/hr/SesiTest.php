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
    $attendance = Attendance::factory()->for($session, 'session')->for($this->announcer)->create(['checked_in_at' => null, 'status' => 'izin']);

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
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create(['checked_in_at' => null, 'status' => 'izin']);

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

it('refuses to postpone a session that already has a check-in', function () {
    $session = ($this->sessionWithCrew)();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)
        ->post(route('sesi.postpone', $session))
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHasErrors(['sesi' => 'Sesi ini sudah punya check-in, jadi tanggal dan jamnya tidak bisa diubah.']);

    expect($session->refresh()->status)->toBe(SessionStatus::Scheduled)
        ->and($session->session_date->toDateString())->toBe('2026-10-26');
});

it('still postpones a session whose attendance was only marked by hr', function () {
    $session = ($this->sessionWithCrew)();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create(['checked_in_at' => null, 'status' => 'sakit']);

    $this->actingAs($this->hr)
        ->post(route('sesi.postpone', $session))
        ->assertSessionHasNoErrors();

    expect($session->refresh()->status)->toBe(SessionStatus::Postponed);
});

it('refuses to change the date or times of a session that already has a check-in', function (array $change) {
    $session = ($this->sessionWithCrew)();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)
        ->from(route('sesi.edit', $session))
        ->put(route('sesi.update', $session), ($this->payload)(['session_date' => '2026-10-26', 'crewcall_time' => '07:30', 'tapping_start' => '08:00', 'tapping_end' => '09:30', ...$change]))
        ->assertRedirect(route('sesi.edit', $session))
        ->assertSessionHasErrors(['session_date' => 'Sesi ini sudah punya check-in, jadi tanggal dan jamnya tidak bisa diubah.']);

    expect($session->refresh()->session_date->toDateString())->toBe('2026-10-26')
        ->and($session->crewcall_time)->toBe('07:30:00');
})->with([
    'date' => [['session_date' => '2026-10-28']],
    'crewcall' => [['crewcall_time' => '07:45']],
    'tapping start' => [['tapping_start' => '08:15']],
    'tapping end' => [['tapping_end' => '10:00']],
]);

it('allows a crew only change on a session that already has a check-in', function () {
    $session = ($this->sessionWithCrew)();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)
        ->put(route('sesi.update', $session), ($this->payload)([
            'session_date' => '2026-10-26',
            'crewcall_time' => '07:30',
            'tapping_start' => '08:00',
            'tapping_end' => '09:30',
        ]))
        ->assertRedirect(route('sesi.index'))
        ->assertSessionHasNoErrors();

    expect($session->refresh()->crew()->wherePivot('position', 'so')->pluck('users.id')->all())->toBe([$this->so->id]);
});

it('still cancels a session that already has a check-in', function () {
    $session = ($this->sessionWithCrew)();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)->post(route('sesi.cancel', $session))->assertSessionHasNoErrors();

    expect($session->refresh()->status)->toBe(SessionStatus::Cancelled);
});

it('shows today first on the default upcoming tab even with many future sessions', function () {
    $today = ($this->sessionWithCrew)(['session_date' => '2026-10-24']);
    foreach (range(1, 20) as $day) {
        ($this->sessionWithCrew)(['session_date' => now()->addDays($day)->toDateString()]);
    }
    $past = ($this->sessionWithCrew)(['session_date' => '2026-10-20']);

    $response = $this->actingAs($this->hr)->get(route('sesi.index'))->assertOk();

    expect($response->viewData('sessions')->first()->id)->toBe($today->id)
        ->and($response->viewData('sessions')->pluck('id'))->not->toContain($past->id)
        ->and($response->viewData('sessions')->total())->toBe(21)
        ->and($response->viewData('sessions')->pluck('session_date')->map->toDateString()->all())
        ->toBe(collect($response->viewData('sessions')->pluck('session_date')->map->toDateString()->all())->sort()->values()->all());
});

it('lists past sessions only under the history tab, newest first', function () {
    $older = ($this->sessionWithCrew)(['session_date' => '2026-10-19']);
    $newer = ($this->sessionWithCrew)(['session_date' => '2026-10-23']);
    $today = ($this->sessionWithCrew)(['session_date' => '2026-10-24']);

    $history = $this->actingAs($this->hr)->get(route('sesi.index', ['waktu' => 'riwayat']))->assertOk();

    expect($history->viewData('sessions')->pluck('id')->all())->toBe([$newer->id, $older->id])
        ->and($history->viewData('sessions')->pluck('id'))->not->toContain($today->id);
});

it('keeps the program filter across tabs and marks the active tab', function () {
    $other = Program::factory()->create(['name' => 'Sore Santai']);
    ($this->sessionWithCrew)(['session_date' => '2026-10-26']);
    BroadcastSession::factory()->for($other)->create(['session_date' => '2026-11-02']);

    $this->actingAs($this->hr)
        ->get(route('sesi.index', ['program' => $this->program->id]))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee(e(route('sesi.index', ['program' => $this->program->id, 'waktu' => 'riwayat'])), false)
        ->assertDontSee('Sore Santai</td>', false);

    $this->actingAs($this->hr)
        ->get(route('sesi.index', ['program' => $this->program->id, 'waktu' => 'riwayat']))
        ->assertOk()
        ->assertSee('aria-current="page"', false)
        ->assertSee(route('sesi.index', ['program' => $this->program->id]), false);
});

it('shows a reactivation hint on the edit page of a cancelled session only', function () {
    $cancelled = ($this->sessionWithCrew)(['status' => SessionStatus::Cancelled]);
    $scheduled = ($this->sessionWithCrew)();

    $this->actingAs($this->hr)->get(route('sesi.edit', $cancelled))->assertOk()
        ->assertSee('Menyimpan akan mengaktifkan kembali sesi ini.');
    $this->actingAs($this->hr)->get(route('sesi.edit', $scheduled))->assertOk()
        ->assertDontSee('Menyimpan akan mengaktifkan kembali sesi ini.');
});
