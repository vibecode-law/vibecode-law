<?php

use App\Actions\ShowcaseDraft\ApproveShowcaseDraftAction;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use App\Models\Showcase\ShowcaseImage;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

describe('field updates', function () {
    test('updates showcase fields from draft', function () {
        $showcase = Showcase::factory()->approved()->create([
            'title' => 'Original Title',
            'tagline' => 'Original Tagline',
        ]);

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
            'title' => 'Updated Title',
            'tagline' => 'Updated Tagline',
            'description' => 'Updated Description',
        ]);

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        $showcase->refresh();

        expect($showcase->title)->toBe('Updated Title');
        expect($showcase->tagline)->toBe('Updated Tagline');
        expect($showcase->description)->toBe('Updated Description');
    });

    test('syncs practice areas from draft to showcase', function () {
        $oldPracticeAreas = PracticeArea::factory()->count(2)->create();
        $newPracticeAreas = PracticeArea::factory()->count(3)->create();

        $showcase = Showcase::factory()->approved()->withoutPracticeAreas()->create();
        $showcase->practiceAreas()->attach($oldPracticeAreas->pluck('id'));

        $draft = ShowcaseDraft::factory()->pending()->withoutPracticeAreas()->create([
            'showcase_id' => $showcase->id,
        ]);
        $draft->practiceAreas()->attach($newPracticeAreas->pluck('id'));

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        $showcase->refresh();

        expect($showcase->practiceAreas)->toHaveCount(3);
        expect($showcase->practiceAreas->pluck('id')->toArray())->toBe($newPracticeAreas->pluck('id')->toArray());
    });
});

describe('draft deletion', function () {
    test('deletes the draft after approval', function () {
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        expect(ShowcaseDraft::find($draft->id))->toBeNull();
    });
});

describe('image handling', function () {
    test('keeps images with keep action and updates order', function () {
        $showcase = Showcase::factory()->approved()->create();
        $image = ShowcaseImage::factory()->create([
            'showcase_id' => $showcase->id,
            'order' => 1,
            'alt_text' => 'Original Alt',
        ]);

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        ShowcaseDraftImage::factory()->keep($image)->create([
            'showcase_draft_id' => $draft->id,
            'order' => 5,
            'alt_text' => 'Updated Alt',
        ]);

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        $image->refresh();

        expect($image->order)->toBe(5);
        expect($image->alt_text)->toBe('Updated Alt');
    });

    test('removes images with remove action', function () {
        $showcase = Showcase::factory()->approved()->create();
        $image = ShowcaseImage::factory()->create([
            'showcase_id' => $showcase->id,
            'path' => "showcase/{$showcase->id}/images/test.jpg",
        ]);

        Storage::disk('public')->put($image->path, 'fake image');

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        ShowcaseDraftImage::factory()->remove($image)->create([
            'showcase_draft_id' => $draft->id,
        ]);

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        expect(ShowcaseImage::find($image->id))->toBeNull();
    });

    test('adds new images from draft to showcase', function () {
        $showcase = Showcase::factory()->approved()->create();

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        $draftImagePath = "showcase-drafts/{$draft->id}/images/new-image.jpg";
        Storage::disk('public')->put($draftImagePath, 'fake image');

        ShowcaseDraftImage::factory()->add()->create([
            'showcase_draft_id' => $draft->id,
            'path' => $draftImagePath,
            'filename' => 'new-image.jpg',
            'alt_text' => 'New Image Alt',
            'order' => 1,
        ]);

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        $showcase->refresh();

        expect($showcase->images)->toHaveCount(1);
        expect($showcase->images[0]->filename)->toBe('new-image.jpg');
        expect($showcase->images[0]->alt_text)->toBe('New Image Alt');
    });
});

describe('thumbnail handling', function () {
    test('updates thumbnail from draft with different extension', function () {
        $showcase = Showcase::factory()->approved()->create([
            'thumbnail_extension' => 'png',
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ]);

        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.png", 'old thumbnail');

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 200, 'height' => 200],
        ]);

        Storage::disk('public')->put("showcase-drafts/{$draft->id}/thumbnail.jpg", 'new thumbnail');

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        $showcase->refresh();

        expect($showcase->thumbnail_extension)->toBe('jpg');
        expect($showcase->thumbnail_crop)->toBe(['x' => 10, 'y' => 20, 'width' => 200, 'height' => 200]);
        Storage::disk('public')->assertExists("showcase/{$showcase->id}/thumbnail.jpg");
        Storage::disk('public')->assertMissing("showcase/{$showcase->id}/thumbnail.png");
    });

    test('updates thumbnail from draft with same extension', function () {
        $showcase = Showcase::factory()->approved()->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ]);

        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.jpg", 'old thumbnail content');

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 50, 'y' => 50, 'width' => 300, 'height' => 300],
        ]);

        Storage::disk('public')->put("showcase-drafts/{$draft->id}/thumbnail.jpg", 'new thumbnail content');

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        $showcase->refresh();

        expect($showcase->thumbnail_extension)->toBe('jpg');
        expect($showcase->thumbnail_crop)->toBe(['x' => 50, 'y' => 50, 'width' => 300, 'height' => 300]);
        Storage::disk('public')->assertExists("showcase/{$showcase->id}/thumbnail.jpg");
        expect(Storage::disk('public')->get("showcase/{$showcase->id}/thumbnail.jpg"))->toBe('new thumbnail content');
    });
});

describe('markdown cache clearing', function () {
    beforeEach(function () {
        Storage::fake('public');
        Cache::flush();
    });

    test('clears markdown cache when approving draft with updated description', function () {
        $showcase = Showcase::factory()->approved()->create([
            'description' => 'Original content',
        ]);

        $markdownService = app(MarkdownService::class);
        $cacheKey = "showcase|{$showcase->id}|description";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);
        expect(Cache::has(key: $fullKey))->toBeTrue();

        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
            'description' => 'Updated content from draft',
        ]);

        (new ApproveShowcaseDraftAction)->approve(draft: $draft);

        expect(Cache::has(key: $fullKey))->toBeFalse();
    });
});
