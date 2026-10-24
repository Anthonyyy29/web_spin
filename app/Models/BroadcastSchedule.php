<?php

namespace App\Models;

use Database\Factories\BroadcastScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'program_title', 'broadcast_date', 'start_time', 'end_time', 'notes'])]
class BroadcastSchedule extends Model
{
    /** @use HasFactory<BroadcastScheduleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'broadcast_date' => 'date',
        ];
    }

    public function announcer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
