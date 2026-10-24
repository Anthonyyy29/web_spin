<?php

namespace App\Enums;

enum CrewPosition: string
{
    case Announcer = 'announcer';
    case So = 'sound_operator';

    public function label(): string
    {
        return match ($this) {
            self::Announcer => 'Announcer',
            self::So => 'SO',
        };
    }

    /**
     * Role names whose holders may be assigned to this position.
     *
     * @return list<string>
     */
    public function roleNames(): array
    {
        return match ($this) {
            self::Announcer => ['kadiv_announcer', 'announcer'],
            self::So => ['kadiv_sound_operator', 'sound_operator'],
        };
    }

    /**
     * Get the position a role may be assigned to, if any.
     */
    public static function forRoleName(string $name): ?self
    {
        foreach (self::cases() as $position) {
            if (in_array($name, $position->roleNames(), true)) {
                return $position;
            }
        }

        return null;
    }
}
