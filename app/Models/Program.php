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

#[Fillable(['name', 'day_of_week', 'crewcall_time', 'tapping_start', 'tapping_end'])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    /**
     * Day of week names (ISO 8601, 1 = Monday).
     *
     * @var array<int, string>
     */
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
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
     * First broadcast day on or after the start of $from that has no active (non-cancelled) session yet.
     *
     * Starts at the first occurrence of the program's weekday on or after $from and moves forward a week
     * at a time until it reaches a date without a session of this program. Never returns null.
     */
    public function nextOpenBroadcastDate(CarbonInterface $from): CarbonImmutable
    {
        $start = CarbonImmutable::parse($from->toDateString());

        $takenDates = $this->sessions()
            ->whereNotNull('session_date')
            ->where('status', '!=', SessionStatus::Cancelled->value)
            ->whereDate('session_date', '>=', $start->toDateString())
            ->pluck('session_date')
            ->map(fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $candidate = $start->addDays(((int) $this->day_of_week - $start->isoWeekday() + 7) % 7);

        while (in_array($candidate->toDateString(), $takenDates, true)) {
            $candidate = $candidate->addWeek();
        }

        return $candidate;
    }
}
