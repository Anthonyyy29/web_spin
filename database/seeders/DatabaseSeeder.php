<?php

namespace Database\Seeders;

use App\Enums\CrewPosition;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'username' => 'HR',
            'password' => 'hr',
            'role' => 'hr',
        ]);
        $announcer = User::create([
            'username' => 'announcer',
            'password' => 'announcer',
            'role' => 'announcer',
        ]);
        $so = User::create([
            'username' => 'so',
            'password' => 'so',
            'role' => 'so',
        ]);
        $announcerTwo = User::create([
            'username' => 'announcer2',
            'password' => 'announcer2',
            'role' => 'announcer',
        ]);
        User::create([
            'username' => 'announcer3',
            'password' => 'announcer3',
            'role' => 'announcer',
        ]);

        $this->seedExampleProgram($announcer, $announcerTwo, $so);
    }

    /**
     * Create the example program "K-Time" with its crew and broadcast sessions.
     */
    private function seedExampleProgram(User $announcer, User $announcerTwo, User $so): void
    {
        DB::transaction(function () use ($announcer, $announcerTwo, $so): void {
            $periodStart = today()->startOfWeek(Carbon::MONDAY);

            $program = Program::create([
                'name' => 'K-Time',
                'day_of_week' => 1,
                'crewcall_time' => '07:30:00',
                'tapping_start' => '08:00:00',
                'tapping_end' => '09:30:00',
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodStart->copy()->addWeeks(15)->toDateString(),
            ]);

            $program->crew()->sync([
                $announcer->id => ['position' => CrewPosition::Announcer->value],
                $announcerTwo->id => ['position' => CrewPosition::Announcer->value],
                $so->id => ['position' => CrewPosition::So->value],
            ]);

            $program->syncSessions(now());
        });
    }
}
