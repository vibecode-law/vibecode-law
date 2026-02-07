<?php

use App\Models\PressCoverage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        post(route('staff.press-coverage.store'), [
            'title' => 'Test Article',
            'publication_name' => 'Test Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/article',
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.press-coverage.store'), [
            'title' => 'Test Article',
            'publication_name' => 'Test Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/article',
        ])->assertForbidden();
    });

    test('allows moderators to store press coverage', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'Test Article',
            'publication_name' => 'Test Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/article',
        ])->assertRedirect();
    });
});

describe('storing', function () {
    test('creates press coverage with all fields', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'AI in Legal',
            'publication_name' => 'Legal Times',
            'publication_date' => '2026-01-15',
            'url' => 'https://example.com/article',
            'excerpt' => 'A summary.',
            'is_published' => true,
            'display_order' => 3,
        ])->assertRedirect();

        $item = PressCoverage::latest('id')->first();

        expect($item->title)->toBe('AI in Legal')
            ->and($item->publication_name)->toBe('Legal Times')
            ->and($item->publication_date->format('Y-m-d'))->toBe('2026-01-15')
            ->and($item->url)->toBe('https://example.com/article')
            ->and($item->excerpt)->toBe('A summary.')
            ->and($item->is_published)->toBeTrue()
            ->and($item->display_order)->toBe(3);
    });

    test('creates press coverage with only required fields', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'Minimal Article',
            'publication_name' => 'Some Pub',
            'publication_date' => '2026-02-01',
            'url' => 'https://example.com/minimal',
        ])->assertRedirect();

        $item = PressCoverage::latest('id')->first();

        expect($item->title)->toBe('Minimal Article')
            ->and($item->excerpt)->toBeNull()
            ->and($item->thumbnail_extension)->toBeNull()
            ->and($item->thumbnail_crop)->toBeNull();
    });

    test('handles thumbnail upload', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'With Thumbnail',
            'publication_name' => 'Pub',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/thumb',
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 200, 200),
        ])->assertRedirect();

        $item = PressCoverage::latest('id')->first();

        expect($item->thumbnail_extension)->toBe('jpg');
        Storage::disk('public')->assertExists("press-coverage/{$item->id}/thumbnail.jpg");
    });

    test('handles thumbnail upload with crop data', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'With Crop',
            'publication_name' => 'Pub',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/crop',
            'thumbnail' => UploadedFile::fake()->image('thumbnail.png', 200, 200),
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 80],
        ])->assertRedirect();

        $item = PressCoverage::latest('id')->first();

        expect($item->thumbnail_extension)->toBe('png')
            ->and($item->thumbnail_crop)->toBe(['x' => 10, 'y' => 20, 'width' => 100, 'height' => 80]);
    });
});

describe('validation', function () {
    test('validates required and invalid data', function (array $data, array $invalidFields) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), $data)
            ->assertInvalid($invalidFields);
    })->with([
        'title is required' => [
            ['publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com'],
            ['title'],
        ],
        'title max 255 characters' => [
            ['title' => str_repeat('a', 256), 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com'],
            ['title'],
        ],
        'publication_name is required' => [
            ['title' => 'Title', 'publication_date' => '2026-01-01', 'url' => 'https://example.com'],
            ['publication_name'],
        ],
        'publication_date is required' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'url' => 'https://example.com'],
            ['publication_date'],
        ],
        'publication_date must be a date' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => 'not-a-date', 'url' => 'https://example.com'],
            ['publication_date'],
        ],
        'url is required' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01'],
            ['url'],
        ],
        'url must be a valid url' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'not-a-url'],
            ['url'],
        ],
        'url max 500 characters' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com/'.str_repeat('a', 500)],
            ['url'],
        ],
        'excerpt max 500 characters' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com', 'excerpt' => str_repeat('a', 501)],
            ['excerpt'],
        ],
        'display_order min 0' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com', 'display_order' => -1],
            ['display_order'],
        ],
        'thumbnail must be an image' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com', 'thumbnail' => UploadedFile::fake()->create('doc.pdf', 100)],
            ['thumbnail'],
        ],
        'thumbnail max 2MB' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com', 'thumbnail' => UploadedFile::fake()->image('large.jpg', 200, 200)->size(3000)],
            ['thumbnail'],
        ],
    ]);

    test('rejects invalid thumbnail file types', function (string $extension) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'Title',
            'publication_name' => 'Pub',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com',
            'thumbnail' => UploadedFile::fake()->create("file.{$extension}", 100),
        ])->assertInvalid(['thumbnail']);
    })->with([
        'svg',
        'bmp',
        'tiff',
    ]);

    test('accepts valid thumbnail file types', function (string $extension) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.press-coverage.store'), [
            'title' => 'Title',
            'publication_name' => 'Pub',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com',
            'thumbnail' => UploadedFile::fake()->image("thumb.{$extension}", 200, 200),
        ])->assertSessionHasNoErrors();
    })->with([
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
    ]);
});
