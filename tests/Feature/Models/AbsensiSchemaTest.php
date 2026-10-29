<?php

use App\Enums\AttendanceStatus;
use App\Enums\CrewPosition;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('links a program, its session crew and attendance', function () {
    $program = Program::factory()->create();
    $session = BroadcastSession::factory()->for($program)->create();
    $user = User::factory()->so()->create();

    $program->crew()->attach($user, ['position' => 'sound_operator']);
    $session->crew()->attach($user, ['position' => 'sound_operator']);
    Attendance::factory()->for($session, 'session')->for($user)->create();

    expect($session->crew->first()->pivot->position)->toBe('sound_operator')
        ->and($session->attendances->first()->status)->toBe(AttendanceStatus::Hadir)
        ->and($session->program->is($program))->toBeTrue()
        ->and($program->sessions)->toHaveCount(1)
        ->and($program->crew->first()->pivot->position)->toBe('sound_operator')
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

it('has no program period columns', function () {
    expect(Schema::hasColumns('programs', ['period_start']))->toBeFalse()
        ->and(Schema::hasColumns('programs', ['period_end']))->toBeFalse();
});

it('has no session customization column and requires the original date', function () {
    $program = Program::factory()->create();

    expect(Schema::hasColumn('broadcast_sessions', 'is_customized'))->toBeFalse()
        ->and(fn () => DB::table('broadcast_sessions')->insert([
            'program_id' => $program->id,
            'status' => 'scheduled',
            'regular_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
});

it('backfills missing original dates before requiring them', function () {
    $migration = require database_path('migrations/2026_10_29_145332_remove_period_and_session_customization.php');
    $program = Program::factory()->create();

    $migration->down();

    $row = ['program_id' => $program->id, 'status' => 'scheduled', 'regular_date' => null, 'created_at' => '2026-10-20 09:00:00', 'updated_at' => '2026-10-20 09:00:00'];
    $moved = DB::table('broadcast_sessions')->insertGetId([...$row, 'session_date' => '2026-10-15']);
    $unscheduled = DB::table('broadcast_sessions')->insertGetId([...$row, 'session_date' => null]);

    $undated = DB::table('broadcast_sessions')->insertGetId([...$row, 'created_at' => null, 'session_date' => null]);

    $migration->up();

    expect(DB::table('broadcast_sessions')->where('id', $moved)->value('regular_date'))->toStartWith('2026-10-15')
        ->and(DB::table('broadcast_sessions')->where('id', $unscheduled)->value('regular_date'))->toStartWith('2026-10-20')
        ->and(DB::table('broadcast_sessions')->where('id', $undated)->value('regular_date'))->toStartWith(today()->toDateString())
        ->and(Schema::hasColumn('broadcast_sessions', 'is_customized'))->toBeFalse();
});
