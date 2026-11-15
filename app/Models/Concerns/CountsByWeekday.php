<?php

namespace App\Models\Concerns;

trait CountsByWeekday
{
    /**
     * Jumlah data yang dibuat minggu ini, per hari dari Senin sampai Minggu.
     *
     * @return list<int>
     */
    public static function countsThisWeek(): array
    {
        $counts = array_fill(0, 7, 0);

        static::query()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->pluck('created_at')
            ->each(function ($createdAt) use (&$counts): void {
                $counts[$createdAt->dayOfWeekIso - 1]++;
            });

        return $counts;
    }
}
