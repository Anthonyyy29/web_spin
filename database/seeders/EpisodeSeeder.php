<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Episode;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    /**
     * Nama program di data lama yang berbeda dari judul di tabel contents.
     * Selebihnya dicocokkan berdasarkan judul tanpa membedakan huruf besar/kecil.
     *
     * @var array<string, string>
     */
    private const array NAMA_PROGRAM = [
        'MOVHUN' => 'Movie Hunter',
        'ERROR' => 'ERROR (ERA HORROR)',
    ];

    /**
     * Isi episode dari repo lama (data/episodes.json). Jalankan ProgramPodcastSeeder
     * lebih dulu. ID lama dipertahankan agar aman dijalankan berulang.
     */
    public function run(): void
    {
        $idProgram = Content::pluck('id', 'title')->mapWithKeys(fn (int $id, string $judul): array => [mb_strtolower($judul) => $id]);
        $episode = json_decode(file_get_contents(__DIR__.'/data/episodes.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($episode as $data) {
            $judulProgram = self::NAMA_PROGRAM[$data['podcast']] ?? $data['podcast'];
            $idContent = $idProgram[mb_strtolower($judulProgram)]
                ?? throw new \RuntimeException("Program '{$judulProgram}' belum ada. Jalankan ProgramPodcastSeeder dulu.");

            Episode::updateOrCreate(['id' => (int) $data['id']], [
                'id_content' => $idContent,
                'title' => $data['title'],
                'description' => $data['description'],
                'audio' => $data['spotify_embed'],
            ]);
        }
    }
}
