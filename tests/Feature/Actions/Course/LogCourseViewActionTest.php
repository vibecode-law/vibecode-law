<?php

use App\Actions\Course\LogCourseViewAction;
use App\Models\Course\Course;
use App\Models\Course\CourseUser;
use App\Models\User;

it('creates a course_user record with viewed_at when no record exists', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();

    (new LogCourseViewAction)->handle(course: $course, user: $user);

    $pivot = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($pivot)
        ->not->toBeNull()
        ->viewed_at->not->toBeNull()
        ->started_at->toBeNull()
        ->completed_at->toBeNull();
});

it('sets viewed_at when record exists with null viewed_at', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();

    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'viewed_at' => null,
        'started_at' => now(),
    ]);

    (new LogCourseViewAction)->handle(course: $course, user: $user);

    $pivot = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($pivot)
        ->viewed_at->not->toBeNull()
        ->started_at->not->toBeNull();
});

it('does not overwrite viewed_at when already set', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();

    $originalViewedAt = now()->subMinutes(5);
    CourseUser::factory()->create([
        'course_id' => $course->id,
        'user_id' => $user->id,
        'viewed_at' => $originalViewedAt,
    ]);

    (new LogCourseViewAction)->handle(course: $course, user: $user);

    $pivot = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->first();

    expect($pivot->viewed_at->timestamp)->toBe($originalViewedAt->timestamp);
});

it('does not create duplicate records on repeated calls', function () {
    $user = User::factory()->create();
    $course = Course::factory()->published()->create();

    (new LogCourseViewAction)->handle(course: $course, user: $user);
    (new LogCourseViewAction)->handle(course: $course, user: $user);

    $count = CourseUser::query()
        ->where(column: 'course_id', operator: '=', value: $course->id)
        ->where(column: 'user_id', operator: '=', value: $user->id)
        ->count();

    expect($count)->toBe(1);
});
