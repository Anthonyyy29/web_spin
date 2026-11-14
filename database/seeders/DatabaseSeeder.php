<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            User::updateOrCreate(
                ['username' => $role->value],
                ['password' => $this->passwordFor($role), 'role' => $role],
            );
        }
    }

    /**
     * Ambil password peran dari config; hanya local dan testing yang boleh
     * memakai password bawaan.
     */
    private function passwordFor(UserRole $role): string
    {
        $password = config("seed.passwords.{$role->value}");

        if (filled($password)) {
            return $password;
        }

        if (app()->environment(['local', 'testing'])) {
            return 'password';
        }

        throw new RuntimeException(
            'Isi SEED_'.strtoupper($role->value).'_PASSWORD di .env sebelum menjalankan seeder.'
        );
    }
}
