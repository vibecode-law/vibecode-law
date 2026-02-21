<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update allow preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertRedirect();
    });

    test('does not allow regular users to update allow preview', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertForbidden();
    });

    test('does not allow moderators to update allow preview', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertForbidden();
    });
});

describe('enable preview', function () {
    test('enables allow_preview when title, slug and tagline are present', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'allow_preview' => false,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionHas('flash.message', [
                'message' => 'Preview enabled.',
                'type' => 'success',
            ]);

        expect($lesson->refresh()->allow_preview)->toBeTrue();
    });

    test('succeeds even without description, learning_objectives, or video host sync', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => null,
            'learning_objectives' => null,
            'asset_id' => null,
            'playback_id' => null,
            'duration_seconds' => null,
            'allow_preview' => false,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionDoesntHaveErrors();

        expect($lesson->refresh()->allow_preview)->toBeTrue();
    });

    test('fails when required fields are missing', function (string $field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            $field => null,
            'allow_preview' => false,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertSessionHasErrors('allow_preview');
    })->with([
        'missing tagline' => 'tagline',
    ]);
});

describe('disable preview', function () {
    test('disables allow_preview without readiness checks', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'tagline' => null,
            'allow_preview' => true,
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => false,
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionHas('flash.message', [
                'message' => 'Preview disabled.',
                'type' => 'success',
            ]);

        expect($lesson->refresh()->allow_preview)->toBeFalse();
    });
});

describe('validation', function () {
    test('requires allow_preview and must be boolean', function (array $data, string $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing allow_preview' => [fn () => [], 'allow_preview'],
        'non-boolean allow_preview' => [fn () => ['allow_preview' => 'not-a-boolean'], 'allow_preview'],
    ]);

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.allow-preview', [$course, $lesson]), [
            'allow_preview' => true,
        ])->assertNotFound();
    });
});
