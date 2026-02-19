<?php

use App\Enums\ExperienceLevel;
use App\Models\Course\Course;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        post(route('staff.academy.courses.store'), [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to create courses', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertRedirect();
    });

    test('does not allow moderators to create courses', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'is_featured' => false,
        ])->assertForbidden();
    });

    test('does not allow regular users to create courses', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test Course',
            'slug' => 'test-course',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'is_featured' => false,
        ])->assertForbidden();
    });
});

describe('store', function () {
    test('creates a new course and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'AI Legal Course',
            'slug' => 'ai-legal-course',
            'tagline' => 'Learn about legal AI',
            'description' => 'A comprehensive course.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertRedirect(
            route('staff.academy.courses.edit', Course::query()->where('slug', 'ai-legal-course')->firstOrFail())
        );
    });

    test('creates course with all fields', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Full Course',
            'slug' => 'full-course',
            'tagline' => 'A full course',
            'description' => 'Description here.',
            'learning_objectives' => 'Learn everything.',
            'experience_level' => ExperienceLevel::Intermediate->value,
            'is_featured' => true,
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'full-course')->firstOrFail();

        expect($course->title)->toBe('Full Course')
            ->and($course->tagline)->toBe('A full course')
            ->and($course->description)->toBe('Description here.')
            ->and($course->learning_objectives)->toBe('Learn everything.')
            ->and($course->experience_level)->toBe(ExperienceLevel::Intermediate)
            ->and($course->allow_preview)->toBeFalse()
            ->and($course->is_featured)->toBeTrue()
            ->and($course->publish_date)->toBeNull();
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        post(route('staff.academy.courses.store'), [
            'title' => 'Thumb Course',
            'slug' => 'thumb-course',
            'tagline' => 'A thumbnail course',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'thumb-course')->firstOrFail();

        expect($course->thumbnail_filename)->not->toBeNull();
        Storage::disk('public')->assertExists("course/{$course->id}/{$course->thumbnail_filename}");
    });

    test('handles thumbnail upload with crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 800, height: 600);

        post(route('staff.academy.courses.store'), [
            'title' => 'Crop Course',
            'slug' => 'crop-course',
            'tagline' => 'A crop course',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'thumbnail' => $thumbnail,
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
            ],
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'crop-course')->firstOrFail();

        expect($course->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Flash Course',
            'slug' => 'flash-course',
            'tagline' => 'A flash course',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertSessionHas('flash.message', [
            'message' => 'Course created successfully.',
            'type' => 'success',
        ]);
    });

    test('creates course with is_featured set to false', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Hidden Course',
            'slug' => 'hidden-course',
            'tagline' => 'Hidden',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'hidden-course')->firstOrFail();

        expect($course->allow_preview)->toBeFalse()
            ->and($course->is_featured)->toBeFalse();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing title' => [
            ['slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'is_featured' => false],
            ['title'],
        ],
        'missing slug' => [
            ['title' => 'Test', 'tagline' => 'Tagline', 'description' => 'Desc', 'is_featured' => false],
            ['slug'],
        ],
        'missing is_featured' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'is_featured' => null],
            ['is_featured'],
        ],
        'invalid slug format' => [
            ['title' => 'Test', 'slug' => 'Invalid Slug!', 'tagline' => 'Tagline', 'description' => 'Desc', 'is_featured' => false],
            ['slug'],
        ],
        'title too long' => [
            ['title' => str_repeat('a', 256), 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'is_featured' => false],
            ['title'],
        ],
    ]);

    test('validates unique slug', function () {
        $admin = User::factory()->admin()->create();
        Course::factory()->create(['slug' => 'existing-slug']);

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test',
            'slug' => 'existing-slug',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertSessionHasErrors(['slug']);
    });

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        post(route('staff.academy.courses.store'), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('validates thumbnail max size', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->image(name: 'large.jpg')->size(kilobytes: 3000);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('rejects invalid crop keys', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'portrait' => ['x' => 0, 'y' => 0, 'width' => 300, 'height' => 500],
            ],
        ])->assertSessionHasErrors(['thumbnail_crops']);
    });

    test('validates tags must be an array of valid tag ids', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
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
    test('creates course with tags', function () {
        $admin = User::factory()->admin()->create();
        $tags = Tag::factory()->count(3)->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'Tagged Course',
            'slug' => 'tagged-course',
            'tagline' => 'A tagged course',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
            'tags' => $tags->pluck('id')->toArray(),
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'tagged-course')->firstOrFail();
        $course->load('tags');

        expect($course->tags)->toHaveCount(3)
            ->and($course->tags->pluck('id')->sort()->values()->toArray())
            ->toBe($tags->pluck('id')->sort()->values()->toArray());
    });

    test('creates course without tags', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.academy.courses.store'), [
            'title' => 'No Tags Course',
            'slug' => 'no-tags-course',
            'tagline' => 'No tags',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'experience_level' => ExperienceLevel::Beginner->value,
            'is_featured' => false,
        ])->assertRedirect();

        $course = Course::query()->where('slug', 'no-tags-course')->firstOrFail();
        $course->load('tags');

        expect($course->tags)->toHaveCount(0);
    });
});
