<?php

namespace Database\Factories\Challenge;

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\User;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge\Challenge>
 */
class ChallengeFactory extends Factory
{
    use HasStockImages;

    protected $model = Challenge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(nb: 3, asText: true);
        $slugBase = preg_replace(pattern: '/[^a-zA-Z\s]/', replacement: '', subject: $title);
        $slug = Str::slug($slugBase).'-'.fake()->lexify('???');

        return [
            'title' => $title,
            'slug' => $slug,
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraphs(nb: 3, asText: true),
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => false,
            'is_featured' => false,
            'organisation_id' => null,
            'user_id' => null,
            'thumbnail_extension' => null,
            'thumbnail_crops' => null,
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(fn () => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function forOrganisation(?Organisation $organisation = null): static
    {
        return $this->state(fn () => [
            'organisation_id' => $organisation?->id ?? Organisation::factory(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'is_active' => true,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }

    public function withDates(): static
    {
        return $this->state(fn () => [
            'starts_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'ends_at' => fake()->dateTimeBetween('+1 month', '+3 months'),
        ]);
    }

    public function ongoing(): static
    {
        return $this->active()->state(fn () => [
            'starts_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'ends_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
            'starts_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'ends_at' => fake()->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }

    public function withStockThumbnail(): static
    {
        return $this->afterCreating(function (Challenge $challenge): void {
            $imagePath = $this->getRandomStockImagePath();
            $extension = Str::afterLast($imagePath, '.');

            $path = "challenge/{$challenge->id}/thumbnail.{$extension}";

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $challenge->update([
                'thumbnail_extension' => $extension,
            ]);
        });
    }
}
