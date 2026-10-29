<?php

use App\Enums\SessionStatus;
use App\Models\BroadcastSession;
use App\Models\Program;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the next broadcast day without a session', function () {
    $program = Program::factory()->create(['day_of_week' => 1]);
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-11-02');
});

it('counts the start date itself when it is an open broadcast day', function () {
    $program = Program::factory()->create(['day_of_week' => 6]);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24 15:00'))->toDateString())->toBe('2026-10-24');
});

it('ignores cancelled sessions and sessions of other programs', function () {
    $program = Program::factory()->create(['day_of_week' => 1]);
    BroadcastSession::factory()->for($program)->create(['session_date' => '2026-10-26', 'status' => SessionStatus::Cancelled]);
    BroadcastSession::factory()->for(Program::factory())->create(['session_date' => '2026-10-26']);

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-10-26');
});

it('skips several consecutive weeks that already have sessions', function () {
    $program = Program::factory()->create(['day_of_week' => 1]);

    foreach (['2026-10-26', '2026-11-02', '2026-11-09'] as $date) {
        BroadcastSession::factory()->for($program)->create(['session_date' => $date]);
    }

    expect($program->nextOpenBroadcastDate(CarbonImmutable::parse('2026-10-24'))->toDateString())->toBe('2026-11-16');
});
