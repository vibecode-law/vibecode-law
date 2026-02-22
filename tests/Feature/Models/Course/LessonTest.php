<?php

use App\Enums\MarkdownProfile;
use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonTranscriptLine;
use App\Models\Tag;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
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

describe('tags relationship', function () {
    test('lesson can have many tags', function () {
        $lesson = Lesson::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $lesson->tags()->attach($tags);

        expect($lesson->tags)->toHaveCount(3);
        expect($lesson->tags->first())->toBeInstanceOf(Tag::class);
    });

    test('detaching tag removes pivot record', function () {
        $lesson = Lesson::factory()->create();
        $tag = Tag::factory()->create();

        $lesson->tags()->attach($tag);

        expect($lesson->tags)->toHaveCount(1);

        $lesson->tags()->detach($tag);

        expect($lesson->fresh()->tags)->toHaveCount(0);
    });

    test('deleting lesson removes pivot records', function () {
        $lesson = Lesson::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $lesson->tags()->attach($tags);

        $lesson->delete();

        expect(
            Tag::query()
                ->whereHas('lessons', fn ($q) => $q->where('lesson_id', $lesson->id))
                ->count()
        )->toBe(0);
    });
});

describe('instructors relationship', function () {
    test('lesson can have many instructors', function () {
        $lesson = Lesson::factory()->create();
        $instructors = User::factory()->count(3)->create();

        $lesson->instructors()->attach($instructors);

        expect($lesson->instructors)->toHaveCount(3)
            ->each->toBeInstanceOf(User::class);
    });

    test('detaching instructor removes pivot record', function () {
        $lesson = Lesson::factory()->create();
        $instructor = User::factory()->create();

        $lesson->instructors()->attach($instructor);

        expect($lesson->instructors)->toHaveCount(1);

        $lesson->instructors()->detach($instructor);

        expect($lesson->fresh()->instructors)->toHaveCount(0);
    });

    test('deleting lesson removes instructor pivot records', function () {
        $lesson = Lesson::factory()->create();
        $instructors = User::factory()->count(2)->create();

        $lesson->instructors()->attach($instructors);

        $lessonId = $lesson->id;
        $lesson->delete();

        expect(
            \Illuminate\Support\Facades\DB::table('instructor_lesson')
                ->where('lesson_id', $lessonId)
                ->count()
        )->toBe(0);
    });
});

describe('transcriptLines relationship', function () {
    test('lesson has many transcript lines', function () {
        $lesson = Lesson::factory()->create();
        $lines = LessonTranscriptLine::factory()->count(3)->for($lesson)->create();

        expect($lesson->transcriptLines)->toHaveCount(3)
            ->each->toBeInstanceOf(LessonTranscriptLine::class);
    });

    test('transcript line belongs to a lesson', function () {
        $lesson = Lesson::factory()->create();
        $line = LessonTranscriptLine::factory()->for($lesson)->create();

        expect($line->lesson)->toBeInstanceOf(Lesson::class)
            ->and($line->lesson->id)->toBe($lesson->id);
    });

    test('deleting lesson cascades to transcript lines', function () {
        $lesson = Lesson::factory()->create();
        LessonTranscriptLine::factory()->count(3)->for($lesson)->create();

        $lesson->delete();

        expect(LessonTranscriptLine::query()->where('lesson_id', $lesson->id)->count())->toBe(0);
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

    it('clears all profile caches when a markdown field is updated', function (string $field) {
        $lesson = Lesson::factory()->create();
        $markdownService = app(MarkdownService::class);

        $cacheKey = "lesson|{$lesson->id}|$field";

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

        $lesson->update([$field => 'Updated content']);

        foreach (MarkdownProfile::cases() as $profile) {
            $fullKey = $markdownService->getCacheKey(profile: $profile, cacheKey: $cacheKey);
            expect(Cache::has(key: $fullKey))->toBeFalse();
        }
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

    it('clears all profile caches when lesson is deleted', function () {
        $lesson = Lesson::factory()->create();
        $markdownService = app(MarkdownService::class);

        foreach ($lesson->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $markdownService->render(
                    markdown: '**test content**',
                    profile: $profile,
                    cacheKey: "lesson|{$lesson->id}|$field"
                );
            }
        }

        foreach ($lesson->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $fullKey = $markdownService->getCacheKey(
                    profile: $profile,
                    cacheKey: "lesson|{$lesson->id}|$field"
                );
                expect(Cache::has(key: $fullKey))->toBeTrue();
            }
        }

        $lesson->delete();

        foreach ($lesson->getCachedFields() as $field) {
            foreach (MarkdownProfile::cases() as $profile) {
                $fullKey = $markdownService->getCacheKey(
                    profile: $profile,
                    cacheKey: "lesson|{$lesson->id}|$field"
                );
                expect(Cache::has(key: $fullKey))->toBeFalse();
            }
        }
    });
});
