<?php

use App\Actions\Showcase\SubmitShowcaseAction;
use App\Enums\ShowcaseStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $practiceArea = PracticeArea::factory()->create();

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect(route('login'));
    });

    test('allows authenticated user to create showcase', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect();
    });

    test('requires email verification', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->unverified()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect(route('verification.notice'));
    });

    test('prevents blocked user from creating showcase', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->blockedFromSubmissions()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertForbidden();
    });
});

describe('validation', function () {
    test('validates showcase data', function (array $data, array $invalidFields) {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $baseData = [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ];

        $response = post(route('showcase.manage.store'), array_merge($baseData, $data));

        $response->assertInvalid($invalidFields);
    })->with([
        'practice_area_ids is required' => [
            ['practice_area_ids' => null],
            ['practice_area_ids'],
        ],
        'practice_area_ids must be an array' => [
            ['practice_area_ids' => 'not-an-array'],
            ['practice_area_ids'],
        ],
        'practice_area_ids must have at least one item' => [
            ['practice_area_ids' => []],
            ['practice_area_ids'],
        ],
        'practice_area_ids items must exist' => [
            ['practice_area_ids' => [99999]],
            ['practice_area_ids.0'],
        ],
        'title is required' => [
            ['title' => null],
            ['title'],
        ],
        'tagline is required' => [
            ['tagline' => null],
            ['tagline'],
        ],
        'tagline cannot exceed 255 characters' => [
            ['tagline' => str_repeat('a', 256)],
            ['tagline'],
        ],
        'description is required' => [
            ['description' => null],
            ['description'],
        ],
        'key_features is required' => [
            ['key_features' => null],
            ['key_features'],
        ],
        'url must be valid format' => [
            ['url' => 'not-a-valid-url'],
            ['url'],
        ],
        'title cannot exceed 60 characters' => [
            ['title' => str_repeat('a', 61)],
            ['title'],
        ],
        'description cannot exceed 5000 characters' => [
            ['description' => str_repeat('a', 5001)],
            ['description'],
        ],
        'key_features cannot exceed 5000 characters' => [
            ['key_features' => str_repeat('a', 5001)],
            ['key_features'],
        ],
        'help_needed cannot exceed 5000 characters' => [
            ['help_needed' => str_repeat('a', 5001)],
            ['help_needed'],
        ],
        'thumbnail must be an image' => [
            ['thumbnail' => UploadedFile::fake()->create('document.pdf', 100)],
            ['thumbnail'],
        ],
        'thumbnail must meet minimum dimensions' => [
            ['thumbnail' => UploadedFile::fake()->image('small.jpg', 200, 200)],
            ['thumbnail'],
        ],
        'thumbnail_crop is required when thumbnail is provided' => [
            [
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => null,
            ],
            ['thumbnail_crop'],
        ],
        'thumbnail_crop.x must be an integer' => [
            [
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => ['x' => 'not-an-int', 'y' => 0, 'width' => 100, 'height' => 100],
            ],
            ['thumbnail_crop.x'],
        ],
        'thumbnail_crop.y must be non-negative' => [
            [
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => ['x' => 0, 'y' => -1, 'width' => 100, 'height' => 100],
            ],
            ['thumbnail_crop.y'],
        ],
        'thumbnail_crop.width must be at least 1' => [
            [
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 100],
            ],
            ['thumbnail_crop.width'],
        ],
        'thumbnail_crop.height must be at least 1' => [
            [
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 0],
            ],
            ['thumbnail_crop.height'],
        ],
        'images must be valid image files' => [
            ['images' => [UploadedFile::fake()->create('document.pdf', 100)]],
            ['images.0'],
        ],
        'images cannot exceed 2MB' => [
            ['images' => [UploadedFile::fake()->image('large.jpg', 1280, 720)->size(2049)]],
            ['images.0'],
        ],
        'images must meet minimum dimensions' => [
            ['images' => [UploadedFile::fake()->image('small.jpg', 640, 480)]],
            ['images.0'],
        ],
        'cannot upload more than 10 images' => [
            ['images' => array_map(fn ($i) => UploadedFile::fake()->image("image{$i}.jpg", 1280, 720), range(0, 10))],
            ['images'],
        ],
        'video_url must be valid format' => [
            ['video_url' => 'not-a-valid-url'],
            ['video_url'],
        ],
        'source_url must be valid format' => [
            ['source_status' => SourceStatus::OpenSource->value, 'source_url' => 'not-a-valid-url'],
            ['source_url'],
        ],
        'source_url is required when source_status is SourceAvailable' => [
            ['source_status' => SourceStatus::SourceAvailable->value, 'source_url' => null],
            ['source_url'],
        ],
        'source_url is required when source_status is OpenSource' => [
            ['source_status' => SourceStatus::OpenSource->value, 'source_url' => null],
            ['source_url'],
        ],
        'at least one image is required' => [
            ['images' => []],
            ['images'],
        ],
        'at least one image is required when images is null' => [
            ['images' => null],
            ['images'],
        ],
        'slug is prohibited on creation' => [
            ['slug' => 'my-custom-slug'],
            ['slug'],
        ],
    ]);
});

describe('showcase creation', function () {
    test('creates showcase with minimal required data', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect();

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        assertDatabaseHas('showcases', [
            'user_id' => $user->id,
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
        ]);

        expect($showcase->slug)->toMatch('/^my-awesome-project-\d{6}$/');

        assertDatabaseHas('practice_area_showcase', [
            'showcase_id' => $showcase->id,
            'practice_area_id' => $practiceArea->id,
        ]);
    });

    test('creates showcase with all optional data', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => '## Key Features\n- Feature 1\n- Feature 2',
            'help_needed' => 'Looking for contributors to help with documentation.',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect();

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        assertDatabaseHas('showcases', [
            'user_id' => $user->id,
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => '## Key Features\n- Feature 1\n- Feature 2',
            'help_needed' => 'Looking for contributors to help with documentation.',
            'url' => 'https://example.com',
        ]);

        expect($showcase->slug)->toMatch('/^my-awesome-project-\d{6}$/');
    });

    test('creates showcase with help_needed as null', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'help_needed' => null,
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect();

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        assertDatabaseHas('showcases', [
            'id' => $showcase->id,
            'key_features' => 'Some key features',
            'help_needed' => null,
        ]);
    });

    test('auto-generates slug from title and truncates long titles', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        // Title that would exceed 60 chars when slugified
        $longTitle = 'This Is A Really Long Title That Exceeds The Maximum Length';

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => $longTitle,
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', $longTitle)->first();

        // Slug should be truncated to 60 chars + 7 for suffix = 67 max
        expect(strlen($showcase->slug))->toBeLessThanOrEqual(67);
        expect($showcase->slug)->toMatch('/^this-is-a-really-long-title-that-exceeds-the-maximum-length?-\d{6}$/');
    });

    test('showcase defaults to draft status', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        expect($showcase->status)->toBe(ShowcaseStatus::Draft);
    });

    test('showcase belongs to authenticated user', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        expect($showcase->user_id)->toBe($user->id);
        expect($showcase->user->id)->toBe($user->id);
    });

    test('showcase belongs to practice areas', function () {
        $practiceArea1 = PracticeArea::factory()->create();
        $practiceArea2 = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea1->id, $practiceArea2->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        expect($showcase->practiceAreas)->toHaveCount(2);
        expect($showcase->practiceAreas->pluck('id')->toArray())->toContain($practiceArea1->id, $practiceArea2->id);
    });

    test('creates showcase with video_url and open source info', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'video_url' => 'https://youtube.com/watch?v=example',
            'source_status' => SourceStatus::OpenSource->value,
            'source_url' => 'https://github.com/user/repo',
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        assertDatabaseHas('showcases', [
            'title' => 'My Awesome Project',
            'video_url' => 'https://youtube.com/watch?v=example',
            'source_status' => SourceStatus::OpenSource->value,
            'source_url' => 'https://github.com/user/repo',
        ]);
    });

    test('creates showcase with source_status NotAvailable defaults', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        assertDatabaseHas('showcases', [
            'title' => 'My Awesome Project',
            'source_status' => SourceStatus::NotAvailable->value,
            'source_url' => null,
        ]);
    });

    test('source_url is cleared when source_status is NotAvailable', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'source_url' => 'https://github.com/user/repo',
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        expect($showcase->source_url)->toBeNull();
    });

});

describe('image uploads', function () {
    test('creates showcase with single image', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $image = UploadedFile::fake()->image('screenshot.jpg', 1280, 720);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$image],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect();

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        expect($showcase->images)->toHaveCount(1);
    });

    test('creates showcase with multiple images', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $images = [
            UploadedFile::fake()->image('screenshot1.jpg', 1280, 720),
            UploadedFile::fake()->image('screenshot2.jpg', 1280, 720),
            UploadedFile::fake()->image('screenshot3.jpg', 1280, 720),
        ];

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => $images,
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertRedirect();

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        expect($showcase->images)->toHaveCount(3);
    });

    test('images are stored in correct directory', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $image = UploadedFile::fake()->image('screenshot.jpg', 1280, 720);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$image],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        $storedImage = $showcase->images->first();

        expect($storedImage->path)->toStartWith("showcase/{$showcase->id}/images/");
        Storage::disk('public')->assertExists($storedImage->path);
    });

    test('images are ordered correctly', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $images = [
            UploadedFile::fake()->image('first.jpg', 1280, 720),
            UploadedFile::fake()->image('second.jpg', 1280, 720),
            UploadedFile::fake()->image('third.jpg', 1280, 720),
        ];

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => $images,
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        $orderedImages = $showcase->images()->orderBy('order')->get();

        expect($orderedImages[0]->order)->toBe(1);
        expect($orderedImages[1]->order)->toBe(2);
        expect($orderedImages[2]->order)->toBe(3);
    });

    test('stores thumbnail correctly', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $thumbnail = UploadedFile::fake()->image('my-thumbnail.jpg', 500, 500);
        $image = UploadedFile::fake()->image('screenshot.jpg', 1280, 720);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'thumbnail' => $thumbnail,
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            'images' => [$image],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        expect($showcase->thumbnail_extension)->toBe('jpg');
        expect($showcase->thumbnail_crop)->toBe(['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500]);
        Storage::disk('public')->assertExists("showcase/{$showcase->id}/thumbnail.jpg");
    });

    test('image filename is preserved', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $image = UploadedFile::fake()->image('my-screenshot.jpg', 1280, 720);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$image],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();
        $storedImage = $showcase->images->first();

        expect($storedImage->filename)->toBe('my-screenshot.jpg');
    });
});

describe('response', function () {
    test('redirects to showcase show page', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        $response->assertRedirect(route('showcase.manage.edit', $showcase));
    });

    test('includes success message in session', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Showcase created successfully.', 'type' => 'success']);
    });
});

describe('submit on create', function () {
    test('creates and submits showcase when submit flag is true', function () {
        Notification::fake();

        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            'submit' => true,
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        expect($showcase->status)->toBe(ShowcaseStatus::Pending);
        expect($showcase->submitted_date)->not->toBeNull();

        $response->assertRedirect(route('user-area.showcases.index'));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase created and submitted for approval.', 'type' => 'success']);
    });

    test('creates showcase as draft when submit flag is false', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            'submit' => false,
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        expect($showcase->status)->toBe(ShowcaseStatus::Draft);
        expect($showcase->submitted_date)->toBeNull();

        $response->assertRedirect(route('showcase.manage.edit', $showcase));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase created successfully.', 'type' => 'success']);
    });

    test('creates showcase as draft when submit flag is not provided', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
        ]);

        $showcase = Showcase::where('title', 'My Awesome Project')->first();

        expect($showcase->status)->toBe(ShowcaseStatus::Draft);
        expect($showcase->submitted_date)->toBeNull();
    });

    test('blocked user cannot create and submit showcase', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->blockedFromSubmissions()->create();

        actingAs($user);

        $response = post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'Test Showcase',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'key_features' => 'Test key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            'submit' => true,
        ]);

        $response->assertForbidden();
    });

    test('calls SubmitShowcaseAction when submitting on create', function () {
        $practiceArea = PracticeArea::factory()->create();

        /** @var User */
        $user = User::factory()->create();

        $mock = Mockery::mock(SubmitShowcaseAction::class);
        $mock->shouldReceive('submit')->once();
        app()->instance(SubmitShowcaseAction::class, $mock);

        actingAs($user);

        post(route('showcase.manage.store'), [
            'practice_area_ids' => [$practiceArea->id],
            'title' => 'My Awesome Project',
            'tagline' => 'A great project tagline',
            'description' => 'This is a great project description',
            'key_features' => 'Some key features',
            'url' => 'https://example.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [UploadedFile::fake()->image('test.jpg', 1280, 720)],
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 500, 500),
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            'submit' => true,
        ]);
    });
});
