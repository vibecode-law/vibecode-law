<?php

namespace Database\Factories\Organisation;

use App\Models\Organisation\Organisation;
use Database\Factories\Concerns\HasStockImages;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organisation\Organisation>
 */
class OrganisationFactory extends Factory
{
    use HasStockImages;

    protected $model = Organisation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'tagline' => fake()->sentence(),
            'about' => fake()->paragraphs(nb: 3, asText: true),
            'thumbnail_extension' => null,
            'thumbnail_crops' => null,
        ];
    }

    public function withStockThumbnail(): static
    {
        return $this->afterCreating(function (Organisation $organisation): void {
            $imagePath = $this->getRandomStockImagePath();
            $extension = Str::afterLast($imagePath, '.');

            $path = "organisation/{$organisation->id}/thumbnail.{$extension}";

            Storage::disk('public')->put($path, Storage::get($imagePath));

            $organisation->update([
                'thumbnail_extension' => $extension,
            ]);
        });
    }
}
