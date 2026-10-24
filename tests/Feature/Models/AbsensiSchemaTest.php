<?php

use App\Enums\AttendanceStatus;
use App\Enums\CrewPosition;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links a program, its session crew and attendance', function () {
    $program = Program::factory()->create();
    $session = BroadcastSession::factory()->for($program)->create();
    $user = User::factory()->so()->create();

    $program->crew()->attach($user, ['position' => 'so']);
    $session->crew()->attach($user, ['position' => 'so']);
    Attendance::factory()->for($session, 'session')->for($user)->create();

    expect($session->crew->first()->pivot->position)->toBe('so')
        ->and($session->attendances->first()->status)->toBe(AttendanceStatus::Hadir)
        ->and($session->program->is($program))->toBeTrue()
        ->and($program->sessions)->toHaveCount(1)
        ->and($program->crew->first()->pivot->position)->toBe('so')
        ->and($user->broadcastSessions->first()->is($session))->toBeTrue()
        ->and($user->programs->first()->is($program))->toBeTrue()
        ->and($user->attendances)->toHaveCount(1)
        ->and($user->attendances->first()->session->is($session))->toBeTrue();
});

it('uses the Jakarta timezone', function () {
    expect(config('app.timezone'))->toBe('Asia/Jakarta');
});

it('casts session fields and builds factory states', function () {
    $session = BroadcastSession::factory()->create();
    $postponed = BroadcastSession::factory()->postponed()->create();
    $cancelled = BroadcastSession::factory()->cancelled()->create();

    expect($session->status)->toBe(SessionStatus::Scheduled)
        ->and($session->is_customized)->toBeFalse()
        ->and($session->regular_date->toDateString())->toBe('2026-10-26')
        ->and($session->session_date->toDateString())->toBe('2026-10-26')
        ->and($postponed->status)->toBe(SessionStatus::Postponed)
        ->and($postponed->session_date)->toBeNull()
        ->and($postponed->crewcall_time)->toBeNull()
        ->and($postponed->regular_date->toDateString())->toBe('2026-10-26')
        ->and($cancelled->status)->toBe(SessionStatus::Cancelled);
});

it('exposes enum labels', function () {
    expect(SessionStatus::Postponed->label())->toBe('Ditunda')
        ->and(AttendanceStatus::TidakHadir->label())->toBe('Tidak hadir')
        ->and(CrewPosition::So->label())->toBe('SO');
});
