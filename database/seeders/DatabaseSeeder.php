<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Buat dua akun bersama. Password disimpan ter-hash oleh model User.
     */
    public function run(): void
    {
        $akun = [
            ['username' => 'admin', 'password' => 'password', 'role' => 'admin'],
            ['username' => 'hr', 'password' => 'password', 'role' => 'hr'],
        ];

        foreach ($akun as $data) {
            User::updateOrCreate(['username' => $data['username']], $data);
        }

        $this->call(CrewSeeder::class);
    }
}
