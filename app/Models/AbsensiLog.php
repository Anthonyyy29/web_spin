<?php

namespace App\Models;

use Database\Factories\AbsensiLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiLog extends Model
{
    /** @use HasFactory<AbsensiLogFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = ['rfid_uid', 'session_id', 'tanggal', 'jam_masuk', 'status'];

    /**
     * Absensi beserta nama dan divisi crew pemilik kartu.
     *
     * @param  Builder<self>  $query
     */
    public function scopeWithCrew(Builder $query): void
    {
        $query
            ->leftJoin('rfid_crews', 'rfid_crews.rfid_uid', '=', 'absensi_logs.rfid_uid')
            ->leftJoin('crews', 'crews.id', '=', 'rfid_crews.crew_id')
            ->select('absensi_logs.*', 'crews.name as nama', 'crews.role as divisi');
    }

    /**
     * @return BelongsTo<BroadcastSession, $this>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BroadcastSession::class, 'session_id');
    }
}
