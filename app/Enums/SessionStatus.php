<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Scheduled = 'scheduled';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Postponed => 'Ditunda',
            self::Cancelled => 'Batal',
        };
    }
}
