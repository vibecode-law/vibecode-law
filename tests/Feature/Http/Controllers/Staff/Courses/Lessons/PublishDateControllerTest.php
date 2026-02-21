<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Course\LessonTranscriptLine;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update publish date', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',

            'asset_id' => 'asset-123',
            'playback_id' => 'playback-123',
            'duration_seconds' => 120,
        ]);
        Storage::put("lessons/{$lesson->id}/transcript.vtt", 'WEBVTT');
        Storage::put("lessons/{$lesson->id}/transcript.txt", 'Hello world');
        LessonTranscriptLine::factory()->for($lesson)->create();

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertRedirect();
    });

    test('does not allow regular users to update publish date', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertForbidden();
    });

    test('does not allow moderators to update publish date', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertForbidden();
    });
});

describe('setting publish date', function () {
    test('sets publish date when all requirements are met', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',

            'asset_id' => 'asset-123',
            'playback_id' => 'playback-123',
            'duration_seconds' => 120,
            'publish_date' => null,
        ]);
        Storage::put("lessons/{$lesson->id}/transcript.vtt", 'WEBVTT');
        Storage::put("lessons/{$lesson->id}/transcript.txt", 'Hello world');
        LessonTranscriptLine::factory()->for($lesson)->create();

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionHas('flash.message', [
                'message' => 'Publish date set successfully.',
                'type' => 'success',
            ]);

        $lesson->refresh();

        expect($lesson->publish_date->format('Y-m-d'))->toBe('2026-06-01');
    });

    test('clears publish date without readiness checks', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => null,
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionHas('flash.message', [
                'message' => 'Publish date cleared.',
                'type' => 'success',
            ]);

        $lesson->refresh();

        expect($lesson->publish_date)->toBeNull();
    });

    test('clears publish date even when required fields are missing', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'learning_objectives' => null,
            'publish_date' => '2026-06-01',
        ]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => null,
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionDoesntHaveErrors();

        expect($lesson->refresh()->publish_date)->toBeNull();
    });
});

describe('readiness validation', function () {
    test('fails when nullable required fields are missing', function (string $field) {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',

            'asset_id' => 'asset-123',
            'playback_id' => 'playback-123',
            'duration_seconds' => 120,
            $field => null,
        ]);
        Storage::put("lessons/{$lesson->id}/transcript.vtt", 'WEBVTT');

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    })->with([
        'missing learning_objectives' => 'learning_objectives',
    ]);

    test('fails when video host fields are missing', function (string $field) {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',

            'asset_id' => 'asset-123',
            'playback_id' => 'playback-123',
            'duration_seconds' => 120,
            $field => null,
        ]);
        Storage::put("lessons/{$lesson->id}/transcript.vtt", 'WEBVTT');

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    })->with([
        'missing asset_id' => 'asset_id',
        'missing playback_id' => 'playback_id',
        'missing duration_seconds' => 'duration_seconds',
    ]);

    test('fails when parsed transcript lines are missing', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'asset_id' => 'asset-123',
            'playback_id' => 'playback-123',
            'duration_seconds' => 120,
        ]);
        Storage::put("lessons/{$lesson->id}/transcript.vtt", 'WEBVTT');
        Storage::put("lessons/{$lesson->id}/transcript.txt", 'Hello world');

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    });

    test('fails when transcript files are missing', function (string $file) {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A tagline',
            'description' => 'A description',
            'learning_objectives' => 'Some objectives',
            'asset_id' => 'asset-123',
            'playback_id' => 'playback-123',
            'duration_seconds' => 120,
        ]);
        Storage::put("lessons/{$lesson->id}/{$file}", 'content');
        LessonTranscriptLine::factory()->for($lesson)->create();

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertSessionHasErrors('publish_date');
    })->with([
        'missing txt transcript' => 'transcript.vtt',
        'missing vtt transcript' => 'transcript.txt',
    ]);
});

describe('validation', function () {
    test('validates publish_date is a valid date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => 'not-a-date',
        ])->assertSessionHasErrors('publish_date');
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.publish-date', [$course, $lesson]), [
            'publish_date' => '2026-06-01',
        ])->assertNotFound();
    });
});
