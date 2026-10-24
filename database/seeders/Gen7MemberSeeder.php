<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class Gen7MemberSeeder extends Seeder
{
    /**
     * Temporary password for every seeded member account.
     */
    public const DEFAULT_PASSWORD = '1234';

    /**
     * Gen 7 members (from "Data Diri Gen 7 - Data Anggota") per role name. The username is the full name.
     *
     * @var array<string, list<string>>
     */
    private const MEMBERS = [
        'announcer' => [
            'Ivanda Leona Putri Vabita',
            'Ashfaa Atsqya Fernando',
            'Belva Vanya Aulia',
            'Kahlil Al Fathir',
            'Kimberly Valencia',
            'Lalia Alina',
            'Nail Khairi Nur Hadi',
            'Nisrina Ihda Jamaliah',
            'Noval Rasyah Kurnia Putra',
            'Priscilla Christiana Nahadin',
            'Rael Nathania Wibowo',
            'Saskia Oktaviani',
            'Shafa Dhiya Raihanah',
            'Sheriel Zelda Fitriani Cholid',
            'Shezza Nadyra Hergianto',
            'Shiren Rizkia fahira',
            'Yariza Nafisha Hamzah',
        ],
        'sound_operator' => [
            'Salwa Syafira',
            'Ahmad Faris Firta Budiman',
            'Audrey Geovanni Rionald Sinaga',
            'Bimo Septiansyah',
            'Dhini Aulya',
            'Fatyanheiza Putri Cahyadi',
            'Meirina Kartika Rahmah',
            'Raka Desnoel Sr',
            'Syavirni Dwi Amalia',
            'Yohanes Dimas Restu Putra',
            'Zahra Dinda Rahma Dani',
        ],
        'human_resource' => [
            'Satriani',
            'Anggraini Dwina Putri',
            'Azza Nabila Assaida',
            'Dewi Novita Sari',
            'Feby Kumala',
            'Keisha Ann Aida Rahman',
            'Yoga Nanda Saputra',
        ],
    ];

    /**
     * Create an account for every announcer, sound operator and human resources member.
     */
    public function run(): void
    {
        foreach (self::MEMBERS as $roleName => $names) {
            $roleId = Role::where('name', $roleName)->value('id');

            foreach ($names as $name) {
                User::create([
                    'username' => $name,
                    'password' => self::DEFAULT_PASSWORD,
                    'role_id' => $roleId,
                ]);
            }
        }
    }
}
