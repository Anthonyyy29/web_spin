<?php

namespace Database\Factories;

use App\Models\Crew;
use App\Models\RfidCrew;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfidCrew>
 */
class RfidCrewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfid_uid' => fake()->unique()->regexify('[0-9A-F]{8}'),
            'crew_id' => Crew::factory(),
        ];
    }
}
