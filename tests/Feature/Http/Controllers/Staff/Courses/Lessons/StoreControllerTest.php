<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();

        post(route('staff.academy.courses.lessons.store', $course), [
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

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertRedirect();
    });

    test('does not allow moderators to create lessons', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();

        actingAs($moderator);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertForbidden();
    });

    test('does not allow regular users to create lessons', function () {
        /** @var User */
        $user = User::factory()->create();
        $course = Course::factory()->create();

        actingAs($user);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test Lesson',
            'slug' => 'test-lesson',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertForbidden();
    });
});

describe('store', function () {
    test('creates a lesson with only title and slug', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Minimal Lesson',
            'slug' => 'minimal-lesson',
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'minimal-lesson')->firstOrFail();

        expect($lesson->title)->toBe('Minimal Lesson')
            ->and($lesson->slug)->toBe('minimal-lesson')
            ->and($lesson->tagline)->toBeNull()
            ->and($lesson->description)->toBeNull()
            ->and($lesson->learning_objectives)->toBeNull()
            ->and($lesson->gated)->toBeTrue();
    });

    test('creates a new lesson and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Intro Lesson',
            'slug' => 'intro-lesson',
            'tagline' => 'Learn the basics',
            'description' => 'An introductory lesson.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertRedirect(
            route('staff.academy.courses.lessons.edit', [$course, Lesson::query()->where('slug', 'intro-lesson')->firstOrFail()])
        );
    });

    test('creates lesson with course_id from route', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Course Lesson',
            'slug' => 'course-lesson',
            'tagline' => 'Linked to course',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'course-lesson')->firstOrFail();

        expect($lesson->course_id)->toBe($course->id);
    });

    test('auto-sets host to Mux when asset_id is present', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Video Lesson',
            'slug' => 'video-lesson',
            'tagline' => 'A video lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
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

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Full Lesson',
            'slug' => 'full-lesson',
            'tagline' => 'A full lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Learn everything.',
            'copy' => 'Some copy content.',
            'gated' => true,
            'asset_id' => 'mux-123',
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'full-lesson')->firstOrFail();

        expect($lesson->title)->toBe('Full Lesson')
            ->and($lesson->tagline)->toBe('A full lesson')
            ->and($lesson->description)->toBe('Description here.')
            ->and($lesson->learning_objectives)->toBe('Learn everything.')
            ->and($lesson->copy)->toBe('Some copy content.')
            ->and($lesson->gated)->toBeTrue()
            ->and($lesson->asset_id)->toBe('mux-123')
            ->and($lesson->course_id)->toBe($course->id);
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Thumb Lesson',
            'slug' => 'thumb-lesson',
            'tagline' => 'A thumbnail lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'thumb-lesson')->firstOrFail();

        expect($lesson->thumbnail_filename)->not->toBeNull();
        Storage::disk('public')->assertExists("lesson/{$lesson->id}/{$lesson->thumbnail_filename}");
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Flash Lesson',
            'slug' => 'flash-lesson',
            'tagline' => 'A flash lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
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

        post(route('staff.academy.courses.lessons.store', $course), $data)
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
        'invalid slug format' => [
            ['title' => 'Test', 'slug' => 'Invalid Slug!'],
            ['slug'],
        ],
    ]);

    test('validates unique slug within course', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        Lesson::factory()->create(['slug' => 'existing-slug', 'course_id' => $course->id]);

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'existing-slug',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
        ])->assertSessionHasErrors(['slug']);
    });

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('validates instructor_ids must be valid user ids', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
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

    test('validates thumbnail_crops rejects invalid keys', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
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

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'learning_objectives' => 'Objectives',
            'gated' => true,
            'thumbnail_crops' => ['landscape' => ['x' => 0, 'y' => 0, 'width' => 1600, 'height' => 900]],
        ])->assertSessionDoesntHaveErrors(['thumbnail_crops']);
    });

    test('validates tags must be an array of valid tag ids', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
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
});

describe('instructors', function () {
    test('creates lesson with instructor_ids', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $instructors = User::factory()->count(2)->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Instructor Lesson',
            'slug' => 'instructor-lesson',
            'tagline' => 'A lesson with instructors',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'instructor_ids' => $instructors->pluck('id')->toArray(),
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'instructor-lesson')->firstOrFail();
        $lesson->load('instructors');

        expect($lesson->instructors)->toHaveCount(2)
            ->and($lesson->instructors->pluck('id')->sort()->values()->toArray())
            ->toBe($instructors->pluck('id')->sort()->values()->toArray());
    });

    test('creates lesson without instructor_ids', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'No Instructor Lesson',
            'slug' => 'no-instructor-lesson',
            'tagline' => 'No instructors',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'no-instructor-lesson')->firstOrFail();
        $lesson->load('instructors');

        expect($lesson->instructors)->toHaveCount(0);
    });
});

describe('tags', function () {
    test('creates lesson with tags', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'Tagged Lesson',
            'slug' => 'tagged-lesson',
            'tagline' => 'A tagged lesson',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
            'tags' => $tags->pluck('id')->toArray(),
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'tagged-lesson')->firstOrFail();
        $lesson->load('tags');

        expect($lesson->tags)->toHaveCount(3)
            ->and($lesson->tags->pluck('id')->sort()->values()->toArray())
            ->toBe($tags->pluck('id')->sort()->values()->toArray());
    });

    test('creates lesson without tags', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();

        actingAs($admin);

        post(route('staff.academy.courses.lessons.store', $course), [
            'title' => 'No Tags Lesson',
            'slug' => 'no-tags-lesson',
            'tagline' => 'No tags',
            'description' => 'Description here.',
            'learning_objectives' => 'Test objectives',
            'gated' => true,
        ])->assertRedirect();

        $lesson = Lesson::query()->where('slug', 'no-tags-lesson')->firstOrFail();
        $lesson->load('tags');

        expect($lesson->tags)->toHaveCount(0);
    });
});
