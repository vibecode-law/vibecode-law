<?php

namespace Database\Factories\Showcase;

use App\Enums\ShowcaseStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Showcase\Showcase>
 */
class ShowcaseFactory extends Factory
{
    use HasStockImages;

    protected $model = Showcase::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Showcase $showcase) {
            $practiceArea = PracticeArea::first() ?? PracticeArea::factory()->create();
            $showcase->practiceAreas()->attach($practiceArea);
        });
    }

    /**
     * Create the showcase without attaching practice areas.
     * This adds an afterCreating callback that detaches all practice areas,
     * running after the default callback that attaches one.
     */
    public function withoutPracticeAreas(): static
    {
        return $this->afterCreating(function (Showcase $showcase) {
            $showcase->practiceAreas()->detach();
        });
    }

    public function definition(): array
    {
        $title = fake()->words(nb: 3, asText: true);
        $slugBase = preg_replace(pattern: '/[^a-zA-Z\s]/', replacement: '', subject: $title);
        $slug = Str::slug($slugBase).'-'.fake()->lexify('???');

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => $slug,
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'key_features' => fake()->paragraphs(2, true),
            'help_needed' => fake()->paragraph(),
            'url' => fake()->url(),
            'video_url' => fake()->optional()->url(),
            'source_status' => SourceStatus::OpenSource,
            'source_url' => fake()->url(),
            'thumbnail_extension' => null,
            'thumbnail_crop' => null,
            'status' => ShowcaseStatus::Draft,
            'view_count' => fake()->numberBetween(0, 1000),
            'is_featured' => false,
        ];
    }

    public function withStockThumbnail(): static
    {
        return $this->afterCreating(function (Showcase $showcase): void {
            $imagePath = $this->getRandomStockImagePath();
            $extension = Str::afterLast($imagePath, '.');

            $path = "showcase/{$showcase->id}/thumbnail.{$extension}";

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $showcase->update([
                'thumbnail_extension' => $extension,
            ]);
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseStatus::Draft,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseStatus::Pending,
            'submitted_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseStatus::Approved,
            'submitted_date' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'approved_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'approved_by' => User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseStatus::Rejected,
            'submitted_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function featured(): static
    {
        return $this->approved()->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
