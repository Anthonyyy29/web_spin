<?php

namespace Database\Factories;

use App\Models\Role;
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
            'role_id' => fn (): ?int => Role::where('name', 'announcer')->value('id'),
        ];
    }

    /**
     * Indicate that the user has the role with the given name.
     */
    public function role(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => fn (): ?int => Role::where('name', $name)->value('id'),
        ]);
    }

    /**
     * Indicate that the user has the HR role.
     */
    public function hr(): static
    {
        return $this->role('human_resource');
    }

    /**
     * Indicate that the user has the announcer role.
     */
    public function announcer(): static
    {
        return $this->role('announcer');
    }

    /**
     * Indicate that the user has the SO role.
     */
    public function so(): static
    {
        return $this->role('sound_operator');
    }
}
