<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\BroadcastSession;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BroadcastSession>
 */
class BroadcastSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'regular_date' => '2026-10-26',
            'session_date' => '2026-10-26',
            'crewcall_time' => '07:30:00',
            'tapping_start' => '08:00:00',
            'tapping_end' => '09:30:00',
            'status' => SessionStatus::Scheduled,
            'is_customized' => false,
        ];
    }

    /**
     * Indicate that the session is postponed without a new date or time.
     */
    public function postponed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionStatus::Postponed,
            'session_date' => null,
            'crewcall_time' => null,
            'tapping_start' => null,
            'tapping_end' => null,
        ]);
    }

    /**
     * Indicate that the session is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionStatus::Cancelled,
        ]);
    }
}
