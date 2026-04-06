<?php

namespace Database\Factories\Showcase;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShowcaseImage>
 */
class ShowcaseImageFactory extends Factory
{
    use HasStockImages;

    protected $model = ShowcaseImage::class;

    public function definition(): array
    {
        return [
            'showcase_id' => Showcase::factory(),
            'path' => 'showcases/test-image.jpg',
            'filename' => fake()->word().'.jpg',
            'order' => 0,
            'alt_text' => fake()->optional()->sentence(),
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

    public function withStockImage(): static
    {
        return $this->afterCreating(function (ShowcaseImage $image): void {
            $imagePath = $this->getRandomStockImagePath();

            $path = 'showcase/'.$image->showcase_id.'/images/'.Str::afterLast($imagePath, '/');

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $image->update([
                'path' => $path,
                'filename' => fake()->slug(2),
            ]);
        });
    }
}
