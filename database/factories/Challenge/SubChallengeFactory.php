<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubChallenge>
 */
class SubChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'name' => fake()->unique()->words(asText: true),
            'tagline' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'order' => 0,
        ];
    }

    public function forChallenge(Challenge $challenge): static
    {
        return $this->state(fn (array $attributes): array => [
            'challenge_id' => $challenge->id,
        ]);
    }
}
