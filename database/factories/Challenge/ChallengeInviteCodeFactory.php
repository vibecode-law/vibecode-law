<?php

namespace Database\Factories\Challenge;

use App\Enums\InviteCodeScope;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge\ChallengeInviteCode>
 */
class ChallengeInviteCodeFactory extends Factory
{
    protected $model = ChallengeInviteCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'code' => Str::random(16),
            'label' => fake()->words(2, asText: true),
            'scope' => InviteCodeScope::ViewAndSubmit,
            'is_active' => true,
        ];
    }

    public function forChallenge(?Challenge $challenge = null): static
    {
        return $this->state(fn () => [
            'challenge_id' => $challenge?->id ?? Challenge::factory(),
        ]);
    }

    public function viewOnly(): static
    {
        return $this->state(fn () => [
            'scope' => InviteCodeScope::View,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
