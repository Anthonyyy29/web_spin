<?php

namespace Database\Factories;

use App\Models\PengumpulanKarya;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengumpulanKarya>
 */
class PengumpulanKaryaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis_lomba' => 'poster',
            'nama' => fake()->name(),
            'judul_karya' => fake()->sentence(3),
            'link_gdrive' => 'https://drive.google.com/x',
            'link_instagram' => 'https://instagram.com/p/x',
        ];
    }
}
