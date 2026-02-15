<?php

use App\Enums\ExperienceLevel;
use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        patch(route('staff.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update courses', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertRedirect();
    });

    test('does not allow moderators to update courses', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        patch(route('staff.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertForbidden();
    });

    test('does not allow regular users to update courses', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        patch(route('staff.courses.update', $course), [
            'title' => 'Updated Course',
            'slug' => $course->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates course fields and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['visible' => false]);

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'learning_objectives' => 'Updated objectives',
            'experience_level' => ExperienceLevel::Professional->value,
            'visible' => true,
            'is_featured' => true,
            'publish_date' => '2026-03-01',
        ])->assertRedirect(
            route('staff.courses.edit', Course::query()->where('slug', 'updated-slug')->firstOrFail())
        );

        $course->refresh();

        expect($course->title)->toBe('Updated Title')
            ->and($course->slug)->toBe('updated-slug')
            ->and($course->tagline)->toBe('Updated tagline')
            ->and($course->description)->toBe('Updated description')
            ->and($course->learning_objectives)->toBe('Updated objectives')
            ->and($course->experience_level)->toBe(ExperienceLevel::Professional)
            ->and($course->visible)->toBeTrue()
            ->and($course->is_featured)->toBeTrue();
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        patch(route('staff.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $course->refresh();

        expect($course->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("course/{$course->id}/thumbnail.{$course->thumbnail_extension}");
    });

    test('handles thumbnail removal', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->withStockThumbnail()->create();

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $course->refresh();

        expect($course->thumbnail_extension)->toBeNull()
            ->and($course->thumbnail_crops)->toBeNull();
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => $course->title,
            'slug' => $course->slug,
            'tagline' => $course->tagline,
            'description' => $course->description,
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHas('flash.message', [
            'message' => 'Course updated successfully.',
            'type' => 'success',
        ]);
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        patch(route('staff.courses.update', $course), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing title' => [
            ['slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'experience_level' => ExperienceLevel::Beginner->value, 'visible' => false, 'is_featured' => false, 'publish_date' => '2026-03-01'],
            ['title'],
        ],
        'missing slug' => [
            ['title' => 'Test', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'experience_level' => ExperienceLevel::Beginner->value, 'visible' => false, 'is_featured' => false, 'publish_date' => '2026-03-01'],
            ['slug'],
        ],
        'missing tagline' => [
            ['title' => 'Test', 'slug' => 'test', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'experience_level' => ExperienceLevel::Beginner->value, 'visible' => false, 'is_featured' => false, 'publish_date' => '2026-03-01'],
            ['tagline'],
        ],
        'missing description' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'learning_objectives' => 'Objectives', 'experience_level' => ExperienceLevel::Beginner->value, 'visible' => false, 'is_featured' => false, 'publish_date' => '2026-03-01'],
            ['description'],
        ],
        'invalid slug format' => [
            ['title' => 'Test', 'slug' => 'Invalid Slug!', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'experience_level' => ExperienceLevel::Beginner->value, 'visible' => false, 'is_featured' => false, 'publish_date' => '2026-03-01'],
            ['slug'],
        ],
    ]);

    test('prevents slug change when course is visible', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'visible' => true]);

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'changed-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => true,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHasErrors(['slug']);
    });

    test('allows keeping same slug when course is visible', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'visible' => true]);

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'original-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => true,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('allows slug change when course is not visible', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'original-slug', 'visible' => false]);

        actingAs($admin);

        patch(route('staff.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'new-slug',
            'tagline' => 'Tagline',
            'description' => 'Desc',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('validates unique slug ignoring current course', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create(['slug' => 'my-course']);
        Course::factory()->create(['slug' => 'other-course']);

        actingAs($admin);

        // Should allow keeping current slug
        patch(route('staff.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'my-course',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionDoesntHaveErrors(['slug']);

        // Should reject existing other slug
        patch(route('staff.courses.update', $course), [
            'title' => 'Test',
            'slug' => 'other-course',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'visible' => false,
            'is_featured' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHasErrors(['slug']);
    });
});
