<?php

use App\Enums\MarkdownProfile;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeShowcase;
use App\Models\Organisation\Organisation;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('uses slug as route key name', function () {
    $challenge = Challenge::factory()->create();

    expect($challenge->getRouteKeyName())->toBe('slug');
});

describe('showcases relationship', function () {
    test('challenge can have many showcases', function () {
        $challenge = Challenge::factory()->create();
        $showcases = Showcase::factory()->count(3)->create();

        $challenge->showcases()->attach($showcases);

        expect($challenge->showcases)->toHaveCount(3);
        expect($challenge->showcases->first())->toBeInstanceOf(Showcase::class);
    });

    test('showcases relationship uses ChallengeShowcase pivot model', function () {
        $challenge = Challenge::factory()->create();
        $showcase = Showcase::factory()->create();

        $challenge->showcases()->attach($showcase);

        expect($challenge->showcases->first()->pivot)->toBeInstanceOf(ChallengeShowcase::class);
    });

    test('showcases relationship includes timestamps on pivot', function () {
        $challenge = Challenge::factory()->create();
        $showcase = Showcase::factory()->create();

        $challenge->showcases()->attach($showcase);

        $pivot = $challenge->showcases->first()->pivot;

        expect($pivot->created_at)->not->toBeNull();
        expect($pivot->updated_at)->not->toBeNull();
    });

    test('detaching showcase removes pivot record', function () {
        $challenge = Challenge::factory()->create();
        $showcase = Showcase::factory()->create();

        $challenge->showcases()->attach($showcase);

        expect($challenge->showcases)->toHaveCount(1);

        $challenge->showcases()->detach($showcase);

        expect($challenge->fresh()->showcases)->toHaveCount(0);
    });
});

describe('organisation relationship', function () {
    test('challenge can belong to an organisation', function () {
        $organisation = Organisation::factory()->create();
        $challenge = Challenge::factory()->forOrganisation($organisation)->create();

        expect($challenge->organisation)->toBeInstanceOf(Organisation::class);
        expect($challenge->organisation->id)->toBe($organisation->id);
    });

    test('challenge organisation is nullable', function () {
        $challenge = Challenge::factory()->create();

        expect($challenge->organisation_id)->toBeNull();
        expect($challenge->organisation)->toBeNull();
    });

    test('challenge can be associated with organisation after creation', function () {
        $challenge = Challenge::factory()->create();
        $organisation = Organisation::factory()->create();

        $challenge->organisation()->associate($organisation);
        $challenge->save();

        expect($challenge->fresh()->organisation->id)->toBe($organisation->id);
    });
});

describe('user relationship', function () {
    test('challenge can belong to a user', function () {
        $user = User::factory()->create();
        $challenge = Challenge::factory()->forUser($user)->create();

        expect($challenge->user)->toBeInstanceOf(User::class);
        expect($challenge->user->id)->toBe($user->id);
    });

    test('challenge user is nullable', function () {
        $challenge = Challenge::factory()->create();

        expect($challenge->user_id)->toBeNull();
        expect($challenge->user)->toBeNull();
    });

    test('challenge can be associated with user after creation', function () {
        $challenge = Challenge::factory()->create();
        $user = User::factory()->create();

        $challenge->user()->associate($user);
        $challenge->save();

        expect($challenge->fresh()->user->id)->toBe($user->id);
    });
});

test('thumbnail url returns null when thumbnail_extension is null', function () {
    $challenge = Challenge::factory()->make(['thumbnail_extension' => null]);

    expect($challenge->thumbnail_url)->toBeNull();
});

test('thumbnail url returns storage url when image transform base url is not set', function () {
    Storage::fake('public');
    Config::set('services.image-transform.base_url', null);

    $challenge = Challenge::factory()->create(['thumbnail_extension' => 'jpg']);

    expect($challenge->thumbnail_url)->toBe(Storage::disk('public')->url("challenge/{$challenge->id}/thumbnail.jpg"));
});

test('thumbnail url returns image transform url when image transform base url is set', function () {
    Config::set('services.image-transform.base_url', 'https://images.example.com');

    $challenge = Challenge::factory()->create(['thumbnail_extension' => 'jpg']);

    expect($challenge->thumbnail_url)->toBe("https://images.example.com/challenge/{$challenge->id}/thumbnail.jpg");
});

test('thumbnail rect strings returns null when thumbnail_crops is null', function () {
    $challenge = Challenge::factory()->make(['thumbnail_crops' => null]);

    expect($challenge->thumbnail_rect_strings)->toBeNull();
});

test('thumbnail rect strings returns correct format for multiple crops', function () {
    $challenge = Challenge::factory()->make([
        'thumbnail_crops' => [
            'square' => ['x' => 50, 'y' => 100, 'width' => 200, 'height' => 200],
            'landscape' => ['x' => 10, 'y' => 20, 'width' => 400, 'height' => 225],
        ],
    ]);

    expect($challenge->thumbnail_rect_strings)->toBe([
        'square' => 'rect=50,100,200,200',
        'landscape' => 'rect=10,20,400,225',
    ]);
});

describe('markdown cache clearing on model events', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('clears all profile caches when description is updated', function () {
        $challenge = Challenge::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "challenge|{$challenge->id}|description";

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

        $challenge->update(['description' => 'Updated content']);

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
    });

    it('does not clear markdown cache when non-markdown fields are updated', function () {
        $challenge = Challenge::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "challenge|{$challenge->id}|description";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);

        expect(Cache::has(key: $fullKey))->toBeTrue();

        $challenge->update(['title' => 'Updated Title']);

        expect(Cache::has(key: $fullKey))->toBeTrue();
    });

    it('clears all profile caches when challenge is deleted', function () {
        $challenge = Challenge::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "challenge|{$challenge->id}|description";

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

        $challenge->delete();

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
    });
});
