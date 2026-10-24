<?php

use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->program = Program::factory()->create([
        'period_start' => '2026-10-05',
        'period_end' => '2027-01-25',
    ]);
    $this->announcer = User::factory()->announcer()->create();
    $this->so = User::factory()->so()->create();
    $this->program->crew()->attach($this->announcer, ['position' => 'announcer']);
    $this->program->crew()->attach($this->so, ['position' => 'so']);
    $this->today = CarbonImmutable::parse('2026-10-24');
});

it('creates a session for every matching weekday in the period', function () {
    $this->program->syncSessions($this->today);

    $sessions = $this->program->sessions()->orderBy('regular_date')->get();

    expect($sessions)->toHaveCount(17)
        ->and($sessions->first()->regular_date->toDateString())->toBe('2026-10-05')
        ->and($sessions->last()->regular_date->toDateString())->toBe('2027-01-25');

    $sessions->each(function (BroadcastSession $session) {
        expect($session->session_date->toDateString())->toBe($session->regular_date->toDateString())
            ->and($session->crewcall_time)->toBe('07:30:00')
            ->and($session->is_customized)->toBeFalse()
            ->and($session->crew)->toHaveCount(2)
            ->and($session->crew->firstWhere('id', $this->so->id)->pivot->position)->toBe('so');
    });
});

it('updates only upcoming sessions that were not customized', function () {
    $this->program->syncSessions($this->today);

    $customized = $this->program->sessions()->whereDate('regular_date', '2026-11-09')->first();
    $customized->update(['is_customized' => true, 'crewcall_time' => '10:00:00']);

    $this->program->update(['crewcall_time' => '08:00:00']);
    $this->program->syncSessions(CarbonImmutable::parse('2026-11-01'));

    $sessions = $this->program->sessions()->get();
    $before = $sessions->filter(fn ($s) => $s->regular_date->toDateString() < '2026-11-01');
    $upcoming = $sessions->filter(fn ($s) => $s->regular_date->toDateString() >= '2026-11-01' && $s->id !== $customized->id);

    expect($before)->not->toBeEmpty()
        ->and($before->every(fn ($s) => $s->crewcall_time === '07:30:00'))->toBeTrue()
        ->and($customized->fresh()->crewcall_time)->toBe('10:00:00')
        ->and($upcoming)->not->toBeEmpty()
        ->and($upcoming->every(fn ($s) => $s->crewcall_time === '08:00:00'))->toBeTrue();
});

it('does not recreate a moved session on its regular date', function () {
    $this->program->syncSessions($this->today);

    $moved = $this->program->sessions()->whereDate('regular_date', '2026-11-02')->first();
    $moved->update(['session_date' => '2026-11-03', 'is_customized' => true]);

    $this->program->syncSessions($this->today);

    expect($this->program->sessions()->whereDate('regular_date', '2026-11-02')->count())->toBe(1)
        ->and($this->program->sessions()->count())->toBe(17)
        ->and($moved->fresh()->session_date->toDateString())->toBe('2026-11-03');
});

it('removes sessions outside a shortened period unless they have attendance', function () {
    $this->program->syncSessions($this->today);

    $withAttendance = $this->program->sessions()->whereDate('regular_date', '2026-12-07')->first();
    Attendance::factory()->for($withAttendance, 'session')->for($this->announcer)->create();

    $this->program->update(['period_end' => '2026-11-30']);
    $this->program->syncSessions($this->today);

    $remaining = $this->program->sessions()->orderBy('regular_date')->get()
        ->map(fn ($s) => $s->regular_date->toDateString())
        ->all();

    expect($remaining)->toBe([
        '2026-10-05', '2026-10-12', '2026-10-19', '2026-10-26',
        '2026-11-02', '2026-11-09', '2026-11-16', '2026-11-23', '2026-11-30',
        '2026-12-07',
    ]);
});

it('keeps attendance when a crew member is removed from the program', function () {
    $this->program->syncSessions($this->today);

    $session = $this->program->sessions()->whereDate('regular_date', '2026-10-26')->first();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->program->crew()->detach($this->announcer);
    $this->program->syncSessions($this->today);

    expect($session->fresh()->crew->pluck('id')->all())->toBe([$this->so->id])
        ->and($session->attendances()->where('user_id', $this->announcer->id)->count())->toBe(1);
});

it('does not create past sessions for a program that already has sessions', function () {
    $this->program->syncSessions($this->today);
    $this->program->sessions()->whereDate('regular_date', '2026-10-05')->delete();

    $this->program->syncSessions($this->today);

    expect($this->program->sessions()->whereDate('regular_date', '2026-10-05')->exists())->toBeFalse()
        ->and($this->program->sessions()->count())->toBe(16);
});

it('leaves today\'s session untouched once check-in has opened but still updates later sessions', function () {
    $this->program->syncSessions($this->today);
    $newAnnouncer = User::factory()->announcer()->create();

    $this->program->update(['crewcall_time' => '09:00:00', 'tapping_start' => '09:30:00', 'tapping_end' => '11:00:00']);
    $this->program->crew()->sync([$newAnnouncer->id => ['position' => 'announcer']]);
    $this->program->syncSessions(CarbonImmutable::parse('2026-10-26 10:00:00'));

    $today = $this->program->sessions()->whereDate('regular_date', '2026-10-26')->first();
    $nextWeek = $this->program->sessions()->whereDate('regular_date', '2026-11-02')->first();

    expect($today->crewcall_time)->toBe('07:30:00')
        ->and($today->tapping_start)->toBe('08:00:00')
        ->and($today->crew->pluck('id')->sort()->values()->all())->toBe(collect([$this->announcer->id, $this->so->id])->sort()->values()->all())
        ->and($nextWeek->crewcall_time)->toBe('09:00:00')
        ->and($nextWeek->crew->pluck('id')->all())->toBe([$newAnnouncer->id]);
});

it('keeps today\'s session when its program no longer matches after check-in opened', function () {
    $this->program->syncSessions($this->today);
    $session = $this->program->sessions()->whereDate('regular_date', '2026-10-26')->first();

    $this->program->update(['day_of_week' => 2]);
    $this->program->syncSessions(CarbonImmutable::parse('2026-10-26 07:15:00'));

    expect($session->fresh())->not->toBeNull();
});

it('still updates today\'s session before check-in opens', function () {
    $this->program->syncSessions($this->today);

    $this->program->update(['crewcall_time' => '09:00:00']);
    $this->program->syncSessions(CarbonImmutable::parse('2026-10-26 07:14:59'));

    expect($this->program->sessions()->whereDate('regular_date', '2026-10-26')->first()->crewcall_time)->toBe('09:00:00');
});

it('leaves extra sessions alone and still creates the regular session on the same date', function () {
    $this->program->update(['period_end' => '2027-01-25']);
    $this->program->syncSessions($this->today);
    $extra = BroadcastSession::factory()->for($this->program)->create([
        'regular_date' => null,
        'session_date' => '2027-02-01',
        'crewcall_time' => '13:00:00',
        'tapping_start' => '13:30:00',
        'tapping_end' => '15:00:00',
        'is_customized' => true,
    ]);

    $this->program->update(['period_end' => '2027-02-01', 'crewcall_time' => '07:00:00']);
    $this->program->syncSessions($this->today);

    expect($this->program->sessions()->whereDate('regular_date', '2027-02-01')->count())->toBe(1)
        ->and($extra->refresh()->crewcall_time)->toBe('13:00:00')
        ->and($extra->regular_date)->toBeNull()
        ->and($extra->session_date->toDateString())->toBe('2027-02-01');
});
