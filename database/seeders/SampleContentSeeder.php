<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Crew;
use App\Models\Episode;
use App\Models\News;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SampleContentSeeder extends Seeder
{
    /**
     * Isi contoh agar halaman publik dan dashboard tidak kosong saat dicoba.
     * Gambar memakai berkas yang sudah ada di public/assets/uploads.
     */
    public function run(): void
    {
        $gambar = array_map('basename', glob(public_path('assets/uploads/*.{png,jpg}'), GLOB_BRACE) ?: []);
        $ambil = fn (int $i): string => $gambar[$i % max(count($gambar), 1)] ?? 'logo.png';

        $segmen = collect(['Spin The Talk', 'Spin The Vibes'])->map(fn (string $judul, int $i): Content => Content::create([
            'title' => $judul,
            'description' => "Segmen {$judul} membahas cerita kampus.",
            'image' => $ambil($i),
        ]));

        foreach ($segmen as $i => $content) {
            Episode::create([
                'id_content' => $content->id,
                'title' => "Episode contoh {$content->title}",
                'description' => 'Deskripsi episode contoh.',
                'audio' => 'https://open.spotify.com/embed/episode/4rOoJ6Egrf8K2IrywzwOMk',
            ]);
        }

        foreach (['General Manager', 'Vice General Manager', 'Sekretaris', 'Bendahara', 'Human Resource'] as $i => $jabatan) {
            Crew::create([
                'name' => "Crew {$jabatan}",
                'role' => $jabatan,
                'image' => $ambil($i),
                'instagram_link' => 'https://instagram.com/spintheradiofm',
                'linkedin_link' => 'https://linkedin.com',
            ]);
        }

        foreach (array_slice(News::CATEGORIES, 0, 4) as $i => $kategori) {
            News::create([
                'title' => "Berita contoh {$kategori}",
                'category' => $kategori,
                'description' => "Paragraf pertama berita.\n\nParagraf kedua berita contoh.",
                'image' => $ambil($i),
            ]);
        }

        Sponsor::create(['image' => $ambil(0)]);
    }
}
