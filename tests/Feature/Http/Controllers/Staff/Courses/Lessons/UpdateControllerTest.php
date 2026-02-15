<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update lessons', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertRedirect();
    });

    test('does not allow moderators to update lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertForbidden();
    });

    test('does not allow regular users to update lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates lesson fields and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'visible' => false]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Updated objectives',
            'copy' => 'Updated copy',
            'gated' => false,
            'visible' => true,
            'publish_date' => '2026-04-01',
        ])->assertRedirect(
            route('staff.courses.lessons.edit', [$course, Lesson::query()->where('slug', 'updated-slug')->firstOrFail()])
        );

        $lesson->refresh();

        expect($lesson->title)->toBe('Updated Title')
            ->and($lesson->slug)->toBe('updated-slug')
            ->and($lesson->tagline)->toBe('Updated tagline')
            ->and($lesson->description)->toBe('Updated description')
            ->and($lesson->learning_objectives)->toBe('Updated objectives')
            ->and($lesson->copy)->toBe('Updated copy')
            ->and($lesson->gated)->toBeFalse()
            ->and($lesson->visible)->toBeTrue()
            ->and($lesson->publish_date->format('Y-m-d'))->toBe('2026-04-01');
    });

    test('auto-sets host to Mux when asset_id is provided', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
            'asset_id' => 'new-mux-asset',
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->host)->toBe(VideoHost::Mux)
            ->and($lesson->asset_id)->toBe('new-mux-asset');
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("lesson/{$lesson->id}/thumbnail.{$lesson->thumbnail_extension}");
    });

    test('handles thumbnail removal', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->withStockThumbnail()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->thumbnail_extension)->toBeNull()
            ->and($lesson->thumbnail_crops)->toBeNull();
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHas('flash.message', [
            'message' => 'Lesson updated successfully.',
            'type' => 'success',
        ]);
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated',
            'slug' => $lesson->slug,
            'tagline' => 'Updated',
            'description' => 'Updated',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertNotFound();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing title' => [
            ['slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'gated' => true, 'visible' => false, 'publish_date' => '2026-03-01'],
            ['title'],
        ],
        'missing slug' => [
            ['title' => 'Test', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'gated' => true, 'visible' => false, 'publish_date' => '2026-03-01'],
            ['slug'],
        ],
        'missing tagline' => [
            ['title' => 'Test', 'slug' => 'test', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'gated' => true, 'visible' => false, 'publish_date' => '2026-03-01'],
            ['tagline'],
        ],
        'missing description' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'learning_objectives' => 'Objectives', 'gated' => true, 'visible' => false, 'publish_date' => '2026-03-01'],
            ['description'],
        ],
    ]);

    test('prevents slug change when lesson is visible', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'visible' => true]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'changed-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => true,
            'publish_date' => '2026-03-01',
        ])->assertSessionHasErrors(['slug']);
    });

    test('allows keeping same slug when lesson is visible', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'visible' => true]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'original-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => true,
            'publish_date' => '2026-03-01',
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('allows slug change when lesson is not visible', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'visible' => false]);

        actingAs($admin);

        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'new-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('validates unique slug ignoring current lesson', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'my-lesson']);
        Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'other-lesson']);

        actingAs($admin);

        // Should allow keeping current slug
        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'my-lesson',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionDoesntHaveErrors(['slug']);

        // Should reject existing other slug within same course
        patch(route('staff.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'other-lesson',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHasErrors(['slug']);
    });
});
