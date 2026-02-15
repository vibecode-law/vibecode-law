<?php

namespace Database\Factories\Course;

use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course\LessonUser>
 */
class LessonUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'viewed_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'playback_time_milliseconds' => null,
        ];
    }

    public function viewed(): static
    {
        return $this->state(fn (): array => ['viewed_at' => now()]);
    }

    public function started(): static
    {
        return $this->state(fn (): array => ['started_at' => now()]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['completed_at' => now()]);
    }
}
