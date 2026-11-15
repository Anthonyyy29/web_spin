<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Isi berita dari repo lama (data/news.json). ID lama dipertahankan agar
     * aman dijalankan berulang. Gambarnya sudah ada di public/assets/uploads.
     */
    public function run(): void
    {
        $berita = json_decode(file_get_contents(__DIR__.'/data/news.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($berita as $data) {
            News::updateOrCreate(['id' => (int) $data['id']], [
                'title' => $data['title'],
                'category' => $data['category'],
                'description' => $data['description'],
                'image' => basename($data['image']),
                'video' => $data['video'] ?: null,
            ]);
        }
    }
}
