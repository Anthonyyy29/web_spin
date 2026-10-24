<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\BroadcastSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

#[Fillable(['program_id', 'regular_date', 'session_date', 'crewcall_time', 'tapping_start', 'tapping_end', 'status', 'is_customized'])]
class BroadcastSession extends Model
{
    /** @use HasFactory<BroadcastSessionFactory> */
    use HasFactory;

    protected $table = 'broadcast_sessions';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'regular_date' => 'date',
            'session_date' => 'date',
            'status' => SessionStatus::class,
            'is_customized' => 'bool',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_crew', 'broadcast_session_id')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'broadcast_session_id');
    }

    /**
     * Get the moment crew may start checking in: fifteen minutes before crewcall.
     * Null unless the session is scheduled and has a date and crewcall time.
     */
    public function checkInOpensAt(): ?CarbonImmutable
    {
        return $this->crewcallAt()?->subMinutes(15);
    }

    /**
     * Get the moment check-in closes (exclusive): when tapping starts.
     * Null unless the session is scheduled and has a date and tapping start time.
     */
    public function checkInClosesAt(): ?CarbonImmutable
    {
        return $this->scheduledMomentAt($this->tapping_start);
    }

    /**
     * Determine whether check-in is open at the given instant (opens inclusive, closes exclusive).
     */
    public function isCheckInOpen(CarbonInterface $now): bool
    {
        $opensAt = $this->checkInOpensAt();
        $closesAt = $this->checkInClosesAt();

        if ($opensAt === null || $closesAt === null) {
            return false;
        }

        return $now->greaterThanOrEqualTo($opensAt) && $now->lessThan($closesAt);
    }

    /**
     * Determine whether a scheduled session's check-in window has already closed at the given instant.
     */
    public function isCheckInClosed(CarbonInterface $now): bool
    {
        $closesAt = $this->checkInClosesAt();

        return $closesAt !== null && $now->greaterThanOrEqualTo($closesAt);
    }

    /**
     * Get the attendance status earned by checking in at the given instant:
     * present before crewcall plus six minutes, late afterwards.
     *
     * @throws LogicException When the session has no crewcall time to compare against.
     */
    public function statusForCheckIn(CarbonInterface $checkedInAt): AttendanceStatus
    {
        $crewcallAt = $this->crewcallAt();

        if ($crewcallAt === null) {
            throw new LogicException('Session has no crewcall time to evaluate a check-in against.');
        }

        return $checkedInAt->lessThan($crewcallAt->addMinutes(6))
            ? AttendanceStatus::Hadir
            : AttendanceStatus::Terlambat;
    }

    /**
     * Get the effective attendance status of a user for this session at the given instant.
     * Uses the loaded attendances relation when available to avoid extra queries.
     */
    public function attendanceStatusFor(User $user, CarbonInterface $now): AttendanceStatus
    {
        $attendance = $this->relationLoaded('attendances')
            ? $this->attendances->firstWhere('user_id', $user->id)
            : $this->attendances()->where('user_id', $user->id)->first();

        if ($attendance !== null) {
            return $attendance->status;
        }

        return $this->isCheckInClosed($now) ? AttendanceStatus::TidakHadir : AttendanceStatus::Belum;
    }

    /**
     * Get the session date combined with the crewcall time in the application timezone.
     */
    private function crewcallAt(): ?CarbonImmutable
    {
        return $this->scheduledMomentAt($this->crewcall_time);
    }

    /**
     * Combine the session date with a time of day in the application timezone.
     * Null unless the session is scheduled and both date and time are present.
     */
    private function scheduledMomentAt(?string $time): ?CarbonImmutable
    {
        if ($this->status !== SessionStatus::Scheduled || $this->session_date === null || $time === null) {
            return null;
        }

        return CarbonImmutable::parse(
            $this->session_date->toDateString().' '.$time,
            config('app.timezone'),
        );
    }
}
