<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to create lessons', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertRedirect();
    });

    test('does not allow moderators to create lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertForbidden();
    });

    test('does not allow regular users to create lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertForbidden();
    });
});

describe('store', function () {
    test('creates a new lesson and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Intro Lesson',
            'slug' => 'intro-lesson',
            'tagline' => 'Learn the basics',
            'description' => 'An introductory lesson.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertRedirect(
            route('staff.courses.lessons.edit', [$course, Lesson::query()->where('slug', 'intro-lesson')->firstOrFail()])
        );
    });

    test('creates lesson with course_id from route', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Course Lesson',
            'slug' => 'course-lesson',
            'tagline' => 'Linked to course',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'course-lesson')->firstOrFail();

        expect($lesson->course_id)->toBe($course->id);
    });

    test('auto-sets host to Mux when asset_id is present', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Video Lesson',
            'slug' => 'video-lesson',
            'tagline' => 'A video lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
            'asset_id' => 'mux-asset-123',
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'video-lesson')->firstOrFail();

        expect($lesson->host)->toBe(VideoHost::Mux)
            ->and($lesson->asset_id)->toBe('mux-asset-123');
    });

    test('creates lesson with all fields', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Full Lesson',
            'slug' => 'full-lesson',
            'tagline' => 'A full lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Learn everything.',
            'copy' => 'Some copy content.',
            'gated' => true,
            'visible' => true,
            'publish_date' => '2026-03-01',
            'asset_id' => 'mux-123',
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'full-lesson')->firstOrFail();

        expect($lesson->title)->toBe('Full Lesson')
            ->and($lesson->tagline)->toBe('A full lesson')
            ->and($lesson->description)->toBe('Description here.')
            ->and($lesson->learning_objectives)->toBe('Learn everything.')
            ->and($lesson->copy)->toBe('Some copy content.')
            ->and($lesson->gated)->toBeTrue()
            ->and($lesson->visible)->toBeTrue()
            ->and($lesson->publish_date->format('Y-m-d'))->toBe('2026-03-01')
            ->and($lesson->asset_id)->toBe('mux-123')
            ->and($lesson->course_id)->toBe($course->id);
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Thumb Lesson',
            'slug' => 'thumb-lesson',
            'tagline' => 'A thumbnail lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'thumb-lesson')->firstOrFail();

        expect($lesson->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("lesson/{$lesson->id}/thumbnail.{$lesson->thumbnail_extension}");
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Flash Lesson',
            'slug' => 'flash-lesson',
            'tagline' => 'A flash lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHas('flash.message', [
            'message' => 'Lesson created successfully.',
            'type' => 'success',
        ]);
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), $data)
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
        'missing learning_objectives' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'gated' => true, 'visible' => false, 'publish_date' => '2026-03-01'],
            ['learning_objectives'],
        ],
        'missing gated' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'visible' => false, 'publish_date' => '2026-03-01'],
            ['gated'],
        ],
        'missing visible' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'gated' => true, 'publish_date' => '2026-03-01'],
            ['visible'],
        ],
        'invalid slug format' => [
            ['title' => 'Test', 'slug' => 'Invalid Slug!', 'tagline' => 'Tagline', 'description' => 'Desc', 'learning_objectives' => 'Objectives', 'gated' => true, 'visible' => false, 'publish_date' => '2026-03-01'],
            ['slug'],
        ],
    ]);

    test('validates unique slug within course', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        Lesson::factory()->create(['slug' => 'existing-slug', 'course_id' => $course->id]);

        actingAs($admin);

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'existing-slug',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
        ])->assertSessionHasErrors(['slug']);
    });

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        post(route('staff.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'visible' => false,
            'publish_date' => '2026-03-01',
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });
});
