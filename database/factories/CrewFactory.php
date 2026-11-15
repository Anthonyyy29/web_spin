<?php

namespace Database\Factories;

use App\Models\Crew;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Crew>
 */
class CrewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => fake()->randomElement(array_keys(Crew::ROLES)),
            'image' => 'crew.png',
            'instagram_link' => 'https://instagram.com/crew',
            'linkedin_link' => 'https://linkedin.com/in/crew',
        ];
    }
}
