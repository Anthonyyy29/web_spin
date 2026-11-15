<?php

namespace App\Models;

use App\Models\Concerns\CountsByWeekday;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use CountsByWeekday;

    /** @use HasFactory<NewsFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    public const array CATEGORIES = [
        'Sneek Peak',
        'Campus Buzz',
        'Spin The Wall',
        'Spin The Talent',
        'Daily Content',
        'Spin The Podcast',
        'SpinTerview',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = ['title', 'category', 'description', 'image', 'video'];

    /**
     * Pecah deskripsi per baris; bila $maxWords > 0, potong setelah sejumlah kata itu.
     *
     * @return list<string>
     */
    public function descriptionRows(int $maxWords = 0): array
    {
        $rows = array_map(
            fn (string $row): string => str_replace('\\n', '', trim($row)),
            explode("\n", (string) $this->description),
        );

        if ($maxWords <= 0) {
            return $rows;
        }

        $limited = [];
        $total = 0;

        foreach ($rows as $row) {
            $count = str_word_count(strip_tags($row));

            if ($total + $count <= $maxWords) {
                $limited[] = $row;
                $total += $count;

                continue;
            }

            if ($maxWords - $total > 0) {
                $words = explode(' ', strip_tags($row));
                $limited[] = implode(' ', array_slice($words, 0, $maxWords - $total)).' ...';
            }

            break;
        }

        return $limited;
    }

    /**
     * Ambil ID reel dari tautan Instagram.
     */
    public static function extractInstagramReelId(?string $url): ?string
    {
        return preg_match('/instagram\.com\/reel\/([a-zA-Z0-9_\-]+)/', (string) $url, $matches) ? $matches[1] : null;
    }
}
