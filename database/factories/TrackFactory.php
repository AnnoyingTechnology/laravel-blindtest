<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file' => $this->faker->unique()->filePath(),
            'name' => $this->faker->words(3, true),
            'artist' => $this->faker->name(),
            'remix' => $this->faker->optional()->sentence(2),
            'year' => $this->faker->optional()->year(),
            'genre' => $this->faker->words(1, true),
            'is_current' => 0,
            'is_name_found' => 0,
            'is_artist_found' => 0,
            'is_remix_found' => 0,
            'scoring_factor' => 1
        ];
    }
}
