<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->session = BroadcastSession::factory()->create();
    $this->at = fn (string $time): CarbonImmutable => CarbonImmutable::parse('2026-10-26 '.$time, 'Asia/Jakarta');
});

it('opens check-in fifteen minutes before crewcall', function () {
    expect($this->session->isCheckInOpen(($this->at)('07:14:59')))->toBeFalse()
        ->and($this->session->isCheckInOpen(($this->at)('07:15:00')))->toBeTrue()
        ->and($this->session->checkInOpensAt()->equalTo(($this->at)('07:15:00')))->toBeTrue();
});

it('closes check-in when tapping starts', function () {
    expect($this->session->isCheckInOpen(($this->at)('07:59:59')))->toBeTrue()
        ->and($this->session->isCheckInOpen(($this->at)('08:00:00')))->toBeFalse()
        ->and($this->session->isCheckInClosed(($this->at)('07:59:59')))->toBeFalse()
        ->and($this->session->isCheckInClosed(($this->at)('08:00:00')))->toBeTrue()
        ->and($this->session->checkInClosesAt()->equalTo(($this->at)('08:00:00')))->toBeTrue();
});

it('evaluates the window in the application timezone regardless of the given instant timezone', function () {
    $utcInstant = CarbonImmutable::parse('2026-10-26 00:15:00', 'UTC');

    expect($this->session->isCheckInOpen($utcInstant))->toBeTrue()
        ->and($this->session->isCheckInOpen($utcInstant->subSecond()))->toBeFalse();
});

it('marks check-ins up to five full minutes after crewcall as present', function () {
    expect($this->session->statusForCheckIn(($this->at)('07:35:59')))->toBe(AttendanceStatus::Hadir)
        ->and($this->session->statusForCheckIn(($this->at)('07:36:00')))->toBe(AttendanceStatus::Terlambat)
        ->and($this->session->statusForCheckIn(($this->at)('07:20:00')))->toBe(AttendanceStatus::Hadir);
});

it('never opens check-in for postponed or cancelled sessions', function () {
    $postponed = BroadcastSession::factory()->postponed()->create();
    $cancelled = BroadcastSession::factory()->cancelled()->create();

    foreach ([$postponed, $cancelled] as $session) {
        expect($session->checkInOpensAt())->toBeNull()
            ->and($session->checkInClosesAt())->toBeNull()
            ->and($session->isCheckInOpen(($this->at)('07:50:00')))->toBeFalse()
            ->and($session->isCheckInClosed(($this->at)('09:00:00')))->toBeFalse();
    }
});

it('derives absent and pending statuses', function () {
    $user = User::factory()->announcer()->create();

    expect($this->session->attendanceStatusFor($user, ($this->at)('07:50:00')))->toBe(AttendanceStatus::Belum)
        ->and($this->session->attendanceStatusFor($user, ($this->at)('08:00:00')))->toBe(AttendanceStatus::TidakHadir);
});

it('returns the stored attendance status when a row exists', function () {
    $user = User::factory()->announcer()->create();
    Attendance::factory()->create([
        'broadcast_session_id' => $this->session->id,
        'user_id' => $user->id,
        'status' => AttendanceStatus::Izin,
        'checked_in_at' => null,
    ]);

    expect($this->session->attendanceStatusFor($user, ($this->at)('08:30:00')))->toBe(AttendanceStatus::Izin);
});

it('uses the already loaded attendances relation without querying again', function () {
    $user = User::factory()->announcer()->create();
    Attendance::factory()->create([
        'broadcast_session_id' => $this->session->id,
        'user_id' => $user->id,
        'status' => AttendanceStatus::Sakit,
        'checked_in_at' => null,
    ]);
    $session = BroadcastSession::query()->with('attendances')->find($this->session->id);

    DB::flushQueryLog();
    DB::enableQueryLog();

    expect($session->attendanceStatusFor($user, ($this->at)('07:00:00')))->toBe(AttendanceStatus::Sakit)
        ->and(DB::getQueryLog())->toBeEmpty();
});
