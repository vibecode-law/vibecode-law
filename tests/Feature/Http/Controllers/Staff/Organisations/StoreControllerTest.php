<?php

use App\Models\Organisation\Organisation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $response = post(route('staff.organisations.store'), [
            'name' => 'Test Organisation',
            'tagline' => 'A test tagline',
            'about' => 'Some about text',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = post(route('staff.organisations.store'), [
            'name' => 'Test Organisation',
            'tagline' => 'A test tagline',
            'about' => 'Some about text',
        ]);

        $response->assertForbidden();
    });

    test('allows admin to create organisation', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.organisations.store'), [
            'name' => 'Test Organisation',
            'tagline' => 'A test tagline',
            'about' => 'Some about text',
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

        actingAs($admin);

        $response = post(route('staff.organisations.store'), $data);

        $response->assertInvalid($invalidFields);
    })->with([
        'name is required' => [
            ['name' => null, 'tagline' => 'A tagline', 'about' => 'Some about text'],
            ['name'],
        ],
        'name cannot exceed 255 characters' => [
            ['name' => str_repeat('a', 256), 'tagline' => 'A tagline', 'about' => 'Some about text'],
            ['name'],
        ],
        'name must be unique' => [
            [
                'name' => 'Existing Org',
                'tagline' => 'A tagline',
                'about' => 'Some about text',
                '_setup' => fn () => Organisation::factory()->create(['name' => 'Existing Org']),
            ],
            ['name'],
        ],
        'tagline is required' => [
            ['name' => 'Test Org', 'tagline' => null, 'about' => 'Some about text'],
            ['tagline'],
        ],
        'tagline cannot exceed 255 characters' => [
            ['name' => 'Test Org', 'tagline' => str_repeat('a', 256), 'about' => 'Some about text'],
            ['tagline'],
        ],
        'about is required' => [
            ['name' => 'Test Org', 'tagline' => 'A tagline', 'about' => null],
            ['about'],
        ],
    ]);

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        post(route('staff.organisations.store'), [
            'name' => 'Test Org',
            'tagline' => 'Tagline',
            'about' => 'About',
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('validates thumbnail max size', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->image(name: 'large.jpg')->size(kilobytes: 3000);

        post(route('staff.organisations.store'), [
            'name' => 'Test Org',
            'tagline' => 'Tagline',
            'about' => 'About',
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('rejects invalid crop keys', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.organisations.store'), [
            'name' => 'Test Org',
            'tagline' => 'Tagline',
            'about' => 'About',
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'portrait' => ['x' => 0, 'y' => 0, 'width' => 300, 'height' => 500],
            ],
        ])->assertSessionHasErrors(['thumbnail_crops']);
    });

    test('rejects crops with incorrect aspect ratios', function ($crops) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.organisations.store'), [
            'name' => 'Test Org',
            'tagline' => 'Tagline',
            'about' => 'About',
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

    test('accepts crops with correct aspect ratios', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.organisations.store'), [
            'name' => 'Ratio Org',
            'tagline' => 'Tagline',
            'about' => 'About',
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'square' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            ],
        ])->assertSessionDoesntHaveErrors(['thumbnail_crops']);
    });
});

describe('creation', function () {
    test('creates organisation with provided data', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.organisations.store'), [
            'name' => 'Acme Legal',
            'tagline' => 'Leading legal innovation',
            'about' => 'Acme Legal is a pioneer in legal tech.',
        ]);

        assertDatabaseHas('organisations', [
            'name' => 'Acme Legal',
            'tagline' => 'Leading legal innovation',
            'about' => 'Acme Legal is a pioneer in legal tech.',
        ]);
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'logo.jpg', width: 400, height: 300);

        post(route('staff.organisations.store'), [
            'name' => 'Thumb Org',
            'tagline' => 'With thumbnail',
            'about' => 'About.',
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $organisation = Organisation::query()->where('name', 'Thumb Org')->firstOrFail();

        expect($organisation->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("organisation/{$organisation->id}/thumbnail.{$organisation->thumbnail_extension}");
    });

    test('handles thumbnail upload with crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'logo.jpg', width: 800, height: 600);

        post(route('staff.organisations.store'), [
            'name' => 'Crop Org',
            'tagline' => 'With crops',
            'about' => 'About.',
            'thumbnail' => $thumbnail,
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
            ],
        ])->assertRedirect();

        $organisation = Organisation::query()->where('name', 'Crop Org')->firstOrFail();

        expect($organisation->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('creates organisation without thumbnail by default', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.organisations.store'), [
            'name' => 'No Thumb Org',
            'tagline' => 'Without thumbnail',
            'about' => 'About.',
        ])->assertRedirect();

        $organisation = Organisation::query()->where('name', 'No Thumb Org')->firstOrFail();

        expect($organisation->thumbnail_extension)->toBeNull()
            ->and($organisation->thumbnail_crops)->toBeNull();
    });
});

describe('response', function () {
    test('redirects back', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.organisations.store'), [
            'name' => 'Test Organisation',
            'tagline' => 'A test tagline',
            'about' => 'Some about text',
        ]);

        $response->assertRedirect();
    });

    test('includes success message in session', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.organisations.store'), [
            'name' => 'Test Organisation',
            'tagline' => 'A test tagline',
            'about' => 'Some about text',
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Organisation created successfully.', 'type' => 'success']);
    });

    test('includes created organisation in flash', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('staff.organisations.store'), [
            'name' => 'Flash Test Org',
            'tagline' => 'A test tagline',
            'about' => 'Some about text',
        ]);

        $organisation = Organisation::query()->where('name', 'Flash Test Org')->first();

        $response->assertSessionHas('flash.created_organisation', [
            'id' => $organisation->id,
            'name' => 'Flash Test Org',
        ]);
    });
});
