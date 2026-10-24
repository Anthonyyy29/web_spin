<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'announcer',
        ];
    }

    /**
     * Indicate that the user has the HR role.
     */
    public function hr(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'hr',
        ]);
    }

    /**
     * Indicate that the user has the announcer role.
     */
    public function announcer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'announcer',
        ]);
    }
}
