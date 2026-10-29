<?php

namespace Database\Seeders;

use App\Enums\CrewPosition;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class Gen7ProgramSeeder extends Seeder
{
    /**
     * First day of the odd semester broadcast period.
     */
    public const PERIOD_START = '2026-09-01';

    /**
     * Last day of the odd semester broadcast period.
     */
    public const PERIOD_END = '2027-01-31';

    /**
     * Gen 7 odd semester programs from the attendance schedule. Crew are the usernames seeded by Gen7MemberSeeder.
     *
     * @var list<array{name: string, day_of_week: int, crewcall_time: string, tapping_start: string, tapping_end: string, announcer: list<string>, sound_operator: list<string>}>
     */
    private const PROGRAMS = [
        [
            'name' => 'K-TIME',
            'day_of_week' => 1,
            'crewcall_time' => '07:30',
            'tapping_start' => '08:00',
            'tapping_end' => '09:30',
            'announcer' => ['Priscilla Christiana Nahadin', 'Rael Nathania Wibowo', 'Saskia Oktaviani'],
            'sound_operator' => ['Dhini Aulya', 'Syavirni Dwi Amalia (Sound Operator)'],
        ],
        [
            'name' => 'CAKHI',
            'day_of_week' => 2,
            'crewcall_time' => '12:30',
            'tapping_start' => '13:00',
            'tapping_end' => '14:30',
            'announcer' => ['Yariza Nafisha Hamzah', 'Noval Rasyah Kurnia Putra (Announcer)'],
            'sound_operator' => ['Ahmad Faris Firta Budiman', 'Yohanes Dimas Restu Putra'],
        ],
        [
            'name' => 'ATTENTHINGS',
            'day_of_week' => 2,
            'crewcall_time' => '16:00',
            'tapping_start' => '16:30',
            'tapping_end' => '18:00',
            'announcer' => ['Nail Khairi Nur Hadi', 'Shezza Nadyra Hergianto'],
            'sound_operator' => ['Bimo Septiansyah', 'Zahra Dinda Rahma Dani'],
        ],
        [
            'name' => 'BISIK BERNADA',
            'day_of_week' => 3,
            'crewcall_time' => '10:00',
            'tapping_start' => '10:30',
            'tapping_end' => '12:00',
            'announcer' => ['Kimberly Valencia', 'Belva Vanya Aulia'],
            'sound_operator' => ['Audrey Geovanni Rionald Sinaga', 'Raka Desnoel Sr'],
        ],
        [
            'name' => 'ERROR',
            'day_of_week' => 4,
            'crewcall_time' => '07:30',
            'tapping_start' => '08:00',
            'tapping_end' => '09:30',
            'announcer' => ['Kahlil Al Fathir', 'Nisrina Ihda Jamaliah', 'Shafa Dhiya Raihanah (Announcer)'],
            'sound_operator' => ['Salwa Syafira', 'Meirina Kartika Rahmah'],
        ],
        [
            'name' => 'MOVIE HUNTER',
            'day_of_week' => 5,
            'crewcall_time' => '10:00',
            'tapping_start' => '10:30',
            'tapping_end' => '12:00',
            'announcer' => ['Ashfaa Atsqya Fernando', 'Lalia Alina'],
            'sound_operator' => ['Salwa Syafira', 'Fatyanheiza Putri Cahyadi'],
        ],
        [
            'name' => 'THE UNTOLD STORY',
            'day_of_week' => 5,
            'crewcall_time' => '12:30',
            'tapping_start' => '13:00',
            'tapping_end' => '14:30',
            'announcer' => ['Ivanda Leona Putri Vabita', 'Shiren Rizkia fahira', 'Sheriel Zelda Fitriani Cholid'],
            'sound_operator' => ['Syavirni Dwi Amalia (Sound Operator)', 'Audrey Geovanni Rionald Sinaga'],
        ],
    ];

    /**
     * Create every Gen 7 program with its announcer and SO crew.
     */
    public function run(): void
    {
        $userIds = User::pluck('id', 'username');

        foreach (self::PROGRAMS as $data) {
            $program = Program::create([
                'name' => $data['name'],
                'day_of_week' => $data['day_of_week'],
                'crewcall_time' => $data['crewcall_time'],
                'tapping_start' => $data['tapping_start'],
                'tapping_end' => $data['tapping_end'],
                'period_start' => self::PERIOD_START,
                'period_end' => self::PERIOD_END,
            ]);

            $crew = [];

            foreach (CrewPosition::cases() as $position) {
                foreach ($data[$position->value] as $username) {
                    $crew[$userIds[$username]] = ['position' => $position->value];
                }
            }

            $program->crew()->sync($crew);
        }
    }
}
