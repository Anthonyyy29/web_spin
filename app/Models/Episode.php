<?php

namespace App\Models;

use App\Models\Concerns\CountsByWeekday;
use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use CountsByWeekday;

    /** @use HasFactory<EpisodeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['id_content', 'title', 'description', 'image', 'audio'];

    /**
     * Ambil ID episode dari tautan Spotify (bagian setelah /episode/).
     */
    public static function extractSpotifyEpisodeId(string $url): ?string
    {
        $parts = explode('/episode/', $url);

        return isset($parts[1]) ? explode('?', $parts[1])[0] : null;
    }
}
