<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminHistory extends Model
{
    protected $table = 'admin_history';

    /**
     * @var list<string>
     */
    protected $fillable = ['ip_address', 'last_login', 'device'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
        ];
    }

    /**
     * Catat login: perbarui baris dengan IP dan perangkat yang sama, atau buat baru.
     */
    public static function recordLogin(Request $request): void
    {
        $userAgent = (string) $request->userAgent();

        $device = match (true) {
            str_contains($userAgent, 'iPhone') => 'Iphone',
            str_contains($userAgent, 'Android'), str_contains($userAgent, 'Mobile') => 'Android',
            default => 'Desktop',
        };

        $history = static::firstOrNew([
            'ip_address' => (string) $request->ip(),
            'device' => $device,
        ]);
        $history->last_login = now();
        $history->save();
    }
}
