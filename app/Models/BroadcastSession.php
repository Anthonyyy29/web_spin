<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Database\Factories\BroadcastSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
