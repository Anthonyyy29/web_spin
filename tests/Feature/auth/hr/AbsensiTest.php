<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-10-26 09:00:00');

    $this->hr = User::factory()->hr()->create();
    $this->program = Program::factory()->create(['name' => 'Pagi Ceria']);

    $this->crewUser = fn (string $username, string $role = 'announcer'): User => User::factory()->create(['username' => $username, 'role' => $role]);

    $this->closedSession = function (array $attributes = []): BroadcastSession {
        return BroadcastSession::factory()->for($this->program)->create($attributes);
    };

    $this->checkIn = function (BroadcastSession $session, User $user, string $at, ?AttendanceStatus $status = null, ?string $note = null): Attendance {
        return Attendance::factory()->create([
            'broadcast_session_id' => $session->id,
            'user_id' => $user->id,
            'checked_in_at' => $at,
            'status' => $status ?? $session->statusForCheckIn(Carbon::parse($at)),
            'note' => $note,
        ]);
    };

    $this->streamed = fn ($response): array => array_map(
        fn (string $line): array => str_getcsv($line, ',', '"', ''),
        array_values(array_filter(preg_split('/\r?\n/', $response->streamedContent()))),
    );
});

it('shows each crew member\'s status for a session', function () {
    $session = ($this->closedSession)();
    $present = ($this->crewUser)('ani');
    $late = ($this->crewUser)('budi');
    $absent = ($this->crewUser)('cici', 'so');
    foreach ([$present, $late] as $user) {
        $session->crew()->attach($user->id, ['position' => 'announcer']);
    }
    $session->crew()->attach($absent->id, ['position' => 'so']);
    ($this->checkIn)($session, $present, '2026-10-26 07:20:00');
    ($this->checkIn)($session, $late, '2026-10-26 07:40:00');

    $this->actingAs($this->hr)
        ->get(route('absensi.session', $session))
        ->assertOk()
        ->assertSeeInOrder(['ani', 'Hadir', '07:20', 'budi', 'Terlambat', '07:40', 'cici', 'Tidak hadir']);

    $open = ($this->closedSession)(['session_date' => '2026-10-26', 'crewcall_time' => '10:00:00', 'tapping_start' => '10:30:00', 'tapping_end' => '11:30:00']);
    $open->crew()->attach($present->id, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->get(route('absensi.session', $open))
        ->assertOk()
        ->assertSee('Belum')
        ->assertDontSee('Tidak hadir');
});

it('renders the attendance page of a postponed session', function () {
    $session = BroadcastSession::factory()->for($this->program)->postponed()->create();
    $member = ($this->crewUser)('ani');
    $session->crew()->attach($member->id, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->get(route('absensi.session', $session))
        ->assertOk()
        ->assertSee('Ditunda')
        ->assertSee('ani')
        ->assertSee('Belum')
        ->assertDontSee(route('absensi.update', [$session, $member]));
});

it('keeps showing a member removed from the crew after checking in', function () {
    $session = ($this->closedSession)();
    $removed = ($this->crewUser)('dodi');
    ($this->checkIn)($session, $removed, '2026-10-26 07:20:00');

    $this->actingAs($this->hr)
        ->get(route('absensi.session', $session))
        ->assertOk()
        ->assertSeeInOrder(['dodi', '-', 'Hadir']);
});

it('lets hr mark a member as sick with a note', function () {
    $session = ($this->closedSession)();
    $member = ($this->crewUser)('ani');
    $session->crew()->attach($member->id, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->put(route('absensi.update', [$session, $member]), ['status' => 'sakit', 'note' => 'Demam'])
        ->assertRedirect(route('absensi.session', $session))
        ->assertSessionHas('status');

    $attendance = Attendance::where('broadcast_session_id', $session->id)->where('user_id', $member->id)->sole();

    expect($attendance->status)->toBe(AttendanceStatus::Sakit)
        ->and($attendance->note)->toBe('Demam')
        ->and($attendance->checked_in_at)->toBeNull();

    $this->actingAs($this->hr)
        ->put(route('absensi.update', [$session, $member]), ['status' => 'izin', 'note' => null])
        ->assertRedirect();

    expect($attendance->refresh()->status)->toBe(AttendanceStatus::Izin)
        ->and($attendance->note)->toBeNull();
});

it('keeps the check in time when hr overrides the status', function () {
    $session = ($this->closedSession)();
    $member = ($this->crewUser)('ani');
    $session->crew()->attach($member->id, ['position' => 'announcer']);
    $attendance = ($this->checkIn)($session, $member, '2026-10-26 07:20:00');

    $this->actingAs($this->hr)
        ->put(route('absensi.update', [$session, $member]), ['status' => 'izin', 'note' => 'Dispensasi'])
        ->assertRedirect();

    expect($attendance->refresh()->status)->toBe(AttendanceStatus::Izin)
        ->and($attendance->checked_in_at->format('Y-m-d H:i'))->toBe('2026-10-26 07:20');
});

it('resets an override back to the checked in status', function () {
    $session = ($this->closedSession)();
    $member = ($this->crewUser)('ani');
    $late = ($this->crewUser)('budi');
    $skipped = ($this->crewUser)('cici');
    foreach ([$member, $late, $skipped] as $user) {
        $session->crew()->attach($user->id, ['position' => 'announcer']);
    }
    $onTime = ($this->checkIn)($session, $member, '2026-10-26 07:20:00', AttendanceStatus::Sakit, 'Demam');
    $afterGrace = ($this->checkIn)($session, $late, '2026-10-26 07:36:00', AttendanceStatus::Izin, 'Macet');
    Attendance::factory()->create(['broadcast_session_id' => $session->id, 'user_id' => $skipped->id, 'checked_in_at' => null, 'status' => AttendanceStatus::Izin, 'note' => 'Acara']);

    foreach ([$member, $late, $skipped] as $user) {
        $this->actingAs($this->hr)
            ->put(route('absensi.update', [$session, $user]), ['status' => 'reset'])
            ->assertRedirect(route('absensi.session', $session));
    }

    expect($onTime->refresh()->status)->toBe(AttendanceStatus::Hadir)
        ->and($onTime->note)->toBeNull()
        ->and($afterGrace->refresh()->status)->toBe(AttendanceStatus::Terlambat)
        ->and($afterGrace->note)->toBeNull()
        ->and(Attendance::where('user_id', $skipped->id)->exists())->toBeFalse();
});

it('does not let hr set a check-in time', function () {
    $session = ($this->closedSession)();
    $member = ($this->crewUser)('ani');
    $session->crew()->attach($member->id, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->put(route('absensi.update', [$session, $member]), ['status' => 'izin', 'checked_in_at' => '2026-10-26 07:00:00'])
        ->assertRedirect();

    expect(Attendance::sole()->checked_in_at)->toBeNull();
});

it('rejects invalid status changes', function () {
    $session = ($this->closedSession)();
    $member = ($this->crewUser)('ani');
    $session->crew()->attach($member->id, ['position' => 'announcer']);

    $this->actingAs($this->hr)
        ->put(route('absensi.update', [$session, $member]), ['status' => 'hadir', 'note' => str_repeat('a', 256)])
        ->assertSessionHasErrors(['status', 'note']);

    expect(Attendance::count())->toBe(0);
});

it('returns not found for a user who is not part of the session', function () {
    $session = ($this->closedSession)();
    $stranger = ($this->crewUser)('eko');

    $this->actingAs($this->hr)
        ->put(route('absensi.update', [$session, $stranger]), ['status' => 'izin'])
        ->assertNotFound();

    expect(Attendance::count())->toBe(0);
});

it('only changes attendance status of scheduled sessions', function () {
    $member = ($this->crewUser)('ani');
    $postponed = BroadcastSession::factory()->for($this->program)->postponed()->create();
    $cancelled = ($this->closedSession)(['session_date' => '2026-10-19', 'status' => 'cancelled']);
    foreach ([$postponed, $cancelled] as $session) {
        $session->crew()->attach($member->id, ['position' => 'announcer']);

        $this->actingAs($this->hr)
            ->from(route('absensi.session', $session))
            ->put(route('absensi.update', [$session, $member]), ['status' => 'reset'])
            ->assertRedirect(route('absensi.session', $session))
            ->assertSessionHasErrors(['absensi' => 'Status absensi hanya bisa diubah untuk sesi terjadwal.']);
    }

    expect(Attendance::count())->toBe(0);
});

it('summarises attendance per person for a date range', function () {
    $ani = ($this->crewUser)('ani');
    $budi = ($this->crewUser)('budi');
    $cici = ($this->crewUser)('cici', 'so');
    $dodi = ($this->crewUser)('dodi');
    $nobody = ($this->crewUser)('eko');
    $removed = ($this->crewUser)('fani');

    $make = function (string $date, array $attributes = []) use ($ani, $budi, $cici): BroadcastSession {
        $session = BroadcastSession::factory()->for($this->program)->create(['session_date' => $date, 'regular_date' => $date, ...$attributes]);
        $session->crew()->attach([$ani->id => ['position' => 'announcer'], $budi->id => ['position' => 'announcer'], $cici->id => ['position' => 'so']]);

        return $session;
    };

    $first = $make('2026-10-05');
    ($this->checkIn)($first, $ani, '2026-10-05 07:20:00');
    ($this->checkIn)($first, $budi, '2026-10-05 07:50:00');
    ($this->checkIn)($first, $removed, '2026-10-05 07:20:00');
    Attendance::factory()->create(['broadcast_session_id' => $first->id, 'user_id' => $cici->id, 'checked_in_at' => null, 'status' => AttendanceStatus::Sakit]);

    $second = $make('2026-10-12');
    ($this->checkIn)($second, $ani, '2026-10-12 07:40:00');
    Attendance::factory()->create(['broadcast_session_id' => $second->id, 'user_id' => $budi->id, 'checked_in_at' => null, 'status' => AttendanceStatus::Izin]);

    $outOfRange = $make('2026-09-28');
    ($this->checkIn)($outOfRange, $ani, '2026-09-28 07:20:00');

    $make('2026-10-19', ['status' => 'cancelled']);
    $make('2026-10-27');
    $postponed = BroadcastSession::factory()->for($this->program)->postponed()->create();
    $postponed->crew()->attach($ani->id, ['position' => 'announcer']);
    $make('2026-10-26', ['crewcall_time' => '10:00:00', 'tapping_start' => '10:30:00', 'tapping_end' => '11:30:00']);

    $response = $this->actingAs($this->hr)
        ->get(route('absensi.index', ['from' => '2026-10-01', 'to' => '2026-10-26']))
        ->assertOk()
        ->assertSeeInOrder(['ani', 'budi', 'dodi', 'cici']);

    $rows = collect($response->viewData('rows'))->keyBy(fn ($row) => $row['user']->username)
        ->map(fn ($row) => [$row['hadir'], $row['terlambat'], $row['izin'], $row['sakit'], $row['tidak_hadir']]);

    expect($rows->keys()->all())->toBe(['ani', 'budi', 'dodi', 'eko', 'fani', 'cici'])
        ->and($rows['ani'])->toBe([1, 1, 0, 0, 0])
        ->and($rows['budi'])->toBe([0, 1, 1, 0, 0])
        ->and($rows['cici'])->toBe([0, 0, 0, 1, 1])
        ->and($rows['dodi'])->toBe([0, 0, 0, 0, 0])
        ->and($rows['eko'])->toBe([0, 0, 0, 0, 0])
        ->and($rows['fani'])->toBe([1, 0, 0, 0, 0]);
});

it('defaults the summary range to this month and validates it', function () {
    $response = $this->actingAs($this->hr)->get(route('absensi.index'))->assertOk();

    expect($response->viewData('from'))->toBe('2026-10-01')
        ->and($response->viewData('to'))->toBe('2026-10-26');

    $this->actingAs($this->hr)
        ->from(route('absensi.index'))
        ->get(route('absensi.index', ['from' => '2026/10/01', 'to' => '2026-10-26']))
        ->assertRedirect(route('absensi.index'))
        ->assertSessionHasErrors('from');

    $this->actingAs($this->hr)
        ->from(route('absensi.index'))
        ->get(route('absensi.export', ['from' => '2026-10-20', 'to' => '2026-10-01']))
        ->assertRedirect(route('absensi.index'))
        ->assertSessionHasErrors('to');
});

it('exports the per person summary as csv', function () {
    $ani = ($this->crewUser)('ani');
    $session = ($this->closedSession)(['session_date' => '2026-10-12']);
    $session->crew()->attach($ani->id, ['position' => 'announcer']);
    ($this->checkIn)($session, $ani, '2026-10-12 07:40:00');

    $response = $this->actingAs($this->hr)
        ->get(route('absensi.export', ['from' => '2026-10-01', 'to' => '2026-10-26']))
        ->assertOk()
        ->assertDownload('rekap-absensi-2026-10-01-2026-10-26.csv');

    $lines = ($this->streamed)($response);

    expect($lines[0])->toBe(['Username', 'Role', 'Hadir', 'Terlambat', 'Tidak hadir', 'Izin', 'Sakit'])
        ->and($lines[1])->toBe(['ani', 'announcer', '0', '1', '0', '0', '0'])
        ->and($lines)->toHaveCount(2);
});

it('exports a session as csv', function () {
    $session = ($this->closedSession)();
    $ani = ($this->crewUser)('ani');
    $cici = ($this->crewUser)('cici', 'so');
    $session->crew()->attach([$ani->id => ['position' => 'announcer'], $cici->id => ['position' => 'so']]);
    ($this->checkIn)($session, $ani, '2026-10-26 07:40:00', null, 'Macet, banjir');

    $response = $this->actingAs($this->hr)
        ->get(route('absensi.session.export', $session))
        ->assertOk()
        ->assertDownload('absensi-pagi-ceria-2026-10-26.csv');

    $lines = ($this->streamed)($response);

    expect($lines[0])->toBe(['Program', 'Tanggal', 'Crewcall', 'Username', 'Posisi', 'Status', 'Jam check-in', 'Catatan'])
        ->and($lines[1])->toBe(['Pagi Ceria', '2026-10-26', '07:30', 'ani', 'announcer', 'Terlambat', '07:40', 'Macet, banjir'])
        ->and($lines[2])->toBe(['Pagi Ceria', '2026-10-26', '07:30', 'cici', 'so', 'Tidak hadir', '', ''])
        ->and($lines)->toHaveCount(3);
});

it('exports a postponed session as csv without a date', function () {
    $session = BroadcastSession::factory()->for($this->program)->postponed()->create();
    $ani = ($this->crewUser)('ani');
    $session->crew()->attach($ani->id, ['position' => 'announcer']);

    $response = $this->actingAs($this->hr)
        ->get(route('absensi.session.export', $session))
        ->assertOk()
        ->assertDownload('absensi-pagi-ceria-ditunda.csv');

    expect(($this->streamed)($response)[1])->toBe(['Pagi Ceria', '', '', 'ani', 'announcer', 'Belum', '', '']);
});

it('forbids non hr users from attendance routes', function () {
    $session = ($this->closedSession)();
    $member = ($this->crewUser)('ani');
    $session->crew()->attach($member->id, ['position' => 'announcer']);

    foreach ([$member, ($this->crewUser)('cici', 'so')] as $user) {
        $this->actingAs($user)->get(route('absensi.index'))->assertForbidden();
        $this->actingAs($user)->get(route('absensi.export'))->assertForbidden();
        $this->actingAs($user)->get(route('absensi.session', $session))->assertForbidden();
        $this->actingAs($user)->get(route('absensi.session.export', $session))->assertForbidden();
        $this->actingAs($user)->put(route('absensi.update', [$session, $member]), ['status' => 'izin'])->assertForbidden();
    }

    expect(Attendance::count())->toBe(0);
});

it('links to the attendance page from the session list and the nav', function () {
    $session = ($this->closedSession)();

    $this->actingAs($this->hr)
        ->get(route('sesi.index'))
        ->assertOk()
        ->assertSee(route('absensi.session', $session))
        ->assertSee(route('absensi.index'));
});
