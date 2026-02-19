<?php

namespace Database\Factories\Course;

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course\Lesson>
 */
class LessonFactory extends Factory
{
    use HasStockImages;

    protected $model = Lesson::class;

    public function definition(): array
    {
        $title = fake()->words(nb: 4, asText: true);

        return [
            'course_id' => Course::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->lexify('???'),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraphs(nb: 2, asText: true),
            'copy' => fake()->optional()->paragraphs(nb: 4, asText: true),
            'asset_id' => null,
            'playback_id' => null,
            'host' => null,
            'learning_objectives' => fake()->optional()->paragraphs(nb: 2, asText: true),
            'duration_seconds' => fake()->optional()->numberBetween(60, 3600),
            'gated' => true,
            'order' => fake()->numberBetween(0, 20),
            'allow_preview' => fake()->boolean(),
            'publish_date' => fake()->optional()->date(),
            'thumbnail_filename' => null,
            'thumbnail_crops' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'publish_date' => fake()->dateTimeBetween(startDate: '-1 year', endDate: '-1 day'),
            'allow_preview' => false,
        ]);
    }

    public function previewable(): static
    {
        return $this->state(fn (): array => [
            'publish_date' => null,
            'allow_preview' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'publish_date' => null,
            'allow_preview' => false,
        ]);
    }

    public function ungated(): static
    {
        return $this->state(fn (): array => [
            'gated' => false,
        ]);
    }

    public function withStockThumbnail(): static
    {
        return $this->afterCreating(function (Lesson $lesson): void {
            $imagePath = $this->getRandomStockImagePath();
            $extension = Str::afterLast($imagePath, '.');
            $filename = Str::random(40).'.'.$extension;

            $path = "lesson/{$lesson->id}/{$filename}";

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $lesson->update([
                'thumbnail_filename' => $filename,
            ]);
        });
    }
}
