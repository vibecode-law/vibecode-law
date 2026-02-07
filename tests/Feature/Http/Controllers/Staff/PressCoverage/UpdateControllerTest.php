<?php

use App\Models\PressCoverage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $pressCoverage = PressCoverage::factory()->create();

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title',
            'publication_name' => 'Updated Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/updated',
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($user);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title',
            'publication_name' => 'Updated Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/updated',
        ])->assertForbidden();
    });

    test('allows moderators to update press coverage', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title',
            'publication_name' => 'Updated Publication',
            'publication_date' => '2026-01-01',
            'url' => 'https://example.com/updated',
        ])->assertRedirect();
    });
});

describe('updating', function () {
    test('updates press coverage fields', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'New Title',
            'publication_name' => 'New Publication',
            'publication_date' => '2026-06-15',
            'url' => 'https://example.com/new',
            'excerpt' => 'New excerpt.',
            'is_published' => true,
            'display_order' => 5,
        ])->assertRedirect();

        $pressCoverage->refresh();

        expect($pressCoverage->title)->toBe('New Title')
            ->and($pressCoverage->publication_name)->toBe('New Publication')
            ->and($pressCoverage->publication_date->format('Y-m-d'))->toBe('2026-06-15')
            ->and($pressCoverage->url)->toBe('https://example.com/new')
            ->and($pressCoverage->excerpt)->toBe('New excerpt.')
            ->and($pressCoverage->is_published)->toBeTrue()
            ->and($pressCoverage->display_order)->toBe(5);
    });

    test('handles thumbnail upload', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => $pressCoverage->title,
            'publication_name' => $pressCoverage->publication_name,
            'publication_date' => $pressCoverage->publication_date->format('Y-m-d'),
            'url' => $pressCoverage->url,
            'thumbnail' => UploadedFile::fake()->image('thumbnail.png', 200, 200),
        ])->assertRedirect();

        $pressCoverage->refresh();

        expect($pressCoverage->thumbnail_extension)->toBe('png');
        Storage::disk('public')->assertExists("press-coverage/{$pressCoverage->id}/thumbnail.png");
    });

    test('deletes old thumbnail when uploading new one', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => 'jpg',
        ]);
        Storage::disk('public')->put("press-coverage/{$pressCoverage->id}/thumbnail.jpg", 'old content');

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => $pressCoverage->title,
            'publication_name' => $pressCoverage->publication_name,
            'publication_date' => $pressCoverage->publication_date->format('Y-m-d'),
            'url' => $pressCoverage->url,
            'thumbnail' => UploadedFile::fake()->image('new-thumb.png', 200, 200),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing("press-coverage/{$pressCoverage->id}/thumbnail.jpg");
        Storage::disk('public')->assertExists("press-coverage/{$pressCoverage->id}/thumbnail.png");
    });

    test('removes thumbnail when remove_thumbnail is true', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100],
        ]);
        Storage::disk('public')->put("press-coverage/{$pressCoverage->id}/thumbnail.jpg", 'content');

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => $pressCoverage->title,
            'publication_name' => $pressCoverage->publication_name,
            'publication_date' => $pressCoverage->publication_date->format('Y-m-d'),
            'url' => $pressCoverage->url,
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $pressCoverage->refresh();

        expect($pressCoverage->thumbnail_extension)->toBeNull()
            ->and($pressCoverage->thumbnail_crop)->toBeNull();
        Storage::disk('public')->assertMissing("press-coverage/{$pressCoverage->id}/thumbnail.jpg");
    });

    test('preserves thumbnail when updating without thumbnail changes', function () {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create([
            'thumbnail_extension' => 'jpg',
        ]);
        Storage::disk('public')->put("press-coverage/{$pressCoverage->id}/thumbnail.jpg", 'content');

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), [
            'title' => 'Updated Title Only',
            'publication_name' => $pressCoverage->publication_name,
            'publication_date' => $pressCoverage->publication_date->format('Y-m-d'),
            'url' => $pressCoverage->url,
        ])->assertRedirect();

        $pressCoverage->refresh();

        expect($pressCoverage->thumbnail_extension)->toBe('jpg');
        Storage::disk('public')->assertExists("press-coverage/{$pressCoverage->id}/thumbnail.jpg");
    });
});

describe('validation', function () {
    test('validates required and invalid data', function (array $data, array $invalidFields) {
        $moderator = User::factory()->moderator()->create();
        $pressCoverage = PressCoverage::factory()->create();

        actingAs($moderator);

        put(route('staff.press-coverage.update', $pressCoverage), $data)
            ->assertInvalid($invalidFields);
    })->with([
        'title is required' => [
            ['publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com'],
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
        'url is required' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01'],
            ['url'],
        ],
        'url must be valid' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'not-valid'],
            ['url'],
        ],
        'excerpt max 500 characters' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com', 'excerpt' => str_repeat('a', 501)],
            ['excerpt'],
        ],
        'thumbnail must be an image' => [
            ['title' => 'Title', 'publication_name' => 'Pub', 'publication_date' => '2026-01-01', 'url' => 'https://example.com', 'thumbnail' => UploadedFile::fake()->create('doc.pdf', 100)],
            ['thumbnail'],
        ],
    ]);
});
