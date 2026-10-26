<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'username' => 'HR',
            'password' => 'hr',
            'role_id' => Role::where('name', 'human_resource')->value('id'),
        ]);

        $this->call([Gen7MemberSeeder::class, Gen7ProgramSeeder::class]);
    }
}
