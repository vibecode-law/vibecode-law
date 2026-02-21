<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertRedirect(route('login'));
    });

    test('does not allow regular users', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertForbidden();
    });

    test('does not allow moderators', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertForbidden();
    });
});

describe('generate', function () {
    test('generates content and updates lesson', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $backendTag = Tag::factory()->create(['name' => 'Backend', 'slug' => 'backend']);
        $frontendTag = Tag::factory()->create(['name' => 'Frontend', 'slug' => 'frontend']);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'tagline' => 'Old tagline',
            'description' => 'Old description',
            'learning_objectives' => 'Old objectives',
            'copy' => 'Old copy',
        ]);

        Storage::put("lessons/{$lesson->id}/transcript.txt", 'A transcript about building legaltech apps.');

        Prism::fake([
            StructuredResponseFake::make()
                ->withStructured([
                    'tagline' => 'New AI tagline',
                    'description' => 'New AI description.',
                    'learning_objectives' => '- Objective one',
                    'copy' => 'New AI copy content.',
                    'suggested_tag_ids' => [$backendTag->id],
                ]),
        ]);

        actingAs($admin);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionHas('flash.message', [
                'message' => 'Lesson content generated successfully.',
                'type' => 'success',
            ]);

        $lesson->refresh();

        expect($lesson->tagline)->toBe('New AI tagline')
            ->and($lesson->description)->toBe('New AI description.')
            ->and($lesson->learning_objectives)->toBe('- Objective one')
            ->and($lesson->copy)->toBe('New AI copy content.')
            ->and($lesson->tags->pluck('id')->all())->toBe([$backendTag->id]);
    });

    test('returns validation error when lesson has no transcript', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertSessionHasErrors('transcript');
    });

    test('returns error when AI generation fails', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'tagline' => 'Original tagline',
        ]);

        Storage::put("lessons/{$lesson->id}/transcript.txt", 'A transcript.');

        Prism::fake([
            StructuredResponseFake::make()
                ->withStructured([]),
        ]);

        actingAs($admin);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertRedirect()
            ->assertSessionHasErrors('copywriter');

        $lesson->refresh();

        expect($lesson->tagline)->toBe('Original tagline');
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        post(route('staff.academy.courses.lessons.generate-copywriter', [$course, $lesson]))
            ->assertNotFound();
    });
});
