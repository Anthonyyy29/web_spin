<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['ip_address', 'content', 'action'];

    /**
     * Catat perubahan data: $action adalah CREATE, UPDATE, atau DELETE.
     */
    public static function record(string $content, string $action): void
    {
        static::create([
            'ip_address' => (string) request()->ip(),
            'content' => $content,
            'action' => $action,
        ]);
    }
}
