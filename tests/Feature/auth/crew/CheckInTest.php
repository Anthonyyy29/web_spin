<?php

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->announcer = User::factory()->announcer()->create();
    $this->so = User::factory()->so()->create();
    $this->program = Program::factory()->create(['name' => 'Pagi Ceria']);

    $this->crewSession = function (User $user, array $attributes = []): BroadcastSession {
        $session = BroadcastSession::factory()->for($this->program)->create($attributes);
        $session->crew()->attach($user->id, ['position' => $user->role]);

        return $session;
    };
});

it('lets an announcer check in on time', function () {
    $session = ($this->crewSession)($this->announcer);
    $this->travelTo('2026-10-26 07:20:00');

    $this->actingAs($this->announcer)
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertRedirect(route('announcer.sesi.index'))
        ->assertSessionHas('status', 'Check-in tercatat: Hadir 07:20.');

    $attendance = Attendance::where('broadcast_session_id', $session->id)->where('user_id', $this->announcer->id)->firstOrFail();
    expect($attendance->status)->toBe(AttendanceStatus::Hadir)
        ->and($attendance->checked_in_at->format('Y-m-d H:i:s'))->toBe('2026-10-26 07:20:00');
});

it('lets a sound operator check in late', function () {
    $session = ($this->crewSession)($this->so);
    $this->travelTo('2026-10-26 07:40:00');

    $this->actingAs($this->so)
        ->from(route('so.sesi.index'))
        ->post(route('so.sesi.checkIn', $session))
        ->assertRedirect(route('so.sesi.index'))
        ->assertSessionHas('status', 'Check-in tercatat: Terlambat 07:40.');

    expect(Attendance::where('user_id', $this->so->id)->firstOrFail()->status)->toBe(AttendanceStatus::Terlambat);
});

it('rejects check-in outside the window', function (string $moment) {
    $session = ($this->crewSession)($this->announcer);
    $this->travelTo($moment);

    $this->actingAs($this->announcer)
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertRedirect(route('announcer.sesi.index'))
        ->assertSessionHasErrors(['checkIn' => 'Check-in hanya bisa dari 15 menit sebelum crewcall sampai tapping dimulai.']);

    expect(Attendance::count())->toBe(0);
})->with(['too early' => '2026-10-26 07:14:59', 'at tapping start' => '2026-10-26 08:00:00']);

it('rejects check-in for a non crew member', function () {
    $session = ($this->crewSession)($this->so);
    $this->travelTo('2026-10-26 07:20:00');

    $this->actingAs($this->announcer)
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertForbidden();

    expect(Attendance::count())->toBe(0);
});

it('rejects check-in for postponed and cancelled sessions', function (string $state) {
    $session = ($this->crewSession)($this->announcer);
    $session->update(
        $state === 'postponed'
            ? ['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]
            : ['status' => SessionStatus::Cancelled]
    );
    $this->travelTo('2026-10-26 07:20:00');

    $this->actingAs($this->announcer)
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertRedirect(route('announcer.sesi.index'))
        ->assertSessionHasErrors(['checkIn' => 'Sesi ini tidak bisa di-check-in.']);

    expect(Attendance::count())->toBe(0);
})->with(['postponed', 'cancelled']);

it('rejects a second check-in', function () {
    $session = ($this->crewSession)($this->announcer);
    $this->travelTo('2026-10-26 07:20:00');

    $this->actingAs($this->announcer)->post(route('announcer.sesi.checkIn', $session));
    $this->travelTo('2026-10-26 07:50:00');

    $this->actingAs($this->announcer)
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertSessionHasErrors(['checkIn' => 'Kamu sudah check-in di sesi ini.']);

    $attendance = Attendance::sole();
    expect($attendance->status)->toBe(AttendanceStatus::Hadir);
});

it('reports an already checked-in error when a concurrent insert wins the race', function () {
    $session = ($this->crewSession)($this->announcer);
    $this->travelTo('2026-10-26 07:20:00');

    Attendance::creating(function () use ($session): void {
        Attendance::withoutEvents(fn () => Attendance::factory()->create([
            'broadcast_session_id' => $session->id,
            'user_id' => $this->announcer->id,
        ]));
    });

    $this->actingAs($this->announcer)
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertRedirect(route('announcer.sesi.index'))
        ->assertSessionHasErrors(['checkIn' => 'Kamu sudah check-in di sesi ini.']);

    expect(Attendance::count())->toBe(1);
});

it('shows my sessions including postponed ones without errors', function () {
    $this->travelTo('2026-10-26 07:20:00');
    $open = ($this->crewSession)($this->announcer);
    $postponed = ($this->crewSession)($this->announcer, ['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);
    ($this->crewSession)($this->announcer, ['session_date' => '2026-10-27', 'status' => SessionStatus::Cancelled]);
    ($this->crewSession)($this->so);

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSee('Pagi Ceria')
        ->assertSee('Hari ini')
        ->assertSee('07:30')
        ->assertSee('Ditunda')
        ->assertDontSee('Batal')
        ->assertSee(route('announcer.sesi.checkIn', $open), false)
        ->assertDontSee(route('announcer.sesi.checkIn', $postponed), false)
        ->assertSeeInOrder(['26 Okt 2026', 'Ditunda']);
});

it('keeps today in upcoming with the check-in button even with many past sessions', function () {
    $this->travelTo('2026-10-26 07:20:00');
    foreach (range(1, 20) as $day) {
        ($this->crewSession)($this->announcer, ['session_date' => now()->subDays($day)->toDateString()]);
    }
    $today = ($this->crewSession)($this->announcer);

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSeeInOrder(['Mendatang', 'Hari ini', route('announcer.sesi.checkIn', $today), 'Riwayat'], false);
});

it('lists past sessions under history newest first and paginates them', function () {
    $this->travelTo('2026-10-26 07:20:00');
    ($this->crewSession)($this->announcer, ['session_date' => '2026-10-20']);
    ($this->crewSession)($this->announcer, ['session_date' => '2026-10-22']);
    ($this->crewSession)($this->announcer, ['session_date' => '2026-10-21']);
    foreach (range(1, 15) as $day) {
        ($this->crewSession)($this->announcer, ['session_date' => '2026-09-'.str_pad((string) $day, 2, '0', STR_PAD_LEFT)]);
    }

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSeeInOrder(['Belum ada sesi mendatang.', 'Riwayat', '22 Okt 2026', '21 Okt 2026', '20 Okt 2026'])
        ->assertDontSee('01 Sep 2026');

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('01 Sep 2026');
});

it('shows empty states for upcoming and history', function () {
    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSee('Belum ada sesi mendatang.')
        ->assertSee('Belum ada riwayat sesi.');
});

it('lists postponed sessions under upcoming after dated ones', function () {
    $this->travelTo('2026-10-26 07:20:00');
    ($this->crewSession)($this->announcer, ['status' => SessionStatus::Postponed, 'session_date' => null, 'crewcall_time' => null, 'tapping_start' => null, 'tapping_end' => null]);
    ($this->crewSession)($this->announcer, ['session_date' => '2026-11-02']);
    ($this->crewSession)($this->announcer, ['session_date' => '2026-10-19']);

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSeeInOrder(['Mendatang', '02 Nov 2026', 'Ditunda', 'Riwayat', '19 Okt 2026']);
});

it('hides the check-in button after checking in and shows the status and time', function () {
    $this->travelTo('2026-10-26 07:20:00');
    $session = ($this->crewSession)($this->announcer);
    Attendance::factory()->create([
        'broadcast_session_id' => $session->id,
        'user_id' => $this->announcer->id,
        'checked_in_at' => '2026-10-26 07:36:00',
        'status' => AttendanceStatus::Terlambat,
    ]);

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSee('Terlambat')
        ->assertSee('07:36')
        ->assertDontSee(route('announcer.sesi.checkIn', $session), false);
});

it('shows the closed status after the window ends', function () {
    $this->travelTo('2026-10-26 08:05:00');
    ($this->crewSession)($this->announcer);

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSee('Tidak hadir');
});

it('renders the check-in error on the page', function () {
    $this->travelTo('2026-10-26 07:00:00');
    $session = ($this->crewSession)($this->announcer);

    $this->actingAs($this->announcer)
        ->followingRedirects()
        ->from(route('announcer.sesi.index'))
        ->post(route('announcer.sesi.checkIn', $session))
        ->assertOk()
        ->assertSee('Check-in hanya bisa dari 15 menit sebelum crewcall sampai tapping dimulai.');
});

it('redirects a sound operator to their sessions after login', function () {
    $user = User::factory()->so()->create(['password' => 'secret-pass']);

    $this->post(route('login'), ['username' => $user->username, 'password' => 'secret-pass'])
        ->assertRedirect(route('so.sesi.index'));
});

it('keeps roles out of each other\'s areas', function () {
    $this->actingAs($this->so)->get('/announcer/sesi')->assertForbidden();
    $this->actingAs($this->so)->get('/hr/program')->assertForbidden();
    $this->actingAs($this->announcer)->get('/so/sesi')->assertForbidden();
});

it('shows my sessions when hr marked me sakit without a check-in', function () {
    $this->travelTo('2026-10-26 07:00:00');
    $hr = User::factory()->hr()->create();
    $session = ($this->crewSession)($this->announcer);

    $this->actingAs($hr)
        ->put(route('absensi.update', [$session, $this->announcer]), ['status' => 'sakit'])
        ->assertRedirect(route('absensi.session', $session));

    expect(Attendance::sole()->checked_in_at)->toBeNull();

    $this->actingAs($this->announcer)
        ->get(route('announcer.sesi.index'))
        ->assertOk()
        ->assertSee('Sakit');
});

it('forbids hr users from checking in', function () {
    $hr = User::factory()->hr()->create();
    $session = ($this->crewSession)($this->announcer);
    $this->travelTo('2026-10-26 07:20:00');

    $this->actingAs($hr)->post(route('announcer.sesi.checkIn', $session))->assertForbidden();
    $this->actingAs($hr)->post(route('so.sesi.checkIn', $session))->assertForbidden();

    expect(Attendance::count())->toBe(0);
});
