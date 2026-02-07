<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PressCoverage>
 */
class PressCoverageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'publication_name' => fake()->company(),
            'publication_date' => fake()->date(),
            'url' => fake()->url(),
            'excerpt' => fake()->paragraph(),
            'thumbnail_extension' => null,
            'thumbnail_crop' => null,
            'is_published' => false,
            'display_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
