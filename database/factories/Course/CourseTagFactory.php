<?php

namespace Database\Factories\Course;

use App\Models\Course\CourseTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course\CourseTag>
 */
class CourseTagFactory extends Factory
{
    protected $model = CourseTag::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(nb: 2, asText: true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
