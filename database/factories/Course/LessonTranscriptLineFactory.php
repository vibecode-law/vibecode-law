<?php

namespace Database\Factories\Course;

use App\Models\Course\Lesson;
use App\Models\Course\LessonTranscriptLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course\LessonTranscriptLine>
 */
class LessonTranscriptLineFactory extends Factory
{
    protected $model = LessonTranscriptLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startSeconds = fake()->randomFloat(nbMaxDecimals: 3, min: 0, max: 3600);

        return [
            'lesson_id' => Lesson::factory(),
            'start_seconds' => $startSeconds,
            'end_seconds' => $startSeconds + fake()->randomFloat(nbMaxDecimals: 3, min: 1, max: 30),
            'text' => fake()->sentence(),
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
