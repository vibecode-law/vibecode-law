<?php

namespace Database\Factories;

use App\Enums\TagType;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(nb: 2, asText: true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(TagType::cases()),
        ];
    }
}
