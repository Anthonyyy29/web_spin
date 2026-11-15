<?php

namespace App\Models;

use Database\Factories\PengumpulanKaryaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengumpulanKarya extends Model
{
    /** @use HasFactory<PengumpulanKaryaFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    protected $table = 'pengumpulan_karya';

    /**
     * @var list<string>
     */
    protected $fillable = ['jenis_lomba', 'nama', 'judul_karya', 'link_gdrive', 'link_instagram', 'surat_pernyataan'];
}
