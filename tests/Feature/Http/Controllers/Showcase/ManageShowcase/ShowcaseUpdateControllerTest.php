<?php

use App\Actions\Showcase\SubmitShowcaseAction;
use App\Enums\ShowcaseStatus;
use App\Enums\SourceStatus;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use App\Services\Markdown\MarkdownService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\put;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->create();

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect(route('login'));
    });

    test('allows owner to update their showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect();
    });

    test('requires email verification', function () {
        /** @var User */
        $user = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect(route('verification.notice'));
    });

    test('prevents blocked user from creating showcase', function () {
        /** @var User */
        $user = User::factory()->blockedFromSubmissions()->create();

        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertSessionHasNoErrors()->assertForbidden();
    });

    test('allows admin to update any showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($otherUser, 'user')->create();

        actingAs($admin);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect();
    });

    test('prevents non-owner from updating showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($otherUser, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertForbidden();
    });

    test('prevents owner from updating pending showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->pending()->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertForbidden();
    });

    test('prevents owner from updating approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->approved()->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertForbidden();
    });

    test('allows admin to update pending showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->pending()->for($user, 'user')->create();

        actingAs($admin);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect();
    });

    test('allows admin to update approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->approved()->for($user, 'user')->create();

        actingAs($admin);

        // Slug is prohibited for approved showcases
        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect();
    });
});

describe('validation', function () {
    test('validates showcase data', function (array $data, array $invalidFields) {
        if (isset($data['_setup'])) {
            $data['_setup']();
            unset($data['_setup']);
        }

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($user);

        $baseData = [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ];

        $response = put(route('showcase.manage.update', $showcase), array_merge($baseData, $data));

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
        'images must be valid image files' => [
            [
                '_setup' => fn () => Storage::fake('public'),
                'images' => [UploadedFile::fake()->create('document.pdf', 100)],
            ],
            ['images.0'],
        ],
        'images cannot exceed 4MB' => [
            [
                '_setup' => fn () => Storage::fake('public'),
                'images' => [UploadedFile::fake()->image('large.jpg', 1280, 720)->size(4097)],
            ],
            ['images.0'],
        ],
        'images must meet minimum dimensions' => [
            [
                '_setup' => fn () => Storage::fake('public'),
                'images' => [UploadedFile::fake()->image('small.jpg', 300, 200)],
            ],
            ['images.0'],
        ],
        'thumbnail_crop is required when thumbnail is provided' => [
            [
                '_setup' => fn () => Storage::fake('public'),
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => null,
            ],
            ['thumbnail_crop'],
        ],
        'thumbnail_crop.x must be an integer' => [
            [
                '_setup' => fn () => Storage::fake('public'),
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg', 500, 500),
                'thumbnail_crop' => ['x' => 'not-an-int', 'y' => 0, 'width' => 100, 'height' => 100],
            ],
            ['thumbnail_crop.x'],
        ],
        'cannot upload more than 10 images' => [
            [
                '_setup' => fn () => Storage::fake('public'),
                'images' => array_map(fn ($i) => UploadedFile::fake()->image("image{$i}.jpg", 1280, 720), range(0, 10)),
            ],
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
        'slug is prohibited for normal users' => [
            ['slug' => 'my-custom-slug'],
            ['slug'],
        ],
    ]);

    test('admin must provide slug when updating non-approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($admin);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            // No slug provided
        ])->assertInvalid(['slug']);
    });

    test('admin can update slug on non-approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($admin);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'slug' => 'new-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ])->assertValid();

        $showcase->refresh();
        expect($showcase->slug)->toMatch('/^new-slug-\d{6}$/');
    });

    test('admin cannot provide slug for approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->approved()->for($user, 'user')->create();

        actingAs($admin);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'slug' => 'new-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ])->assertInvalid(['slug']);
    });

    test('requires at least one image when removing all existing images', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();
        $imageId = $showcase->images->first()->id;

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'removed_images' => [$imageId],
        ])->assertInvalid(['images']);
    });

    test('requires at least one image when showcase has none and no new images uploaded', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ])->assertInvalid(['images']);
    });

    test('allows update when removing some images but keeping at least one', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()
            ->has(ShowcaseImage::factory()->count(2), 'images')
            ->for($user, 'user')
            ->create();
        $imageToRemove = $showcase->images->first()->id;

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'removed_images' => [$imageToRemove],
        ])->assertValid(['images']);
    });

    test('allows update when removing all images but adding new ones', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();
        $imageId = $showcase->images->first()->id;

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'removed_images' => [$imageId],
            'images' => [UploadedFile::fake()->image('new.jpg', 1280, 720)],
        ])->assertValid(['images']);
    });
});

describe('showcase updates', function () {
    test('updates showcase with minimal required data', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create([
            'title' => 'Original Title',
            'description' => 'Original description',
            'url' => 'https://original.com',
        ]);
        $originalSlug = $showcase->slug;

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect();

        $showcase->refresh();

        assertDatabaseHas('showcases', [
            'id' => $showcase->id,
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'source_url' => null,
        ]);

        // Normal users cannot change slug - it remains unchanged
        expect($showcase->slug)->toBe($originalSlug);
    });

    test('updates showcase with all optional data', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();
        $originalSlug = $showcase->slug;

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Project',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => '## Updated Features\n- New feature 1\n- New feature 2',
            'help_needed' => 'Looking for designers to help with UI.',
            'url' => 'https://updated.com',
            'video_url' => 'https://youtube.com/watch?v=updated',
            'source_status' => SourceStatus::OpenSource->value,
            'source_url' => 'https://github.com/updated/repo',
        ]);

        $response->assertRedirect();

        $showcase->refresh();

        assertDatabaseHas('showcases', [
            'id' => $showcase->id,
            'title' => 'Updated Project',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => '## Updated Features\n- New feature 1\n- New feature 2',
            'help_needed' => 'Looking for designers to help with UI.',
            'url' => 'https://updated.com',
            'video_url' => 'https://youtube.com/watch?v=updated',
            'source_status' => SourceStatus::OpenSource->value,
            'source_url' => 'https://github.com/updated/repo',
        ]);

        // Normal users cannot change slug - it remains unchanged
        expect($showcase->slug)->toBe($originalSlug);
    });

    test('clears help_needed when set to null', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create([
            'key_features' => 'Original features',
            'help_needed' => 'Original help needed',
        ]);

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'description' => $showcase->description,
            'key_features' => 'Updated key features',
            'help_needed' => null,
            'url' => $showcase->url,
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $showcase->refresh();

        assertDatabaseHas('showcases', [
            'id' => $showcase->id,
            'key_features' => 'Updated key features',
            'help_needed' => null,
        ]);
    });

    test('preserves unchanged fields', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create([
            'title' => 'Original Title',
            'tagline' => 'Original tagline',
            'description' => 'Original description',
            'url' => 'https://original.com',
        ]);

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Original tagline',
            'description' => 'Original description',
            'key_features' => $showcase->key_features,
            'url' => 'https://original.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        assertDatabaseHas('showcases', [
            'id' => $showcase->id,
            'title' => 'Updated Title',
            'slug' => $showcase->slug, // Slug should be preserved (normal users can't change it)
            'description' => 'Original description',
            'url' => 'https://original.com',
        ]);
    });

    test('clears source_url when source_status changes to NotAvailable', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create([
            'source_status' => SourceStatus::OpenSource,
            'source_url' => 'https://github.com/user/repo',
        ]);

        expect($showcase->source_status)->toBe(SourceStatus::OpenSource);
        expect($showcase->source_url)->toBe('https://github.com/user/repo');

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'description' => $showcase->description,
            'key_features' => $showcase->key_features,
            'url' => $showcase->url,
            'source_status' => SourceStatus::NotAvailable->value,
            'source_url' => 'https://github.com/user/repo',
        ]);

        $showcase->refresh();
        expect($showcase->source_status)->toBe(SourceStatus::NotAvailable);
        expect($showcase->source_url)->toBeNull();

        assertDatabaseHas('showcases', [
            'id' => $showcase->id,
            'source_status' => SourceStatus::NotAvailable->value,
            'source_url' => null,
        ]);
    });
});

describe('status handling', function () {
    test('admin updating approved showcase keeps it approved', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->approved()->create();

        expect($showcase->status)->toBe(ShowcaseStatus::Approved);

        actingAs($admin);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $showcase->refresh();
        expect($showcase->status)->toBe(ShowcaseStatus::Approved);
    });

    test('keeps draft status when updating draft showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->draft()->create();

        expect($showcase->status)->toBe(ShowcaseStatus::Draft);

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $showcase->refresh();
        expect($showcase->status)->toBe(ShowcaseStatus::Draft);
    });

    test('keeps pending status when updating pending showcase by admin', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->pending()->create();

        expect($showcase->status)->toBe(ShowcaseStatus::Pending);

        actingAs($admin);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $showcase->refresh();
        expect($showcase->status)->toBe(ShowcaseStatus::Pending);
    });

    test('keeps rejected status when updating rejected showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->rejected()->create();

        expect($showcase->status)->toBe(ShowcaseStatus::Rejected);

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $showcase->refresh();
        expect($showcase->status)->toBe(ShowcaseStatus::Rejected);
    });
});

describe('image uploads', function () {
    test('updates showcase without new images', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();
        ShowcaseImage::factory()->for($showcase, 'showcase')->count(2)->create();

        expect($showcase->images)->toHaveCount(2);

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect();

        $showcase->refresh();
        expect($showcase->images)->toHaveCount(2);
    });

    test('updates showcase with new images', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();
        ShowcaseImage::factory()->for($showcase, 'showcase')->count(2)->create();

        actingAs($user);

        $newImage = UploadedFile::fake()->image('new-screenshot.jpg', 1280, 720);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$newImage],
        ]);

        $response->assertRedirect();

        $showcase->refresh();
        expect($showcase->images)->toHaveCount(3);
    });

    test('new images are added to existing images', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();
        ShowcaseImage::factory()->for($showcase, 'showcase')->create([
            'filename' => 'existing.jpg',
            'order' => 1,
        ]);

        actingAs($user);

        $newImage = UploadedFile::fake()->image('new.jpg', 1280, 720);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$newImage],
        ]);

        $showcase->refresh();
        expect($showcase->images)->toHaveCount(2);
        expect($showcase->images->pluck('filename')->toArray())->toContain('existing.jpg');
        expect($showcase->images->pluck('filename')->toArray())->toContain('new.jpg');
    });

    test('new images continue ordering from max order', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();
        ShowcaseImage::factory()->for($showcase, 'showcase')->create(['order' => 1]);
        ShowcaseImage::factory()->for($showcase, 'showcase')->create(['order' => 2]);

        actingAs($user);

        $newImages = [
            UploadedFile::fake()->image('new1.jpg', 1280, 720),
            UploadedFile::fake()->image('new2.jpg', 1280, 720),
        ];

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => $newImages,
        ]);

        $showcase->refresh();
        $orderedImages = $showcase->images()->orderBy('order')->get();

        expect($orderedImages)->toHaveCount(4);
        expect($orderedImages[0]->order)->toBe(1);
        expect($orderedImages[1]->order)->toBe(2);
        expect($orderedImages[2]->order)->toBe(3);
        expect($orderedImages[3]->order)->toBe(4);
    });

    test('new images are stored in correct directory', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        $image = UploadedFile::fake()->image('screenshot.jpg', 1280, 720);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$image],
        ]);

        $showcase->refresh();
        $storedImage = $showcase->images->first();

        expect($storedImage->path)->toStartWith("showcase/{$showcase->id}/images/");
        Storage::disk('public')->assertExists($storedImage->path);
    });

    test('new image filename is preserved', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        $image = UploadedFile::fake()->image('my-new-screenshot.jpg', 1280, 720);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$image],
        ]);

        $showcase->refresh();
        $storedImage = $showcase->images->first();

        expect($storedImage->filename)->toBe('my-new-screenshot.jpg');
    });

    test('uploaded images are added to existing images', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();
        $existingImage = ShowcaseImage::factory()->for($showcase, 'showcase')->create([
            'order' => 1,
        ]);

        actingAs($user);

        $newImage = UploadedFile::fake()->image('new.jpg', 1280, 720);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'images' => [$newImage],
        ]);

        $showcase->refresh();

        expect($showcase->images)->toHaveCount(2);
    });
});

describe('response', function () {
    test('redirects to showcase show page', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $showcase->refresh();

        $response->assertRedirect(route('showcase.manage.edit', $showcase));
    });

    test('includes standard success message for non-approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->draft()->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated successfully.', 'type' => 'success']);
    });

    test('includes standard success message when admin updates approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->approved()->create();

        actingAs($admin);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated successfully.', 'type' => 'success']);
    });
});

describe('submit on update', function () {
    test('updates and submits draft showcase when submit flag is true', function () {
        Notification::fake();

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->draft()->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Pending);
        expect($showcase->submitted_date)->not->toBeNull();
        expect($showcase->title)->toBe('Updated Title');

        $response->assertRedirect(route('user-area.showcases.index'));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated and submitted for approval.', 'type' => 'success']);
    });

    test('updates and submits rejected showcase when submit flag is true', function () {
        Notification::fake();

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->rejected()->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Pending);
        expect($showcase->submitted_date)->not->toBeNull();

        $response->assertRedirect(route('user-area.showcases.index'));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated and submitted for approval.', 'type' => 'success']);
    });

    test('ignores submit flag for pending showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->pending()->for($user, 'user')->create();

        actingAs($admin);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Pending);
        expect($showcase->title)->toBe('Updated Title');

        $response->assertRedirect(route('showcase.manage.edit', $showcase));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated successfully.', 'type' => 'success']);
    });

    test('ignores submit flag for approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->approved()->for($user, 'user')->create();

        actingAs($admin);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Approved);

        $response->assertRedirect(route('showcase.manage.edit', $showcase));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated successfully.', 'type' => 'success']);
    });

    test('calls SubmitShowcaseAction when submitting on update', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->draft()->for($user, 'user')->create();

        $mock = Mockery::mock(SubmitShowcaseAction::class);
        $mock->shouldReceive('submit')->once();
        app()->instance(SubmitShowcaseAction::class, $mock);

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);
    });

    test('does not submit when submit flag is false', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->draft()->for($user, 'user')->create();

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => false,
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Draft);
        expect($showcase->submitted_date)->toBeNull();

        $response->assertRedirect(route('showcase.manage.edit', $showcase));
        $response->assertSessionHas('flash.message', ['message' => 'Showcase updated successfully.', 'type' => 'success']);
    });
});

describe('thumbnail operations', function () {
    test('removes thumbnail when remove_thumbnail flag is true', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()
            ->has(ShowcaseImage::factory(), 'images')
            ->for($user, 'user')
            ->create([
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            ]);

        // Create the thumbnail file
        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.jpg", 'fake-image-content');
        Storage::disk('public')->assertExists("showcase/{$showcase->id}/thumbnail.jpg");

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'remove_thumbnail' => true,
        ]);

        $response->assertRedirect();

        $showcase->refresh();

        expect($showcase->thumbnail_extension)->toBeNull();
        expect($showcase->thumbnail_crop)->toBeNull();
        Storage::disk('public')->assertMissing("showcase/{$showcase->id}/thumbnail.jpg");
    });

    test('does not remove thumbnail when remove_thumbnail flag is false', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()
            ->has(ShowcaseImage::factory(), 'images')
            ->for($user, 'user')
            ->create([
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            ]);

        // Create the thumbnail file
        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.jpg", 'fake-image-content');

        actingAs($user);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'remove_thumbnail' => false,
        ]);

        $response->assertRedirect();

        $showcase->refresh();

        expect($showcase->thumbnail_extension)->toBe('jpg');
        expect($showcase->thumbnail_crop)->toBe(['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500]);
        Storage::disk('public')->assertExists("showcase/{$showcase->id}/thumbnail.jpg");
    });

    test('new thumbnail upload takes precedence over remove_thumbnail flag', function () {
        Storage::fake('public');

        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()
            ->has(ShowcaseImage::factory(), 'images')
            ->for($user, 'user')
            ->create([
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            ]);

        // Create the old thumbnail file
        Storage::disk('public')->put("showcase/{$showcase->id}/thumbnail.jpg", 'fake-image-content');

        actingAs($user);

        $newThumbnail = UploadedFile::fake()->image('new-thumbnail.png', 500, 500);

        $response = put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'url' => 'https://updated.com',
            'source_status' => SourceStatus::NotAvailable->value,
            'thumbnail' => $newThumbnail,
            'thumbnail_crop' => ['x' => 10, 'y' => 10, 'width' => 400, 'height' => 400],
            'remove_thumbnail' => true,
        ]);

        $response->assertRedirect();

        $showcase->refresh();

        // New thumbnail should be stored
        expect($showcase->thumbnail_extension)->toBe('png');
        expect($showcase->thumbnail_crop)->toBe(['x' => 10, 'y' => 10, 'width' => 400, 'height' => 400]);
        Storage::disk('public')->assertExists("showcase/{$showcase->id}/thumbnail.png");
        // Old thumbnail should be removed
        Storage::disk('public')->assertMissing("showcase/{$showcase->id}/thumbnail.jpg");
    });
});

describe('markdown cache clearing', function () {
    beforeEach(function () {
        Cache::flush();
    });

    test('clears markdown cache when updating description via controller', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->has(ShowcaseImage::factory(), 'images')->for($user, 'user')->create([
            'description' => 'Original content',
        ]);

        $markdownService = app(MarkdownService::class);
        $cacheKey = "showcase|{$showcase->id}|description";

        $markdownService->render(
            markdown: '**test content**',
            cacheKey: $cacheKey
        );

        $fullKey = $markdownService->getCacheKey(cacheKey: $cacheKey);
        expect(Cache::has(key: $fullKey))->toBeTrue();

        actingAs($user);

        put(route('showcase.manage.update', $showcase), [
            'practice_area_ids' => $showcase->practiceAreas->pluck('id')->toArray(),
            'title' => $showcase->title,
            'tagline' => $showcase->tagline,
            'description' => 'Updated content',
            'key_features' => $showcase->key_features,
            'url' => $showcase->url,
            'source_status' => SourceStatus::NotAvailable->value,
        ])->assertRedirect();

        expect(Cache::has(key: $fullKey))->toBeFalse();
    });
});
