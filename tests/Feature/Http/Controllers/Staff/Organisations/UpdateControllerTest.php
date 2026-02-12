<?php

use App\Models\Organisation\Organisation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $organisation = Organisation::factory()->create();

        $response = patch(route('staff.organisations.update', $organisation), [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        actingAs($user);

        $response = patch(route('staff.organisations.update', $organisation), [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ]);

        $response->assertForbidden();
    });

    test('allows admin to update organisation', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $response = patch(route('staff.organisations.update', $organisation), [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ]);

        $response->assertRedirect();
    });
});

describe('validation', function () {
    test('validates organisation data', function (array $data, array $invalidFields) {
        if (isset($data['_setup'])) {
            $data['_setup']();
            unset($data['_setup']);
        }

        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create(['name' => 'Original Org']);

        actingAs($admin);

        $baseData = [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ];

        $response = patch(route('staff.organisations.update', $organisation), array_merge($baseData, $data));

        $response->assertInvalid($invalidFields);
    })->with([
        'name is required' => [
            ['name' => null],
            ['name'],
        ],
        'name cannot exceed 255 characters' => [
            ['name' => str_repeat('a', 256)],
            ['name'],
        ],
        'name must be unique' => [
            [
                'name' => 'Existing Org',
                '_setup' => fn () => Organisation::factory()->create(['name' => 'Existing Org']),
            ],
            ['name'],
        ],
        'tagline is required' => [
            ['tagline' => null],
            ['tagline'],
        ],
        'tagline cannot exceed 255 characters' => [
            ['tagline' => str_repeat('a', 256)],
            ['tagline'],
        ],
        'about is required' => [
            ['about' => null],
            ['about'],
        ],
    ]);

    test('allows updating with same name', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create(['name' => 'Original Name']);

        actingAs($admin);

        $response = patch(route('staff.organisations.update', $organisation), [
            'name' => 'Original Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ]);

        $response->assertRedirect();
    });

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('validates thumbnail max size', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->image(name: 'large.jpg')->size(kilobytes: 3000);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('rejects invalid crop keys', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'portrait' => ['x' => 0, 'y' => 0, 'width' => 300, 'height' => 500],
            ],
        ])->assertSessionHasErrors(['thumbnail_crops']);
    });

    test('rejects crops with incorrect aspect ratios', function ($crops) {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => $crops,
        ])->assertSessionHasErrors(['thumbnail_crops']);
    })->with([
        'non-square square crop' => [
            ['square' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 300]],
        ],
        'non-landscape landscape crop' => [
            ['landscape' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400]],
        ],
    ]);
});

describe('update', function () {
    test('updates organisation fields', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create([
            'name' => 'Original',
            'tagline' => 'Original tagline',
            'about' => 'Original about',
        ]);

        actingAs($admin);

        patch(route('staff.organisations.update', $organisation), [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about text',
        ]);

        assertDatabaseHas('organisations', [
            'id' => $organisation->id,
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about text',
        ]);
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'logo.jpg', width: 400, height: 300);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $organisation->refresh();

        expect($organisation->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("organisation/{$organisation->id}/thumbnail.{$organisation->thumbnail_extension}");
    });

    test('handles thumbnail upload with crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'logo.jpg', width: 800, height: 600);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail' => $thumbnail,
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
            ],
        ])->assertRedirect();

        $organisation->refresh();

        expect($organisation->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('handles crop-only update without new file', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crops' => [
                'square' => ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 200],
            ],
        ]);

        Storage::disk('public')->put("organisation/{$organisation->id}/thumbnail.jpg", 'fake-image');

        actingAs($admin);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'thumbnail_crops' => [
                'square' => ['x' => 50, 'y' => 50, 'width' => 300, 'height' => 300],
                'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            ],
        ])->assertRedirect();

        $organisation->refresh();

        expect($organisation->thumbnail_extension)->toBe('jpg')
            ->and($organisation->thumbnail_crops)->toBe([
                'square' => ['x' => 50, 'y' => 50, 'width' => 300, 'height' => 300],
                'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            ]);
    });

    test('handles thumbnail removal', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crops' => [
                'square' => ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 200],
            ],
        ]);

        Storage::disk('public')->put("organisation/{$organisation->id}/thumbnail.jpg", 'fake-image');

        actingAs($admin);

        patch(route('staff.organisations.update', $organisation), [
            'name' => $organisation->name,
            'tagline' => $organisation->tagline,
            'about' => $organisation->about,
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $organisation->refresh();

        expect($organisation->thumbnail_extension)->toBeNull()
            ->and($organisation->thumbnail_crops)->toBeNull();
        Storage::disk('public')->assertMissing("organisation/{$organisation->id}/thumbnail.jpg");
    });
});

describe('response', function () {
    test('redirects back', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $response = patch(route('staff.organisations.update', $organisation), [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ]);

        $response->assertRedirect();
    });

    test('includes success message in session', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $response = patch(route('staff.organisations.update', $organisation), [
            'name' => 'Updated Name',
            'tagline' => 'Updated tagline',
            'about' => 'Updated about',
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Organisation updated successfully.', 'type' => 'success']);
    });
});
