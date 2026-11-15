<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Crew;
use Illuminate\Database\Seeder;

class ProgramCrewSeeder extends Seeder
{
    /**
     * Crew (announcer dan sound operator) tiap program Gen 7, dari jadwal absensi
     * semester ganjil. Jalankan CrewSeeder dan ProgramPodcastSeeder dulu.
     */
    public function run(): void
    {
        $crewPerProgram = [
            'K-TIME' => ['Priscilla Christiana Nahadin', 'Rael Nathania Wibowo', 'Saskia Oktaviani', 'Dhini Aulya', 'Syavirni Dwi Amalia'],
            'CAKHI' => ['Yariza Nafisha Hamzah', 'Noval Rasyah Kurnia Putra', 'Ahmad Faris Firta Budiman', 'Yohanes Dimas Restu Putra'],
            'Attenthings' => ['Nail Khairi Nur Hadi', 'Shezza Nadyra Hergianto', 'Bimo Septiansyah', 'Zahra Dinda Rahma Dani'],
            'Bisik Bernada' => ['Kimberly Valencia', 'Belva Vanya Aulia', 'Audrey Geovanni Rionald Sinaga', 'Raka Desnoel Sr'],
            'ERROR (ERA HORROR)' => ['Kahlil Al Fathir', 'Nisrina Ihda Jamaliah', 'Shafa Dhiya Raihanah', 'Salwa Syafira', 'Meirina Kartika Rahmah'],
            'Movie Hunter' => ['Ashfaa Atsqya Fernando', 'Lalia Alina', 'Salwa Syafira', 'Fatyanheiza Putri Cahyadi'],
            'The Untold Stories' => ['Ivanda Leona Putri Vabita', 'Shiren Rizkia Fahira', 'Sheriel Zelda Fitriani Cholid', 'Syavirni Dwi Amalia', 'Audrey Geovanni Rionald Sinaga'],
        ];

        $idProgram = Content::pluck('id', 'title');
        $idCrew = Crew::pluck('id', 'name');

        foreach ($crewPerProgram as $judul => $namaCrew) {
            $program = Content::findOrFail($idProgram[$judul] ?? throw new \RuntimeException("Program '{$judul}' belum ada. Jalankan ProgramPodcastSeeder dulu."));

            $program->crews()->sync(array_map(
                fn (string $nama): int => $idCrew[$nama] ?? throw new \RuntimeException("Crew '{$nama}' belum ada. Jalankan CrewSeeder dulu."),
                $namaCrew,
            ));
        }
    }
}
