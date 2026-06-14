<?php

use App\Enums\ChallengeVisibility;
use App\Models\Challenge\Challenge;
use App\Models\Organisation\Organisation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $challenge = Challenge::factory()->create();

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated',
            'slug' => $challenge->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect(route('login'));
    });

    test('does not allow regular users to update challenges', function () {
        /** @var User */
        $user = User::factory()->create();
        $challenge = Challenge::factory()->create();

        actingAs($user);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated',
            'slug' => $challenge->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertForbidden();
    });

    test('allows a user with challenge.update permission', function () {
        $user = userWithPermissions(['challenge.view', 'challenge.update']);
        $challenge = Challenge::factory()->create();

        actingAs($user);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated',
            'slug' => $challenge->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect();
    });

    test('forbids a staff user without challenge.update permission', function () {
        $user = userWithPermissions(['challenge.view']);
        $challenge = Challenge::factory()->create();

        actingAs($user);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated',
            'slug' => $challenge->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates challenge fields', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated Challenge',
            'slug' => 'updated-challenge',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description content.',
            'is_active' => true,
            'is_featured' => true,
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->title)->toBe('Updated Challenge')
            ->and($challenge->slug)->toBe('updated-challenge')
            ->and($challenge->tagline)->toBe('Updated tagline')
            ->and($challenge->description)->toBe('Updated description content.')
            ->and($challenge->is_active)->toBeTrue()
            ->and($challenge->is_featured)->toBeTrue();
    });

    test('updates live view fields', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'live_view_enabled' => '1',
            'live_view_access_token' => 'secret-key',
            'live_view_heading' => 'LIVE LEADERBOARD',
            'live_view_subheading' => 'Vote now',
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->live_view_enabled)->toBeTrue()
            ->and($challenge->live_view_access_token)->toBe('secret-key')
            ->and($challenge->live_view_heading)->toBe('LIVE LEADERBOARD')
            ->and($challenge->live_view_subheading)->toBe('Vote now');
    });

    test('clears blank live view fields to null', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create([
            'live_view_access_token' => 'old-key',
            'live_view_heading' => 'Old heading',
        ]);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'live_view_enabled' => '0',
            'live_view_access_token' => '',
            'live_view_heading' => '',
            'live_view_subheading' => '',
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->live_view_enabled)->toBeFalse()
            ->and($challenge->live_view_access_token)->toBeNull()
            ->and($challenge->live_view_heading)->toBeNull()
            ->and($challenge->live_view_subheading)->toBeNull();
    });

    test('updates challenge organisation', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'organisation_id' => $organisation->id,
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->organisation_id)->toBe($organisation->id);
    });

    test('handles thumbnail upload', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'thumbnail' => $thumbnail,
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->thumbnail_extension)->not->toBeNull();
        Storage::disk('public')->assertExists("challenge/{$challenge->id}/thumbnail.{$challenge->thumbnail_extension}");
    });

    test('handles thumbnail upload with multiple crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 800, height: 600);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'thumbnail' => $thumbnail,
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
            ],
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('strips extra shapes and fields from thumbnail crops', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'thumbnail' => UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400, 'zoom' => 1.5],
                'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
                'portrait' => ['x' => 0, 'y' => 0, 'width' => 300, 'height' => 500],
            ],
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->thumbnail_crops)->toBe([
            'square' => ['x' => 100, 'y' => 50, 'width' => 400, 'height' => 400],
            'landscape' => ['x' => 0, 'y' => 50, 'width' => 800, 'height' => 450],
        ]);
    });

    test('updates crops without uploading a new image', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crops' => [
                'square' => ['x' => 0, 'y' => 0, 'width' => 200, 'height' => 200],
                'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            ],
        ]);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'thumbnail_crops' => [
                'square' => ['x' => 50, 'y' => 50, 'width' => 300, 'height' => 300],
                'landscape' => ['x' => 10, 'y' => 20, 'width' => 640, 'height' => 360],
            ],
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->thumbnail_crops)->toBe([
            'square' => ['x' => 50, 'y' => 50, 'width' => 300, 'height' => 300],
            'landscape' => ['x' => 10, 'y' => 20, 'width' => 640, 'height' => 360],
        ]);
    });

    test('handles thumbnail removal', function () {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create([
            'thumbnail_extension' => 'jpg',
        ]);

        Storage::disk('public')->put(
            "challenge/{$challenge->id}/thumbnail.jpg",
            'fake-image-content'
        );

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'remove_thumbnail' => true,
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->thumbnail_extension)->toBeNull()
            ->and($challenge->thumbnail_crops)->toBeNull();
        Storage::disk('public')->assertMissing("challenge/{$challenge->id}/thumbnail.jpg");
    });

    test('redirects to edit page with flash message', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated',
            'slug' => $challenge->slug,
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertRedirect(route('staff.challenges.edit', $challenge))
            ->assertSessionHas('flash.message', [
                'message' => 'Challenge updated successfully.',
                'type' => 'success',
            ]);
    });

    test('updates visibility field', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::InviteToViewAndSubmit->value,
            'involvement_instructions' => 'Request an invite code to take part.',
        ])->assertRedirect();

        expect($challenge->refresh()->visibility)->toBe(ChallengeVisibility::InviteToViewAndSubmit);
    });

    test('forces is_featured to false when visibility is invite to view and submit', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->featured()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::InviteToViewAndSubmit->value,
            'is_featured' => true,
            'involvement_instructions' => 'Request an invite code to take part.',
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->is_featured)->toBeFalse()
            ->and($challenge->visibility)->toBe(ChallengeVisibility::InviteToViewAndSubmit);
    });

    test('requires involvement_instructions when visibility requires an invite to submit', function (int $visibility) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => $visibility,
        ])->assertSessionHasErrors(['involvement_instructions']);
    })->with([
        'invite to submit' => [ChallengeVisibility::InviteToSubmit->value],
        'invite to view and submit' => [ChallengeVisibility::InviteToViewAndSubmit->value],
    ]);

    test('does not require involvement_instructions for public challenges', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::Public->value,
        ])->assertSessionDoesntHaveErrors(['involvement_instructions']);
    });

    test('nulls involvement_instructions when visibility does not require an invite', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->inviteToSubmit()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::Public->value,
            'involvement_instructions' => 'Left over from when it needed an invite.',
        ])->assertRedirect();

        expect($challenge->refresh()->involvement_instructions)->toBeNull();
    });

    test('updates participant_instructions for a public challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::Public->value,
            'participant_instructions' => 'Submit via the form by Friday.',
        ])->assertRedirect();

        expect($challenge->refresh()->participant_instructions)->toBe('Submit via the form by Friday.');
    });

    test('updates involvement_instructions', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::InviteToSubmit->value,
            'involvement_instructions' => 'Email us for an invite code.',
        ])->assertRedirect();

        expect($challenge->refresh()->involvement_instructions)->toBe('Email us for an invite code.');
    });

    test('allows is_featured to be true for invite to submit visibility', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'visibility' => ChallengeVisibility::InviteToSubmit->value,
            'is_featured' => true,
            'involvement_instructions' => 'Request an invite code to take part.',
        ])->assertRedirect();

        expect($challenge->refresh()->is_featured)->toBeTrue();
    });

    test('updates challenge dates', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-07-01',
        ])->assertRedirect();

        $challenge->refresh();

        expect($challenge->starts_at->format('Y-m-d'))->toBe('2026-06-01')
            ->and($challenge->ends_at->format('Y-m-d'))->toBe('2026-07-01');
    });
});

describe('active challenge constraints', function () {
    test('ignores submitted slug when challenge is active', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->active()->create(['slug' => 'original-slug']);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated Title',
            'slug' => 'new-slug',
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'is_active' => true,
        ])->assertRedirect();

        expect($challenge->refresh()->slug)->toBe('original-slug')
            ->and($challenge->title)->toBe('Updated Title');
    });

    test('does not allow deactivating an active challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->active()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
        ])->assertSessionHasErrors(['is_active']);

        expect($challenge->refresh()->is_active)->toBeTrue();
    });

    test('allows slug change when challenge is not active', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create(['slug' => 'old-slug']);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => 'new-slug',
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
        ])->assertSessionDoesntHaveErrors(['slug']);

        expect($challenge->refresh()->slug)->toBe('new-slug');
    });

    test('allows deactivating an inactive challenge', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create(['is_active' => false]);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
        ])->assertSessionDoesntHaveErrors(['is_active']);

        expect($challenge->refresh()->is_active)->toBeFalse();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), $data)
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
    ]);

    test('validates unique slug', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        Challenge::factory()->create(['slug' => 'taken-slug']);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Test',
            'slug' => 'taken-slug',
            'tagline' => 'Tagline',
            'description' => 'Description',
        ])->assertSessionHasErrors(['slug']);
    });

    test('allows keeping own slug', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create(['slug' => 'my-slug']);

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Updated Title',
            'slug' => 'my-slug',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
        ])->assertSessionDoesntHaveErrors(['slug']);
    });

    test('validates thumbnail file type', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        $file = UploadedFile::fake()->create(name: 'document.pdf', mimeType: 'application/pdf');

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Test',
            'slug' => $challenge->slug,
            'tagline' => 'Tagline',
            'description' => 'Description',
            'thumbnail' => $file,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('prohibits thumbnail when organisation is selected', function () {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();
        $organisation = Organisation::factory()->create();

        actingAs($admin);

        $thumbnail = UploadedFile::fake()->image(name: 'thumbnail.jpg', width: 400, height: 300);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Test',
            'slug' => $challenge->slug,
            'tagline' => 'Tagline',
            'description' => 'Description',
            'organisation_id' => $organisation->id,
            'thumbnail' => $thumbnail,
        ])->assertSessionHasErrors(['thumbnail']);
    });

    test('rejects crops with incorrect aspect ratios', function ($crops) {
        $admin = User::factory()->admin()->create();
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => 'Test',
            'slug' => $challenge->slug,
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
        $challenge = Challenge::factory()->create();

        actingAs($admin);

        patch(route('staff.challenges.update', $challenge), [
            'title' => $challenge->title,
            'slug' => $challenge->slug,
            'tagline' => $challenge->tagline,
            'description' => $challenge->description,
            'thumbnail' => UploadedFile::fake()->image(name: 'thumb.jpg', width: 800, height: 600),
            'thumbnail_crops' => [
                'square' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400],
                'landscape' => ['x' => 0, 'y' => 0, 'width' => 800, 'height' => 450],
            ],
        ])->assertSessionDoesntHaveErrors(['thumbnail_crops']);
    });
});
