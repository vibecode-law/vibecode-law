<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(nbWords: 2),
            'value' => fake()->sentence(),
        ];
    }

    public function announcement(?string $value = null): static
    {
        return $this->state([
            'key' => SiteSetting::ANNOUNCEMENT,
            'value' => $value ?? fake()->sentence(),
        ]);
    }
}
