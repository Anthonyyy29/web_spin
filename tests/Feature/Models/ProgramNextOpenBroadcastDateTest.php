<?php

use App\Enums\SessionStatus;
use App\Models\BroadcastSession;
use App\Models\Program;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function programWithPeriod(int $dayOfWeek, string $periodStart, string $periodEnd): Program
{
    return Program::factory()->create([
        'day_of_week' => $dayOfWeek,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
    ]);
}

it('returns the next open broadcast day inside the period', function () {
    $program = programWithPeriod(1, '2026-10-05', '2026-11-30');
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-11-02');
});

it('counts the start date itself when it is an open broadcast day', function () {
    $program = programWithPeriod(6, '2026-10-03', '2026-11-28');

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24 15:00'))->toDateString())->toBe('2026-10-24');
});

it('ignores cancelled sessions and sessions of other programs', function () {
    $program = programWithPeriod(1, '2026-10-05', '2026-11-30');
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26', 'status' => SessionStatus::Cancelled]);
    BroadcastSession::factory()->for(Program::factory())->create(['session_date' => '2026-10-26']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-10-26');
});

it('returns null when every broadcast day left in the period has a session', function () {
    $program = programWithPeriod(1, '2026-10-05', '2026-11-02');
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26']);
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-11-02']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24')))->toBeNull();
});

it('returns null when the period has already ended', function () {
    $program = programWithPeriod(1, '2026-10-05', '2026-10-26');

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-27')))->toBeNull();
});

it('starts at the period start when the period has not begun yet', function () {
    $program = programWithPeriod(1, '2026-11-01', '2026-11-30');

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-11-02');
});
