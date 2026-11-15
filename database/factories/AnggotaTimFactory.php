<?php

namespace Database\Factories;

use App\Models\AnggotaTim;
use App\Models\PendaftaranLomba;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnggotaTim>
 */
class AnggotaTimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pendaftaran_id' => PendaftaranLomba::factory(),
            'nama_anggota' => fake()->name(),
        ];
    }
}
