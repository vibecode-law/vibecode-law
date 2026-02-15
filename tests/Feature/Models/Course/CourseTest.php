<?php

use App\Models\Course\Course;
use App\Models\Course\CourseTag;
use App\Models\Course\Lesson;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

test('uses slug as route key name', function () {
    $course = Course::factory()->create();

    expect($course->getRouteKeyName())->toBe('slug');
});

describe('user relationship', function () {
    test('course belongs to a user', function () {
        $user = User::factory()->create();
        $course = Course::factory()->for($user)->create();

        expect($course->user)->toBeInstanceOf(User::class);
        expect($course->user->id)->toBe($user->id);
    });

    test('deleting user nulls course user_id', function () {
        $user = User::factory()->create();
        $course = Course::factory()->for($user)->create();

        $user->delete();

        expect(Course::query()->find($course->id)->user_id)->toBeNull();
    });
});

describe('lessons relationship', function () {
    test('course can have many lessons', function () {
        $course = Course::factory()->create();
        Lesson::factory()->count(3)->for($course)->create();

        expect($course->lessons)->toHaveCount(3);
        expect($course->lessons->first())->toBeInstanceOf(Lesson::class);
    });

    test('lessons are ordered by order column', function () {
        $course = Course::factory()->create();
        Lesson::factory()->for($course)->create(['order' => 3]);
        Lesson::factory()->for($course)->create(['order' => 1]);
        Lesson::factory()->for($course)->create(['order' => 2]);

        $orders = $course->lessons->pluck('order')->all();

        expect($orders)->toBe([1, 2, 3]);
    });

    test('course with no lessons returns empty collection', function () {
        $course = Course::factory()->create();

        expect($course->lessons)->toBeEmpty();
    });
});

describe('tags relationship', function () {
    test('course can have many tags', function () {
        $course = Course::factory()->create();
        $tags = CourseTag::factory()->count(3)->create();

        $course->tags()->attach($tags);

        expect($course->tags)->toHaveCount(3);
        expect($course->tags->first())->toBeInstanceOf(CourseTag::class);
    });

    test('tags relationship includes timestamps on pivot', function () {
        $course = Course::factory()->create();
        $tag = CourseTag::factory()->create();

        $course->tags()->attach($tag);

        $pivot = $course->tags->first()->pivot;

        expect($pivot->created_at)->not->toBeNull();
        expect($pivot->updated_at)->not->toBeNull();
    });

    test('detaching tag removes pivot record', function () {
        $course = Course::factory()->create();
        $tag = CourseTag::factory()->create();

        $course->tags()->attach($tag);

        expect($course->tags)->toHaveCount(1);

        $course->tags()->detach($tag);

        expect($course->fresh()->tags)->toHaveCount(0);
    });

    test('deleting course removes pivot records', function () {
        $course = Course::factory()->create();
        $tags = CourseTag::factory()->count(2)->create();

        $course->tags()->attach($tags);

        $course->delete();

        expect(
            CourseTag::query()
                ->whereHas('courses', fn ($q) => $q->where('course_id', $course->id))
                ->count()
        )->toBe(0);
    });
});

describe('users relationship', function () {
    test('course belongs to many users', function () {
        $course = Course::factory()->create();
        $users = User::factory()->count(3)->create();

        $course->users()->attach($users);

        expect($course->users)->toHaveCount(3)
            ->each->toBeInstanceOf(User::class);
    });

    test('pivot timestamps are accessible', function () {
        $course = Course::factory()->create();
        $user = User::factory()->create();

        $course->users()->attach($user, [
            'viewed_at' => now(),
            'started_at' => now(),
            'completed_at' => null,
        ]);

        $pivot = $course->users->first()->pivot;

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
        $course = Course::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "course|{$course->id}|$field";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);

        expect(Cache::has(key: $fullKey))->toBeTrue();

        $course->update([$field => 'Updated content']);

        expect(Cache::has(key: $fullKey))->toBeFalse();
    })->with(['description', 'learning_objectives']);

    it('does not clear markdown cache when non-markdown fields are updated', function () {
        $course = Course::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "course|{$course->id}|description";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);

        expect(Cache::has(key: $fullKey))->toBeTrue();

        $course->update(['title' => 'Updated Title']);

        expect(Cache::has(key: $fullKey))->toBeTrue();
    });

    it('clears markdown cache when course is deleted', function () {
        $course = Course::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKeys = new Collection($course->getCachedFields())->map(fn (string $field) => "course|{$course->id}|$field");

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

        $course->delete();

        foreach ($cacheKeys as $cacheKey) {
            $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
    });
});
