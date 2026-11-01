<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'broadcast_session_id' => BroadcastSession::factory(),
            'user_id' => User::factory(),
            'checked_in_at' => '2026-10-26 07:20:00',
            'status' => AttendanceStatus::Hadir,
            'note' => null,
        ];
    }
}
