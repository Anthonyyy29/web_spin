<?php

namespace App\Models;

use Database\Factories\RfidCrewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidCrew extends Model
{
    /** @use HasFactory<RfidCrewFactory> */
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'rfid_uid';

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['rfid_uid', 'crew_id'];

    /**
     * @return BelongsTo<Crew, $this>
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }
}
