<?php

namespace Database\Factories\Course;

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course\Lesson>
 */
class LessonFactory extends Factory
{
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
            'embed' => fake()->url(),
            'host' => VideoHost::Mux,
            'order' => fake()->numberBetween(0, 20),
        ];
    }
}
