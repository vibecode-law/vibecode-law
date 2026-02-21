<?php

use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\User;
use Illuminate\Support\Carbon;

test('timestamps are cast to datetime', function () {
    $progress = CourseUser::factory()->viewed()->started()->completed()->create();

    expect($progress->viewed_at)->toBeInstanceOf(Carbon::class)
        ->and($progress->started_at)->toBeInstanceOf(Carbon::class)
        ->and($progress->completed_at)->toBeInstanceOf(Carbon::class);
});

test('timestamps default to null', function () {
    $progress = CourseUser::factory()->create();

    expect($progress->viewed_at)->toBeNull()
        ->and($progress->started_at)->toBeNull()
        ->and($progress->completed_at)->toBeNull();
});

describe('cascade deletes', function () {
    test('deleting user cascades to course_user', function () {
        $user = User::factory()->create();
        $progress = CourseUser::factory()->create(['user_id' => $user->id]);

        $user->delete();

        expect(CourseUser::query()->find($progress->id))->toBeNull();
    });

    test('deleting course cascades to course_user', function () {
        $course = Course::factory()->create();
        $progress = CourseUser::factory()->create(['course_id' => $course->id]);

        $course->delete();

        expect(CourseUser::query()->find($progress->id))->toBeNull();
    });
});

test('course and user combination is unique', function () {
    $progress = CourseUser::factory()->create();

    CourseUser::factory()->create([
        'course_id' => $progress->course_id,
        'user_id' => $progress->user_id,
    ]);
})->throws(Illuminate\Database\UniqueConstraintViolationException::class);
