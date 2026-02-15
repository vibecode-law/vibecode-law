<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

test('uses slug as route key name', function () {
    $lesson = Lesson::factory()->create();

    expect($lesson->getRouteKeyName())->toBe('slug');
});

test('host is cast to VideoHost enum', function () {
    $lesson = Lesson::factory()->create(['host' => VideoHost::Mux]);

    expect($lesson->host)->toBe(VideoHost::Mux);
});

describe('course relationship', function () {
    test('lesson belongs to a course', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->for($course)->create();

        expect($lesson->course)->toBeInstanceOf(Course::class);
        expect($lesson->course->id)->toBe($course->id);
    });

    test('deleting course cascades to lessons', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->for($course)->create();

        $course->delete();

        expect(Lesson::query()->find($lesson->id))->toBeNull();
    });
});

describe('users relationship', function () {
    test('lesson belongs to many users', function () {
        $lesson = Lesson::factory()->create();
        $users = User::factory()->count(3)->create();

        $lesson->users()->attach($users);

        expect($lesson->users)->toHaveCount(3)
            ->each->toBeInstanceOf(User::class);
    });

    test('pivot timestamps are accessible', function () {
        $lesson = Lesson::factory()->create();
        $user = User::factory()->create();

        $lesson->users()->attach($user, [
            'viewed_at' => now(),
            'started_at' => now(),
            'completed_at' => null,
        ]);

        $pivot = $lesson->users->first()->pivot;

        expect($pivot->viewed_at)->not->toBeNull()
            ->and($pivot->started_at)->not->toBeNull()
            ->and($pivot->completed_at)->toBeNull();
    });
});

describe('markdown cache clearing on model events', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('clears a specific markdown cache when that field is updated', function (string $field) {
        $lesson = Lesson::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "lesson|{$lesson->id}|$field";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);

        expect(Cache::has(key: $fullKey))->toBeTrue();

        $lesson->update([$field => 'Updated content']);

        expect(Cache::has(key: $fullKey))->toBeFalse();
    })->with(['description', 'learning_objectives', 'copy']);

    it('does not clear markdown cache when non-markdown fields are updated', function () {
        $lesson = Lesson::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "lesson|{$lesson->id}|description";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);

        expect(Cache::has(key: $fullKey))->toBeTrue();

        $lesson->update(['title' => 'Updated Title']);

        expect(Cache::has(key: $fullKey))->toBeTrue();
    });

    it('clears markdown cache when lesson is deleted', function () {
        $lesson = Lesson::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKeys = new Collection($lesson->getCachedFields())->map(fn (string $field) => "lesson|{$lesson->id}|$field");

        foreach ($cacheKeys as $cacheKey) {
            $markdownService->render(
                markdown: '**test content**',
                cacheKey: $cacheKey
            );
        }

        foreach ($cacheKeys as $cacheKey) {
            $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeTrue();
        }

        $lesson->delete();

        foreach ($cacheKeys as $cacheKey) {
            $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
    });
});
