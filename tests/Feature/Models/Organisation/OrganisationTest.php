<?php

use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

describe('challenges relationship', function () {
    test('organisation can have many challenges', function () {
        $organisation = Organisation::factory()->create();
        Challenge::factory()->count(3)->forOrganisation($organisation)->create();

        expect($organisation->challenges)->toHaveCount(3);
        expect($organisation->challenges->first())->toBeInstanceOf(Challenge::class);
    });

    test('organisation challenges are returned in correct order', function () {
        $organisation = Organisation::factory()->create();

        $challenge1 = Challenge::factory()->forOrganisation($organisation)->create();
        $challenge2 = Challenge::factory()->forOrganisation($organisation)->create();
        $challenge3 = Challenge::factory()->forOrganisation($organisation)->create();

        expect($organisation->challenges->pluck('id')->toArray())
            ->toBe([$challenge1->id, $challenge2->id, $challenge3->id]);
    });

    test('deleting organisation sets challenge organisation_id to null', function () {
        $organisation = Organisation::factory()->create();
        $challenge = Challenge::factory()->forOrganisation($organisation)->create();

        expect($challenge->organisation_id)->toBe($organisation->id);

        $organisation->delete();

        expect($challenge->fresh()->organisation_id)->toBeNull();
    });
});

describe('thumbnail_url', function () {
    test('returns null when thumbnail_extension is null', function () {
        $organisation = Organisation::factory()->make(['thumbnail_extension' => null]);

        expect($organisation->thumbnail_url)->toBeNull();
    });

    test('returns storage url when image transform base url is not set', function () {
        Storage::fake('public');
        Config::set('services.image-transform.base_url', null);

        $organisation = Organisation::factory()->create(['thumbnail_extension' => 'jpg']);

        expect($organisation->thumbnail_url)->toBe(
            Storage::disk('public')->url("organisation/{$organisation->id}/thumbnail.jpg")
        );
    });

    test('returns image transform url when image transform base url is set', function () {
        Config::set('services.image-transform.base_url', 'https://images.example.com');

        $organisation = Organisation::factory()->create(['thumbnail_extension' => 'jpg']);

        expect($organisation->thumbnail_url)->toBe(
            "https://images.example.com/organisation/{$organisation->id}/thumbnail.jpg"
        );
    });
});

describe('thumbnail_rect_strings', function () {
    test('returns null when thumbnail_crops is null', function () {
        $organisation = Organisation::factory()->make(['thumbnail_crops' => null]);

        expect($organisation->thumbnail_rect_strings)->toBeNull();
    });

    test('returns correct format for multiple crops', function () {
        $organisation = Organisation::factory()->make([
            'thumbnail_crops' => [
                'square' => ['x' => 50, 'y' => 100, 'width' => 200, 'height' => 200],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
            ],
        ]);

        expect($organisation->thumbnail_rect_strings)->toBe([
            'square' => 'rect=50,100,200,200',
            'landscape' => 'rect=0,50,800,450',
        ]);
    });
});
