<?php

namespace Database\Factories;

use App\Models\PendaftaranLomba;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PendaftaranLomba>
 */
class PendaftaranLombaFactory extends Factory
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
            'nama_lengkap' => fake()->name(),
            'instagram' => '',
            'email' => fake()->safeEmail(),
            'no_wa' => '08123456789',
            'bukti_pembayaran' => 'bukti.jpg',
            'kartu_identitas' => 'kartu.jpg',
        ];
    }
}
