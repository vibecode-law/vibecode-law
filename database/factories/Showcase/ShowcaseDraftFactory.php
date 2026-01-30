<?php

namespace Database\Factories\Showcase;

use App\Enums\ShowcaseDraftStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Showcase\ShowcaseDraft>
 */
class ShowcaseDraftFactory extends Factory
{
    use HasStockImages;

    protected $model = ShowcaseDraft::class;

    public function configure(): static
    {
        return $this->afterCreating(function (ShowcaseDraft $draft) {
            $practiceArea = PracticeArea::first() ?? PracticeArea::factory()->create();
            $draft->practiceAreas()->attach($practiceArea);
        });
    }

    /**
     * Create the draft without attaching practice areas.
     * This adds an afterCreating callback that detaches all practice areas,
     * running after the default callback that attaches one.
     */
    public function withoutPracticeAreas(): static
    {
        return $this->afterCreating(function (ShowcaseDraft $draft) {
            $draft->practiceAreas()->detach();
        });
    }

    public function definition(): array
    {
        return [
            'showcase_id' => Showcase::factory()->approved(),
            'title' => fake()->words(nb: 3, asText: true),
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
            'status' => ShowcaseDraftStatus::Draft,
        ];
    }

    public function withStockThumbnail(): static
    {
        return $this->afterCreating(function (ShowcaseDraft $draft): void {
            $imagePath = $this->getRandomStockImagePath();
            $extension = Str::afterLast($imagePath, '.');

            $path = "showcase-drafts/{$draft->id}/thumbnail.{$extension}";

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $draft->update([
                'thumbnail_extension' => $extension,
            ]);
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseDraftStatus::Draft,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseDraftStatus::Pending,
            'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShowcaseDraftStatus::Rejected,
            'submitted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
