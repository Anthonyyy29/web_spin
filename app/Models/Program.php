<?php

namespace App\Models;

use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
