<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update lessons', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertRedirect();
    });

    test('does not allow moderators to update lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($moderator);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertForbidden();
    });

    test('does not allow regular users to update lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($user);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Lesson',
            'slug' => $lesson->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates lesson with only title and slug', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Title',
            'slug' => $lesson->slug,
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->title)->toBe('Updated Title');
    });

    test('updates lesson fields and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Updated objectives',
            'copy' => 'Updated copy',
            'gated' => false,
        ])->assertRedirect(
            route('staff.academy.courses.lessons.edit', [$course, Lesson::query()->where('slug', 'updated-slug')->firstOrFail()])
        );

        $lesson->refresh();

        expect($lesson->title)->toBe('Updated Title')
            ->and($lesson->slug)->toBe('updated-slug')
            ->and($lesson->tagline)->toBe('Updated tagline')
            ->and($lesson->description)->toBe('Updated description')
            ->and($lesson->learning_objectives)->toBe('Updated objectives')
            ->and($lesson->copy)->toBe('Updated copy')
            ->and($lesson->gated)->toBeFalse();
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->thumbnail_filename)->not->toBeNull();
        Storage::disk('public')->assertExists("lesson/{$lesson->id}/{$lesson->thumbnail_filename}");
    });

    test('handles thumbnail removal', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->withStockThumbnail()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->thumbnail_filename)->toBeNull()
            ->and($lesson->thumbnail_crops)->toBeNull();
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionHas('flash.message', [
            'message' => 'Lesson updated successfully.',
            'type' => 'success',
        ]);
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Updated',
            'slug' => $lesson->slug,
            'tagline' => 'Updated',
            'description' => 'Updated',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertNotFound();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing title' => [
            ['slug' => 'test'],
            ['title'],
        ],
        'missing slug' => [
            ['title' => 'Test'],
            ['slug'],
        ],
    ]);

    test('prevents slug change when lesson allows preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'allow_preview' => true]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'changed-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionHasErrors(['slug']);
    });

    test('prevents slug change when lesson has publish date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'allow_preview' => false, 'publish_date' => '2026-06-01']);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'changed-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionHasErrors(['slug']);
    });

    test('allows omitting slug when lesson allows preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'allow_preview' => true]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionDoesntHaveErrors(['slug']);

        expect($lesson->refresh()->slug)->toBe('original-slug');
    });

    test('allows slug change when lesson does not allow preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'original-slug', 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'new-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('validates unique slug ignoring current lesson', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'my-lesson', 'allow_preview' => false, 'publish_date' => null]);
        Lesson::factory()->create(['course_id' => $course->id, 'slug' => 'other-lesson']);

        actingAs($admin);

        // Should allow keeping current slug
        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'my-lesson',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionDoesntHaveErrors(['slug']);

        // Should reject existing other slug within same course
        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => 'other-lesson',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionHasErrors(['slug']);
    });

    test('requires publication fields when lesson allows preview', function ($field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => true]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'gated' => true,
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            $field => null,
        ])->assertSessionHasErrors([$field]);
    })->with([
        'tagline' => ['tagline'],
        'description' => ['description'],
        'learning_objectives' => ['learning_objectives'],
    ]);

    test('requires publication fields when lesson has publish date', function ($field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => '2026-06-01']);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'gated' => true,
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            $field => null,
        ])->assertSessionHasErrors([$field]);
    })->with([
        'tagline' => ['tagline'],
        'description' => ['description'],
        'learning_objectives' => ['learning_objectives'],
    ]);

    test('allows nullable publication fields when lesson is not published', function ($field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => 'Test',
            'slug' => $lesson->slug,
            'gated' => true,
            $field => null,
        ])->assertSessionDoesntHaveErrors([$field]);
    })->with([
        'tagline' => ['tagline'],
        'description' => ['description'],
        'learning_objectives' => ['learning_objectives'],
    ]);

    test('validates thumbnail_crops rejects invalid keys', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            ...$data,
        ])->assertSessionHasErrors($invalid);
    })->with([
        'square crop key rejected' => [
            ['thumbnail_crops' => ['square' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]]],
            ['thumbnail_crops'],
        ],
        'unknown crop key rejected' => [
            ['thumbnail_crops' => ['banner' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]]],
            ['thumbnail_crops'],
        ],
        'landscape crop with wrong aspect ratio rejected' => [
            ['thumbnail_crops' => ['landscape' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]]],
            ['thumbnail_crops'],
        ],
    ]);

    test('accepts valid landscape crop data', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'thumbnail_crops' => ['landscape' => ['x' => 0, 'y' => 0, 'width' => 1600, 'height' => 900]],
        ])->assertSessionDoesntHaveErrors(['thumbnail_crops']);
    });

    test('validates tags must be an array of valid tag ids', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            ...$data,
        ])->assertSessionHasErrors($invalid);
    })->with([
        'tags contains non-existent id' => [
            ['tags' => [99999]],
            ['tags.0'],
        ],
        'tags contains non-integer value' => [
            ['tags' => ['not-an-id']],
            ['tags.0'],
        ],
    ]);

    test('validates instructor_ids must be valid user ids', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            ...$data,
        ])->assertSessionHasErrors($invalid);
    })->with([
        'instructor_ids contains non-existent id' => [
            ['instructor_ids' => [99999]],
            ['instructor_ids.0'],
        ],
        'instructor_ids contains non-integer value' => [
            ['instructor_ids' => ['not-an-id']],
            ['instructor_ids.0'],
        ],
    ]);
});

describe('instructors', function () {
    test('syncs instructor_ids on update', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);
        $instructors = User::factory()->count(2)->create();

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'instructor_ids' => $instructors->pluck('id')->toArray(),
        ])->assertRedirect();

        $lesson->load('instructors');

        expect($lesson->instructors)->toHaveCount(2)
            ->and($lesson->instructors->pluck('id')->sort()->values()->toArray())
            ->toBe($instructors->pluck('id')->sort()->values()->toArray());
    });

    test('replaces existing instructors on update', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $oldInstructors = User::factory()->count(2)->create();
        $newInstructors = User::factory()->count(2)->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);
        $lesson->instructors()->attach($oldInstructors->pluck('id'));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'instructor_ids' => $newInstructors->pluck('id')->toArray(),
        ])->assertRedirect();

        $lesson->load('instructors');

        expect($lesson->instructors)->toHaveCount(2)
            ->and($lesson->instructors->pluck('id')->sort()->values()->toArray())
            ->toBe($newInstructors->pluck('id')->sort()->values()->toArray());
    });

    test('removes all instructors when instructor_ids not sent', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $instructors = User::factory()->count(2)->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);
        $lesson->instructors()->attach($instructors->pluck('id'));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertRedirect();

        $lesson->load('instructors');

        expect($lesson->instructors)->toHaveCount(0);
    });
});

describe('tags', function () {
    test('syncs tags on update', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);
        $tags = Tag::factory()->count(3)->create();

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'tags' => $tags->pluck('id')->toArray(),
        ])->assertRedirect();

        $lesson->load('tags');

        expect($lesson->tags)->toHaveCount(3)
            ->and($lesson->tags->pluck('id')->sort()->values()->toArray())
            ->toBe($tags->pluck('id')->sort()->values()->toArray());
    });

    test('replaces existing tags on update', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $oldTags = Tag::factory()->count(2)->create();
        $newTags = Tag::factory()->count(2)->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);
        $lesson->tags()->attach($oldTags->pluck('id'));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'tags' => $newTags->pluck('id')->toArray(),
        ])->assertRedirect();

        $lesson->load('tags');

        expect($lesson->tags)->toHaveCount(2)
            ->and($lesson->tags->pluck('id')->sort()->values()->toArray())
            ->toBe($newTags->pluck('id')->sort()->values()->toArray());
    });

    test('removes all tags when tags not sent', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id, 'allow_preview' => false, 'publish_date' => null]);
        $lesson->tags()->attach($tags->pluck('id'));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.update', [$course, $lesson]), [
            'title' => $lesson->title,
            'slug' => $lesson->slug,
            'tagline' => $lesson->tagline,
            'description' => $lesson->description,
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertRedirect();

        $lesson->load('tags');

        expect($lesson->tags)->toHaveCount(0);
    });
});
