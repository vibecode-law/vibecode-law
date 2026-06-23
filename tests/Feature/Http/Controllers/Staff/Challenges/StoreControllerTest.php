<?php

use App\Enums\ChallengeVisibility;
use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test Challenge',
            'slug' => 'test-challenge',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users to create challenges', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test Challenge',
            'slug' => 'test-challenge',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
        ])->assertForbidden();
    });

    test('allows a user with challenge.create permission', function () {
        $user = userWithPermissions(['challenge.view', 'challenge.create']);

        actingAs($user);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test Challenge',
            'slug' => 'test-challenge',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
        ])->assertRedirect();
    });

    test('forbids a staff user without challenge.create permission', function () {
        $user = userWithPermissions(['challenge.view']);

        actingAs($user);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test Challenge',
            'slug' => 'test-challenge',
            'tagline' => 'A test tagline',
            'description' => 'A test description',
        ])->assertForbidden();
    });
});

describe('store', function () {
    test('creates a new challenge and redirects to edit page', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'AI Legal Challenge',
            'slug' => 'ai-legal-challenge',
            'tagline' => 'Build the future of legal AI',
            'description' => 'A comprehensive challenge for legal tech.',
        ])->assertRedirect(
            route('staff.challenges.edit', Challenge::query()->where('slug', 'ai-legal-challenge')->firstOrFail())
        );
    });

    test('creates challenge with participant_instructions', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Participant Challenge',
            'slug' => 'participant-challenge',
            'tagline' => 'A challenge with instructions',
            'description' => 'Description here.',
            'participant_instructions' => 'Submit via the form by Friday.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'participant-challenge')->firstOrFail();

        expect($challenge->participant_instructions)->toBe('Submit via the form by Friday.');
    });

    test('creates challenge with organisation', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Org Challenge',
            'slug' => 'org-challenge',
            'tagline' => 'An org challenge',
            'description' => 'Description here.',
            'organisation_id' => $organisation->id,
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'org-challenge')->firstOrFail();

        expect($challenge->organisation_id)->toBe($organisation->id);
    });

    test('creates challenge with dates', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'UTC',
            'title' => 'Dated Challenge',
            'slug' => 'dated-challenge',
            'tagline' => 'A dated challenge',
            'description' => 'Description here.',
            'starts_at' => '2026-03-01T00:00',
            'ends_at' => '2026-04-01T23:59',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'dated-challenge')->firstOrFail();

        expect($challenge->starts_at->format('Y-m-d'))->toBe('2026-03-01')
            ->and($challenge->ends_at->format('Y-m-d'))->toBe('2026-04-01');
    });

    test('pins start to the first second and end to the last second of the minute', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'UTC',
            'title' => 'Timed Challenge',
            'slug' => 'timed-challenge',
            'tagline' => 'A timed challenge',
            'description' => 'Description here.',
            'starts_at' => '2026-03-01T09:30',
            'ends_at' => '2026-04-01T17:45',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'timed-challenge')->firstOrFail();

        expect($challenge->starts_at->format('Y-m-d H:i:s'))->toBe('2026-03-01 09:30:00')
            ->and($challenge->ends_at->format('Y-m-d H:i:s'))->toBe('2026-04-01 17:45:59');
    });

    test('converts entered times from the challenge timezone to UTC', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        // 1 July is BST (UTC+1), so 09:00 London is 08:00 UTC.
        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Zoned Challenge',
            'slug' => 'zoned-challenge',
            'tagline' => 'A zoned challenge',
            'description' => 'Description here.',
            'starts_at' => '2026-07-01T09:00',
            'ends_at' => '2026-07-31T17:00',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'zoned-challenge')->firstOrFail();

        expect($challenge->timezone)->toBe('Europe/London')
            ->and($challenge->starts_at->format('Y-m-d H:i:s'))->toBe('2026-07-01 08:00:00')
            ->and($challenge->ends_at->format('Y-m-d H:i:s'))->toBe('2026-07-31 16:00:59');
    });

    test('stores a null timezone when blank and no dates are set', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => '',
            'title' => 'Undated Challenge',
            'slug' => 'undated-challenge',
            'tagline' => 'An undated challenge',
            'description' => 'Description here.',
        ])->assertValid('timezone')->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'undated-challenge')->firstOrFail();

        expect($challenge->starts_at)->toBeNull()
            ->and($challenge->ends_at)->toBeNull()
            ->and($challenge->timezone)->toBeNull();
    });

    test('nulls a submitted timezone when no dates are set', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Undated Challenge',
            'slug' => 'undated-challenge',
            'tagline' => 'An undated challenge',
            'description' => 'Description here.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'undated-challenge')->firstOrFail();

        expect($challenge->starts_at)->toBeNull()
            ->and($challenge->ends_at)->toBeNull()
            ->and($challenge->timezone)->toBeNull();
    });

    test('creates challenge with active and featured flags', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Featured Challenge',
            'slug' => 'featured-challenge',
            'tagline' => 'A featured challenge',
            'description' => 'Description here.',
            'is_active' => true,
            'is_featured' => true,
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'featured-challenge')->firstOrFail();

        expect($challenge->is_active)->toBeTrue()
            ->and($challenge->is_featured)->toBeTrue();
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Thumb Challenge',
            'slug' => 'thumb-challenge',
            'tagline' => 'A thumbnail challenge',
            'description' => 'Description here.',
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'thumb-challenge')->firstOrFail();

        expect($challenge->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("challenge/{$challenge->id}/thumbnail.{$challenge->thumbnail_extension}");
    });

    test('handles thumbnail upload with multiple crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 800, height: 600);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Crop Challenge',
            'slug' => 'crop-challenge',
            'tagline' => 'A crop challenge',
            'description' => 'Description here.',
            'thumbnail' => $thumbnail,
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
            ],
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'crop-challenge')->firstOrFail();

        expect($challenge->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('strips extra shapes and fields from thumbnail crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Strip Challenge',
            'slug' => 'strip-challenge',
            'tagline' => 'With extras',
            'description' => 'Description here.',
            'thumbnail' => UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400, 'zoom' => 1.5],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
                'portrait' => ['x' => 0, 'y' => 0, 'width' => 300, 'height' => 500],
            ],
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'strip-challenge')->firstOrFail();

        expect($challenge->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('returns success flash message', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Flash Challenge',
            'slug' => 'flash-challenge',
            'tagline' => 'A flash challenge',
            'description' => 'Description here.',
        ])->assertSessionHas('flash.message', [
            'message' => 'Challenge created successfully.',
            'type' => 'success',
        ]);
    });

    test('creates challenge without organisation by default', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'No Org Challenge',
            'slug' => 'no-org-challenge',
            'tagline' => 'No org',
            'description' => 'Description here.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'no-org-challenge')->firstOrFail();

        expect($challenge->organisation_id)->toBeNull();
    });

    test('creates challenge with visibility', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Invite Challenge',
            'slug' => 'invite-challenge',
            'tagline' => 'An invite challenge',
            'description' => 'Description here.',
            'visibility' => ChallengeVisibility::InviteToSubmit->value,
            'involvement_instructions' => 'Request an invite code to take part.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'invite-challenge')->firstOrFail();

        expect($challenge->visibility)->toBe(ChallengeVisibility::InviteToSubmit)
            ->and($challenge->involvement_instructions)->toBe('Request an invite code to take part.');
    });

    test('forces is_featured to false when visibility is invite to view and submit', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Invite View Challenge',
            'slug' => 'invite-view-challenge',
            'tagline' => 'An invite to view challenge',
            'description' => 'Description here.',
            'visibility' => ChallengeVisibility::InviteToViewAndSubmit->value,
            'is_featured' => true,
            'involvement_instructions' => 'Request an invite code to take part.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'invite-view-challenge')->firstOrFail();

        expect($challenge->is_featured)->toBeFalse()
            ->and($challenge->visibility)->toBe(ChallengeVisibility::InviteToViewAndSubmit);
    });

    test('requires involvement_instructions when visibility requires an invite to submit', function (int $visibility) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Invite Challenge',
            'slug' => 'invite-challenge',
            'tagline' => 'An invite challenge',
            'description' => 'Description here.',
            'visibility' => $visibility,
        ])->assertSessionHasErrors(['involvement_instructions']);
    })->with([
        'invite to submit' => [ChallengeVisibility::InviteToSubmit->value],
        'invite to view and submit' => [ChallengeVisibility::InviteToViewAndSubmit->value],
    ]);

    test('does not require involvement_instructions for public challenges', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Public Challenge',
            'slug' => 'public-challenge',
            'tagline' => 'A public challenge',
            'description' => 'Description here.',
            'visibility' => ChallengeVisibility::Public->value,
        ])->assertSessionDoesntHaveErrors(['involvement_instructions']);
    });

    test('creates challenge with public visibility by default', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Default Vis Challenge',
            'slug' => 'default-vis-challenge',
            'tagline' => 'Default visibility',
            'description' => 'Description here.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'default-vis-challenge')->firstOrFail();

        expect($challenge->visibility)->toBe(ChallengeVisibility::Public);
    });

    test('creates challenge inactive by default', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Inactive Challenge',
            'slug' => 'inactive-challenge',
            'tagline' => 'Inactive',
            'description' => 'Description here.',
        ])->assertRedirect();

        $challenge = Challenge::query()->where('slug', 'inactive-challenge')->firstOrFail();

        expect($challenge->is_active)->toBeFalse()
            ->and($challenge->is_featured)->toBeFalse();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing title' => [
            ['slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc'],
            ['title'],
        ],
        'missing slug' => [
            ['title' => 'Test', 'tagline' => 'Tagline', 'description' => 'Desc'],
            ['slug'],
        ],
        'missing tagline' => [
            ['title' => 'Test', 'slug' => 'test', 'description' => 'Desc'],
            ['tagline'],
        ],
        'missing description' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline'],
            ['description'],
        ],
        'invalid slug format' => [
            ['title' => 'Test', 'slug' => 'Invalid Slug!', 'tagline' => 'Tagline', 'description' => 'Desc'],
            ['slug'],
        ],
        'title too long' => [
            ['title' => str_repeat('a', 81), 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc'],
            ['title'],
        ],
        'ends_at before starts_at' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'starts_at' => '2026-04-01', 'ends_at' => '2026-03-01'],
            ['ends_at'],
        ],
        'invalid organisation_id' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'organisation_id' => 99999],
            ['organisation_id'],
        ],
        'missing timezone when a date is set' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'starts_at' => '2026-03-01T00:00', 'timezone' => ''],
            ['timezone'],
        ],
        'invalid timezone when a date is set' => [
            ['title' => 'Test', 'slug' => 'test', 'tagline' => 'Tagline', 'description' => 'Desc', 'starts_at' => '2026-03-01T00:00', 'timezone' => 'Not/AZone'],
            ['timezone'],
        ],
    ]);

    test('prohibits thumbnail when organisation is selected', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'organisation_id' => $organisation->id,
            'thumbnail' => $thumbnail,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('validates unique slug', function () {
        $admin = User::factory()->admin()->create();
        Challenge::factory()->create(['slug' => 'existing-slug']);

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test',
            'slug' => 'existing-slug',
            'tagline' => 'Tagline',
            'description' => 'Description',
        ])->assertSessionHasErrors(['slug']);
    });

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('validates thumbnail max size', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->image(name: 'large.jpg')->size(kilobytes: 3000);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('rejects crops with incorrect aspect ratios', function ($crops) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Test',
            'slug' => 'test',
            'tagline' => 'Tagline',
            'description' => 'Description',
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

        post(route('staff.challenges.store'), [
            'timezone' => 'Europe/London',
            'title' => 'Ratio Test',
            'slug' => 'ratio-test',
            'tagline' => 'Tagline',
            'description' => 'Description',
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'square' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            ],
        ])->assertSessionDoesntHaveErrors(['thumbnail_crops']);
    });
});
