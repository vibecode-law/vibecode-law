<?php

use App\Enums\ExperienceLevel;
use App\Models\Course\Course;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update courses', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertRedirect();
    });

    test('does not allow moderators to update courses', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($moderator);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertForbidden();
    });

    test('does not allow regular users to update courses', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($user);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates course fields and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Updated objectives',
            'experience_level' => ExperienceLevel::Professional->value,
            'is_featured' => true,
        ])->assertRedirect(
            route('staff.academy.courses.edit', Course::query()->where('slug', 'updated-slug')->firstOrFail())
        );

        $course->refresh();

        expect($course->title)->toBe('Updated Title')
            ->and($course->slug)->toBe('updated-slug')
            ->and($course->tagline)->toBe('Updated tagline')
            ->and($course->description)->toBe('Updated description')
            ->and($course->learning_objectives)->toBe('Updated objectives')
            ->and($course->experience_level)->toBe(ExperienceLevel::Professional)
            ->and($course->is_featured)->toBeTrue();
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $course->refresh();

        expect($course->thumbnail_filename)->not->toBeNull();
        Storage::disk('public')->assertExists("course/{$course->id}/{$course->thumbnail_filename}");
    });

    test('handles thumbnail removal', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->withStockThumbnail()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $course->refresh();

        expect($course->thumbnail_filename)->toBeNull()
            ->and($course->thumbnail_crops)->toBeNull();
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertSessionHas('flash.message', [
            'message' => 'Course updated successfully.',
            'type' => 'success',
        ]);
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing title' => [
            ['slug' => 'test', 'is_featured' => false],
            ['title'],
        ],
        'missing slug' => [
            ['title' => 'Test', 'is_featured' => false],
            ['slug'],
        ],
        'invalid slug format' => [
            ['title' => 'Test', 'slug' => 'Invalid Slug!', 'is_featured' => false],
            ['slug'],
        ],
    ]);

    test('prevents slug change when course allows preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'allow_preview' => true]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'changed-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'is_featured' => false,
        ])->assertSessionHasErrors(['slug']);
    });

    test('prevents slug change when course has publish date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'allow_preview' => false, 'publish_date' => '2026-06-01']);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'changed-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'is_featured' => false,
        ])->assertSessionHasErrors(['slug']);
    });

    test('allows omitting slug when course allows preview', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'allow_preview' => true]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'is_featured' => false,
        ])->assertSessionDoesntHaveErrors(['slug']);

        expect($course->refresh()->slug)->toBe('original-slug');
    });

    test('allows slug change when course does not allow preview and has no publish date', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'new-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'is_featured' => false,
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('validates unique slug ignoring current course', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'my-course', 'allow_preview' => false, 'publish_date' => null]);
        Course::factory()->create(['slug' => 'other-course']);

        actingAs($admin);

        // Should allow keeping current slug
        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'my-course',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertSessionDoesntHaveErrors(['slug']);

        // Should reject existing other slug
        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'other-course',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertSessionHasErrors(['slug']);
    });

    test('requires publication fields when course allows preview', function ($field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => true]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'is_featured' => false,
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            $field => null,
        ])->assertSessionHasErrors([$field]);
    })->with([
        'tagline' => ['tagline'],
        'description' => ['description'],
        'learning_objectives' => ['learning_objectives'],
        'experience_level' => ['experience_level'],
    ]);

    test('requires publication fields when course has publish date', function ($field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => '2026-06-01']);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'is_featured' => false,
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            $field => null,
        ])->assertSessionHasErrors([$field]);
    })->with([
        'tagline' => ['tagline'],
        'description' => ['description'],
        'learning_objectives' => ['learning_objectives'],
        'experience_level' => ['experience_level'],
    ]);

    test('allows nullable publication fields when course is not published', function ($field) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => 'Test',
            'slug' => $course->slug,
            'is_featured' => false,
            $field => null,
        ])->assertSessionDoesntHaveErrors([$field]);
    })->with([
        'tagline' => ['tagline'],
        'description' => ['description'],
        'learning_objectives' => ['learning_objectives'],
        'experience_level' => ['experience_level'],
    ]);

    test('validates tags must be an array of valid tag ids', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
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
});

describe('tags', function () {
    test('syncs tags on update', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);
        $tags = Tag::factory()->count(3)->create();

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'tags' => $tags->pluck('id')->toArray(),
        ])->assertRedirect();

        $course->load('tags');

        expect($course->tags)->toHaveCount(3)
            ->and($course->tags->pluck('id')->sort()->values()->toArray())
            ->toBe($tags->pluck('id')->sort()->values()->toArray());
    });

    test('replaces existing tags on update', function () {
        $admin = User::factory()->admin()->create();
        $oldTags = Tag::factory()->count(2)->create();
        $newTags = Tag::factory()->count(2)->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);
        $course->tags()->attach($oldTags->pluck('id'));

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'tags' => $newTags->pluck('id')->toArray(),
        ])->assertRedirect();

        $course->load('tags');

        expect($course->tags)->toHaveCount(2)
            ->and($course->tags->pluck('id')->sort()->values()->toArray())
            ->toBe($newTags->pluck('id')->sort()->values()->toArray());
    });

    test('removes all tags when tags not sent', function () {
        $admin = User::factory()->admin()->create();
        $tags = Tag::factory()->count(2)->create();
        $course = Course::factory()->create(['allow_preview' => false, 'publish_date' => null]);
        $course->tags()->attach($tags->pluck('id'));

        actingAs($admin);

        patch(route('staff.academy.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertRedirect();

        $course->load('tags');

        expect($course->tags)->toHaveCount(0);
    });
});
