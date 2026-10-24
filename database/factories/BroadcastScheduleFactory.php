<?php

namespace Database\Factories;

use App\Models\BroadcastSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BroadcastSchedule>
 */
class BroadcastScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->announcer(),
            'program_title' => fake()->words(3, true),
            'broadcast_date' => fake()->date(),
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'notes' => null,
        ];
    }
}
