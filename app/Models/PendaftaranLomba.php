<?php

namespace App\Models;

use Database\Factories\PendaftaranLombaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PendaftaranLomba extends Model
{
    /** @use HasFactory<PendaftaranLombaFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    protected $table = 'pendaftaran_lomba';

    /**
     * @var list<string>
     */
    protected $fillable = ['jenis_lomba', 'nama_tim', 'nama_lengkap', 'instagram', 'email', 'no_wa', 'bukti_pembayaran', 'kartu_identitas'];

    /**
     * @return HasMany<AnggotaTim, $this>
     */
    public function anggota_tim(): HasMany
    {
        return $this->hasMany(AnggotaTim::class, 'pendaftaran_id');
    }
}
