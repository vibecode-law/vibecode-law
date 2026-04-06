<?php

namespace Database\Factories\Showcase;

use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use App\Models\Showcase\ShowcaseImage;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShowcaseDraftImage>
 */
class ShowcaseDraftImageFactory extends Factory
{
    use HasStockImages;

    protected $model = ShowcaseDraftImage::class;

    public function definition(): array
    {
        return [
            'showcase_draft_id' => ShowcaseDraft::factory(),
            'original_image_id' => null,
            'action' => ShowcaseDraftImage::ACTION_ADD,
            'path' => 'showcase-drafts/test-image.jpg',
            'filename' => fake()->word().'.jpg',
            'alt_text' => fake()->optional()->sentence(),
            'order' => 0,
            'crops' => [
                'landscape' => [
                    'x' => 0,
                    'y' => 0,
                    'width' => 800,
                    'height' => 450,
                ],
            ],
        ];
    }

    public function keep(ShowcaseImage $originalImage): static
    {
        return $this->state(fn (array $attributes) => [
            'original_image_id' => $originalImage->id,
            'action' => ShowcaseDraftImage::ACTION_KEEP,
            'path' => null,
            'filename' => null,
            'alt_text' => $originalImage->alt_text,
            'order' => $originalImage->order,
            'crops' => $originalImage->crops,
        ]);
    }

    public function remove(ShowcaseImage $originalImage): static
    {
        return $this->state(fn (array $attributes) => [
            'original_image_id' => $originalImage->id,
            'action' => ShowcaseDraftImage::ACTION_REMOVE,
            'path' => null,
            'filename' => null,
            'alt_text' => null,
            'order' => 0,
            'crops' => null,
        ]);
    }

    public function add(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_image_id' => null,
            'action' => ShowcaseDraftImage::ACTION_ADD,
        ]);
    }

    public function withStockImage(): static
    {
        return $this->afterCreating(function (ShowcaseDraftImage $image): void {
            if ($image->action !== ShowcaseDraftImage::ACTION_ADD) {
                return;
            }

            $imagePath = $this->getRandomStockImagePath();

            $path = 'showcase-drafts/'.$image->showcase_draft_id.'/images/'.Str::afterLast($imagePath, '/');

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $image->update([
                'path' => $path,
                'filename' => fake()->slug(2),
            ]);
        });
    }
}
