<?php

namespace Database\Factories;

use App\Models\AbsensiLog;
use App\Models\RfidCrew;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbsensiLog>
 */
class AbsensiLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfid_uid' => RfidCrew::factory(),
            'tanggal' => today()->toDateString(),
            'jam_masuk' => '08:00:00',
            'status' => 'Hadir',
        ];
    }
}
