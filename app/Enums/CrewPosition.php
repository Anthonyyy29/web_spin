<?php

namespace App\Enums;

enum CrewPosition: string
{
    case Announcer = 'announcer';
    case So = 'so';

    public function label(): string
    {
        return match ($this) {
            self::Announcer => 'Announcer',
            self::So => 'SO',
        };
    }
}
