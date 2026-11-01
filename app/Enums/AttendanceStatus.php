<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Hadir = 'hadir';
    case Terlambat = 'terlambat';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case TidakHadir = 'tidak_hadir';
    case Belum = 'belum';

    /**
     * Get the statuses HR may set by hand; Belum only describes a check-in still to come.
     *
     * @return list<self>
     */
    public static function assignable(): array
    {
        return [self::Hadir, self::Terlambat, self::Izin, self::Sakit, self::TidakHadir];
    }

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Terlambat => 'Terlambat',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::TidakHadir => 'Tidak hadir',
            self::Belum => 'Belum',
        };
    }
}
