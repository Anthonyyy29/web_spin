<?php

namespace App\Models;

use Database\Factories\AnggotaTimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaTim extends Model
{
    /** @use HasFactory<AnggotaTimFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'pendaftaran_anggota_tim';

    /**
     * @var list<string>
     */
    protected $fillable = ['pendaftaran_id', 'nama_anggota'];
}
