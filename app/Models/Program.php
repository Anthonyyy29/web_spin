<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'day_of_week', 'crewcall_time', 'tapping_start', 'tapping_end', 'period_start', 'period_end'])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'program_crew')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(BroadcastSession::class);
    }

    /**
     * Synchronize this program's broadcast sessions with its schedule and crew.
     *
     * Runs in a single transaction. Rules:
     * - Matching dates are every date in [period_start, period_end] whose ISO weekday equals day_of_week.
     * - A program without sessions gets one for every matching date; otherwise only matching dates on or
     *   after today (the start of $now) that have no session with that regular_date yet (moved sessions are
     *   never recreated).
     * - Non-customized sessions with regular_date on or after today are reset to the program's times and
     *   crew; if their regular_date is no longer a matching date they are deleted unless they have attendance.
     * - Customized sessions, sessions before today, and today's session once its check-in has opened at $now
     *   are left untouched.
     * - Attendances are never modified.
     */
    public function syncSessions(CarbonInterface $now): void
    {
        DB::transaction(function () use ($now): void {
            $todayDate = $now->copy()->startOfDay()->toDateString();
            $matchingDates = $this->matchingDates();
            $crewPayload = $this->crew()->get()
                ->mapWithKeys(fn (User $user): array => [$user->id => ['position' => $user->pivot->position]])
                ->all();

            $existingSessions = $this->sessions()->get();
            $hadSessions = $existingSessions->isNotEmpty();
            $existingRegularDates = $existingSessions
                ->map(fn (BroadcastSession $session): string => $session->regular_date->toDateString())
                ->all();

            foreach ($matchingDates as $date) {
                if (in_array($date, $existingRegularDates, true) || ($hadSessions && $date < $todayDate)) {
                    continue;
                }

                $session = $this->sessions()->create([
                    'regular_date' => $date,
                    'session_date' => $date,
                    'crewcall_time' => $this->crewcall_time,
                    'tapping_start' => $this->tapping_start,
                    'tapping_end' => $this->tapping_end,
                    'status' => SessionStatus::Scheduled,
                    'is_customized' => false,
                ]);
                $session->crew()->sync($crewPayload);
            }

            $existingSessions
                ->reject(fn (BroadcastSession $session): bool => $session->is_customized
                    || $session->regular_date->toDateString() < $todayDate
                    || $this->checkInHasOpened($session, $now))
                ->each(function (BroadcastSession $session) use ($matchingDates, $crewPayload): void {
                    if (! in_array($session->regular_date->toDateString(), $matchingDates, true)) {
                        if (! $session->attendances()->exists()) {
                            $session->delete();
                        }

                        return;
                    }

                    $session->update([
                        'session_date' => $session->regular_date->toDateString(),
                        'crewcall_time' => $this->crewcall_time,
                        'tapping_start' => $this->tapping_start,
                        'tapping_end' => $this->tapping_end,
                    ]);
                    $session->crew()->sync($crewPayload);
                });
        });
    }

    /**
     * Determine whether the session's check-in window has already opened at the given instant.
     */
    private function checkInHasOpened(BroadcastSession $session, CarbonInterface $now): bool
    {
        $opensAt = $session->checkInOpensAt();

        return $opensAt !== null && $opensAt->lessThanOrEqualTo($now);
    }

    /**
     * Dates (Y-m-d) within the program period that fall on the program's weekday.
     *
     * @return list<string>
     */
    protected function matchingDates(): array
    {
        $dates = [];
        $cursor = CarbonImmutable::parse($this->period_start->toDateString());
        $end = CarbonImmutable::parse($this->period_end->toDateString());

        while ($cursor->lessThanOrEqualTo($end)) {
            if ($cursor->isoWeekday() === (int) $this->day_of_week) {
                $dates[] = $cursor->toDateString();
            }
            $cursor = $cursor->addDay();
        }

        return $dates;
    }
}
