<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChallengePartnerLogo>
 */
class ChallengePartnerLogoFactory extends Factory
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
            'path' => 'challenge/1/partner-logos/'.Str::uuid().'.png',
            'filename' => fake()->word().'.png',
            'href' => null,
            'order' => 0,
            'invert_in_dark' => false,
        ];
    }
}
