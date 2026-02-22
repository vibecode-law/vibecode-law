<?php

use App\Enums\MarkdownProfile;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeShowcase;
use App\Models\Showcase\Showcase;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('thumbnail url returns null when thumbnail_extension is null', function () {
    $showcase = Showcase::factory()->withoutPracticeAreas()->make(['thumbnail_extension' => null]);

    expect($showcase->thumbnail_url)->toBeNull();
});

test('thumbnail url returns storage url when image transform base url is not set', function () {
    Storage::fake('public');
    Config::set('services.image-transform.base_url', null);

    $showcase = Showcase::factory()->withoutPracticeAreas()->create(['thumbnail_extension' => 'jpg']);

    expect($showcase->thumbnail_url)->toBe(
        Storage::disk('public')->url("showcase/{$showcase->id}/thumbnail.jpg")
    );
});

test('thumbnail url returns image transform url when image transform base url is set', function () {
    Config::set('services.image-transform.base_url', 'https://images.example.com');

    $showcase = Showcase::factory()->withoutPracticeAreas()->create(['thumbnail_extension' => 'png']);

    expect($showcase->thumbnail_url)->toBe("https://images.example.com/showcase/{$showcase->id}/thumbnail.png");
});

test('thumbnail url does not include rect even when crop data is set', function () {
    Config::set('services.image-transform.base_url', 'https://images.example.com');

    $showcase = Showcase::factory()->withoutPracticeAreas()->create([
        'thumbnail_extension' => 'jpg',
        'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 300, 'height' => 300],
    ]);

    expect($showcase->thumbnail_url)->toBe(
        "https://images.example.com/showcase/{$showcase->id}/thumbnail.jpg"
    );
});

test('thumbnail rect string returns null when no crop data is set', function () {
    $showcase = Showcase::factory()->withoutPracticeAreas()->make(['thumbnail_crop' => null]);

    expect($showcase->thumbnail_rect_string)->toBeNull();
});

test('thumbnail rect string returns correct format', function () {
    $showcase = Showcase::factory()->withoutPracticeAreas()->make([
        'thumbnail_crop' => ['x' => 50, 'y' => 100, 'width' => 200, 'height' => 200],
    ]);

    expect($showcase->thumbnail_rect_string)->toBe('rect=50,100,200,200');
});

test('youtube id returns null when video_url is null', function () {
    $showcase = Showcase::factory()->withoutPracticeAreas()->make(['video_url' => null]);

    expect($showcase->youtube_id)->toBeNull();
});

test('youtube id returns extracted id from valid youtube url', function () {
    $showcase = Showcase::factory()->withoutPracticeAreas()->make([
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    expect($showcase->youtube_id)->toBe('dQw4w9WgXcQ');
});

test('youtube id returns null for invalid youtube url', function () {
    $showcase = Showcase::factory()->withoutPracticeAreas()->make([
        'video_url' => 'https://example.com/video',
    ]);

    expect($showcase->youtube_id)->toBeNull();
});

describe('markdown cache clearing on model events', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('clears all profile caches when a markdown field is updated', function (string $field) {
        $showcase = Showcase::factory()->withoutPracticeAreas()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "showcase|{$showcase->id}|$field";

        foreach (MarkdownProfile::cases() as $profile) {
            $markdownService->render(
                markdown: '**test content**',
                profile: $profile,
                cacheKey: $cacheKey
            );
        }

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeTrue();
        }

        $showcase->update([$field => 'Updated content']);

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
    })->with(['description', 'help_needed', 'key_features']);

    it('does not clear markdown cache when non-markdown fields are updated', function () {
        $showcase = Showcase::factory()->withoutPracticeAreas()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "showcase|{$showcase->id}|description";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);

        expect(Cache::has(key: $fullKey))->toBeTrue();

        $showcase->update(['title' => 'Updated Title']);

        expect(Cache::has(key: $fullKey))->toBeTrue();
    });

    it('clears all profile caches when showcase is deleted', function () {
        $showcase = Showcase::factory()->withoutPracticeAreas()->create();
        $markdownService = app(MarkdownService::class);

        foreach ($showcase->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $markdownService->render(
                    markdown: '**test content**',
                    profile: $profile,
                    cacheKey: "showcase|{$showcase->id}|$field"
                );
            }
        }

        foreach ($showcase->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $fullKey = $markdownService->getCacheKey(
                    profile: $profile,
                    cacheKey: "showcase|{$showcase->id}|$field"
                );
                expect(Cache::has(key: $fullKey))->toBeTrue();
            }
        }

        $showcase->delete();

        foreach ($showcase->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $fullKey = $markdownService->getCacheKey(
                    profile: $profile,
                    cacheKey: "showcase|{$showcase->id}|$field"
                );
                expect(Cache::has(key: $fullKey))->toBeFalse();
            }
        }
    });
});

describe('challenges relationship', function () {
    test('showcase can have many challenges', function () {
        $showcase = Showcase::factory()->create();
        $challenges = Challenge::factory()->count(3)->create();

        $showcase->challenges()->attach($challenges);

        expect($showcase->challenges)->toHaveCount(3);
        expect($showcase->challenges->first())->toBeInstanceOf(Challenge::class);
    });

    test('challenges relationship uses ChallengeShowcase pivot model', function () {
        $showcase = Showcase::factory()->create();
        $challenge = Challenge::factory()->create();

        $showcase->challenges()->attach($challenge);

        expect($showcase->challenges->first()->pivot)->toBeInstanceOf(ChallengeShowcase::class);
    });

    test('challenges relationship includes timestamps on pivot', function () {
        $showcase = Showcase::factory()->create();
        $challenge = Challenge::factory()->create();

        $showcase->challenges()->attach($challenge);

        $pivot = $showcase->challenges->first()->pivot;

        expect($pivot->created_at)->not->toBeNull();
        expect($pivot->updated_at)->not->toBeNull();
    });
});
