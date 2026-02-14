<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

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
