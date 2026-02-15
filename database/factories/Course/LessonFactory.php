<?php

namespace Database\Factories\Course;

use App\Enums\VideoHost;
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
            'transcript' => fake()->optional()->paragraphs(nb: 5, asText: true),
            'caption_track_id' => fake()->optional()->uuid(),
            'asset_id' => fake()->uuid(),
            'playback_id' => fake()->uuid(),
            'host' => VideoHost::Mux,
            'learning_objectives' => fake()->optional()->paragraphs(nb: 2, asText: true),
            'duration_seconds' => fake()->optional()->numberBetween(60, 3600),
            'gated' => true,
            'order' => fake()->numberBetween(0, 20),
            'visible' => fake()->boolean(),
            'publish_date' => fake()->optional()->date(),
            'thumbnail_extension' => null,
            'thumbnail_crops' => null,
        ];
    }

    public function withStockThumbnail(): static
    {
        return $this->afterCreating(function (Lesson $lesson): void {
            $imagePath = $this->getRandomStockImagePath();
            $extension = Str::afterLast($imagePath, '.');

            $path = "lesson/{$lesson->id}/thumbnail.{$extension}";

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $lesson->update([
                'thumbnail_extension' => $extension,
            ]);
        });
    }
}
