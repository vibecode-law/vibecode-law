<?php

use App\Actions\ShowcaseDraft\CreateShowcaseDraftAction;
use App\Enums\ShowcaseDraftStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraftImage;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

describe('draft creation', function () {
    test('creates a draft from an approved showcase', function () {
        $showcase = Showcase::factory()->approved()->create();

        $draft = (new CreateShowcaseDraftAction)->create(showcase: $showcase);

        expect($draft)->not->toBeNull();
        expect($draft->showcase_id)->toBe($showcase->id);
        expect($draft->status)->toBe(ShowcaseDraftStatus::Draft);
    });

    test('copies all editable fields from showcase to draft', function () {
        $showcase = Showcase::factory()->approved()->create([
            'title' => 'Test Title',
            'tagline' => 'Test Tagline',
            'description' => 'Test Description',
            'key_features' => 'Test Features',
            'help_needed' => 'Test Help',
            'url' => 'https://example.com',
            'video_url' => 'https://video.example.com',
            'source_url' => 'https://github.com/example',
        ]);

        $draft = (new CreateShowcaseDraftAction)->create(showcase: $showcase);

        expect($draft->title)->toBe($showcase->title);
        expect($draft->tagline)->toBe($showcase->tagline);
        expect($draft->description)->toBe($showcase->description);
        expect($draft->key_features)->toBe($showcase->key_features);
        expect($draft->help_needed)->toBe($showcase->help_needed);
        expect($draft->url)->toBe($showcase->url);
        expect($draft->video_url)->toBe($showcase->video_url);
        expect($draft->source_url)->toBe($showcase->source_url);
        expect($draft->source_status)->toBe($showcase->source_status);
    });

    test('copies practice areas to draft', function () {
        $practiceAreas = PracticeArea::factory()->count(3)->create();
        $showcase = Showcase::factory()->approved()->withoutPracticeAreas()->create();
        $showcase->practiceAreas()->attach($practiceAreas->pluck('id'));

        $draft = (new CreateShowcaseDraftAction)->create(showcase: $showcase);

        expect($draft->practiceAreas)->toHaveCount(3);
        expect($draft->practiceAreas->pluck('id')->toArray())->toBe($practiceAreas->pluck('id')->toArray());
    });

    test('creates draft images with keep action for existing images', function () {
        $showcase = Showcase::factory()->approved()->create();
        $images = $showcase->images()->createMany([
            ['path' => 'showcase/1/images/image1.jpg', 'filename' => 'image1.jpg', 'order' => 1, 'alt_text' => 'Alt 1'],
            ['path' => 'showcase/1/images/image2.jpg', 'filename' => 'image2.jpg', 'order' => 2, 'alt_text' => 'Alt 2'],
        ]);

        $draft = (new CreateShowcaseDraftAction)->create(showcase: $showcase);

        expect($draft->images)->toHaveCount(2);
        expect($draft->images[0]->action)->toBe(ShowcaseDraftImage::ACTION_KEEP);
        expect($draft->images[0]->original_image_id)->toBe($images[0]->id);
        expect($draft->images[1]->action)->toBe(ShowcaseDraftImage::ACTION_KEEP);
        expect($draft->images[1]->original_image_id)->toBe($images[1]->id);
    });

    test('copies thumbnail metadata to draft', function () {
        $showcase = Showcase::factory()->approved()->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
        ]);

        // Create the thumbnail file
        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.jpg", 'fake image content');

        $draft = (new CreateShowcaseDraftAction)->create(showcase: $showcase);

        expect($draft->thumbnail_extension)->toBe('jpg');
        expect($draft->thumbnail_crop)->toBe(['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100]);
    });

    test('copies thumbnail file to draft folder', function () {
        $showcase = Showcase::factory()->approved()->create([
            'thumbnail_extension' => 'png',
        ]);

        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.png", 'fake image content');

        $draft = (new CreateShowcaseDraftAction)->create(showcase: $showcase);

        Storage::disk('public')->assertExists("showcase-drafts/{$draft->id}/thumbnail.png");
    });
});
