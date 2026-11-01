<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'day_of_week' => 1,
            'crewcall_time' => '07:30:00',
            'tapping_start' => '08:00:00',
            'tapping_end' => '09:30:00',
            'period_start' => '2026-10-05',
            'period_end' => '2026-10-26',
        ];
    }
}
