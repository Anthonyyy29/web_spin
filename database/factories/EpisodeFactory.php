<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Episode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Episode>
 */
class EpisodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_content' => Content::factory(),
            'title' => fake()->unique()->sentence(3),
            'description' => fake()->sentence(),
            'audio' => 'https://open.spotify.com/embed/episode/'.fake()->regexify('[A-Za-z0-9]{12}'),
        ];
    }
}
