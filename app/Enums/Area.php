<?php

namespace App\Enums;

enum Area: string
{
    case Hr = 'hr';
    case Announcer = 'announcer';
    case So = 'so';

    /**
     * Role names allowed into this area.
     *
     * @return list<string>
     */
    public function roleNames(): array
    {
        return match ($this) {
            self::Hr => ['kadiv_human_resource', 'human_resource'],
            self::Announcer => ['kadiv_announcer', 'announcer'],
            self::So => ['kadiv_sound_operator', 'sound_operator'],
        };
    }

    /**
     * The Blade layout used by pages in this area.
     */
    public function layout(): string
    {
        return 'auth.'.$this->value.'.layout';
    }

    /**
     * The route name a user of this area lands on after login.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Hr => 'hr.dashboard',
            self::Announcer => 'announcer.dashboard',
            self::So => 'so.sesi.index',
        };
    }

    /**
     * Get the area a role belongs to, if any.
     */
    public static function forRoleName(string $name): ?self
    {
        foreach (self::cases() as $area) {
            if (in_array($name, $area->roleNames(), true)) {
                return $area;
            }
        }

        return null;
    }
}
