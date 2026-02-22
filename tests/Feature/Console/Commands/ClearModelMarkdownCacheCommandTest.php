<?php

use App\Enums\MarkdownProfile;
use App\Models\Course\Course;
use App\Models\Showcase\Showcase;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Cache::flush();
});

describe('app:model-markdown:clear', function () {
    it('clears markdown caches for all models with the trait', function () {
        $showcase = Showcase::factory()->create();
        $course = Course::factory()->create();
        $markdownService = app(MarkdownService::class);

        $showcaseKey = "showcase|{$showcase->id}|description";
        $courseKey = "course|{$course->id}|description";

        $markdownService->render(markdown: '**test**', cacheKey: $showcaseKey);
        $markdownService->render(markdown: '**test**', cacheKey: $courseKey);

        expect(Cache::has(key: $markdownService->getCacheKey(cacheKey: $showcaseKey)))->toBeTrue()
            ->and(Cache::has(key: $markdownService->getCacheKey(cacheKey: $courseKey)))->toBeTrue();

        artisan(command: 'app:model-markdown:clear')
            ->assertSuccessful()
            ->expectsOutputToContain('Cleared');

        expect(Cache::has(key: $markdownService->getCacheKey(cacheKey: $showcaseKey)))->toBeFalse()
            ->and(Cache::has(key: $markdownService->getCacheKey(cacheKey: $courseKey)))->toBeFalse();
    });

    it('clears all profiles for each field', function () {
        $showcase = Showcase::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "showcase|{$showcase->id}|description";

        foreach (MarkdownProfile::cases() as $profile) {
            $markdownService->render(
                markdown: '**test**',
                profile: $profile,
                cacheKey: $cacheKey
            );
        }

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeTrue();
        }

        artisan(command: 'app:model-markdown:clear')
            ->assertSuccessful();

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
    });

    it('filters to a specific model when --model option is provided', function () {
        $showcase = Showcase::factory()->create();
        $course = Course::factory()->create();
        $markdownService = app(MarkdownService::class);

        $showcaseKey = "showcase|{$showcase->id}|description";
        $courseKey = "course|{$course->id}|description";

        $markdownService->render(markdown: '**test**', cacheKey: $showcaseKey);
        $markdownService->render(markdown: '**test**', cacheKey: $courseKey);

        artisan(command: 'app:model-markdown:clear', parameters: ['--model' => 'Showcase'])
            ->assertSuccessful()
            ->expectsOutputToContain('Showcase');

        expect(Cache::has(key: $markdownService->getCacheKey(cacheKey: $showcaseKey)))->toBeFalse()
            ->and(Cache::has(key: $markdownService->getCacheKey(cacheKey: $courseKey)))->toBeTrue();
    });

    it('matches model name case-insensitively', function () {
        artisan(command: 'app:model-markdown:clear', parameters: ['--model' => 'showcase'])
            ->assertSuccessful()
            ->expectsOutputToContain('Showcase');
    });

    it('returns failure when --model does not match any discovered model', function () {
        artisan(command: 'app:model-markdown:clear', parameters: ['--model' => 'Nonexistent'])
            ->assertFailed()
            ->expectsOutputToContain('not found');
    });

    it('returns success even when no records exist', function () {
        artisan(command: 'app:model-markdown:clear')
            ->assertSuccessful()
            ->expectsOutputToContain('Cleared 0 markdown cache(s)');
    });
});
