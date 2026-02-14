<?php

namespace Database\Factories\Course;

use App\Enums\ExperienceLevel;
use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->words(nb: 3, asText: true);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->lexify('???'),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraphs(nb: 3, asText: true),
            'order' => fake()->numberBetween(0, 20),
            'experience_level' => fake()->randomElement(ExperienceLevel::cases()),
        ];
    }
}
