<?php

use App\Models\Testimonial;
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
        $testimonial = Testimonial::factory()->create();

        put(route('staff.testimonials.update', $testimonial), [
            'content' => 'Updated content.',
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($user);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => 'Updated content.',
        ])->assertForbidden();
    });

    test('allows moderators to update testimonials', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => 'Updated content.',
        ])->assertRedirect();
    });
});

describe('updating', function () {
    test('updates testimonial fields', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->create([
            'content' => 'Original content.',
        ]);

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'user_id' => $user->id,
            'name' => 'Updated Name',
            'job_title' => 'Updated Title',
            'organisation' => 'Updated Org',
            'content' => 'Updated content.',
            'is_published' => true,
            'display_order' => 7,
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->user_id)->toBe($user->id)
            ->and($testimonial->name)->toBe('Updated Name')
            ->and($testimonial->job_title)->toBe('Updated Title')
            ->and($testimonial->organisation)->toBe('Updated Org')
            ->and($testimonial->content)->toBe('Updated content.')
            ->and($testimonial->is_published)->toBeTrue()
            ->and($testimonial->display_order)->toBe(7);
    });

    test('handles avatar upload', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => $testimonial->content,
            'avatar' => UploadedFile::fake()->image('avatar.png', 100, 100),
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->avatar_path)->toStartWith('testimonials/avatars/');
        Storage::disk('public')->assertExists($testimonial->avatar_path);
    });

    test('deletes old avatar when uploading new one', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => 'testimonials/avatars/old-avatar.jpg',
        ]);
        Storage::disk('public')->put('testimonials/avatars/old-avatar.jpg', 'old content');

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => $testimonial->content,
            'avatar' => UploadedFile::fake()->image('new-avatar.jpg', 100, 100),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing('testimonials/avatars/old-avatar.jpg');
        Storage::disk('public')->assertExists($testimonial->refresh()->avatar_path);
    });

    test('removes avatar when remove_avatar is true', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => 'testimonials/avatars/existing.jpg',
        ]);
        Storage::disk('public')->put('testimonials/avatars/existing.jpg', 'content');

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => $testimonial->content,
            'remove_avatar' => true,
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->avatar_path)->toBeNull();
        Storage::disk('public')->assertMissing('testimonials/avatars/existing.jpg');
    });

    test('handles avatar upload with crop data', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => $testimonial->content,
            'avatar' => UploadedFile::fake()->image('avatar.png', 200, 200),
            'avatar_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->avatar_path)->toStartWith('testimonials/avatars/')
            ->and($testimonial->avatar_crop)->toBe(['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100]);
        Storage::disk('public')->assertExists($testimonial->avatar_path);
    });

    test('updates crop data without replacing avatar file', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => 'testimonials/avatars/existing.jpg',
            'avatar_crop' => ['x' => 0, 'y' => 0, 'width' => 50, 'height' => 50],
        ]);
        Storage::disk('public')->put('testimonials/avatars/existing.jpg', 'content');

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => $testimonial->content,
            'avatar_crop' => ['x' => 15, 'y' => 25, 'width' => 80, 'height' => 80],
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->avatar_path)->toBe('testimonials/avatars/existing.jpg')
            ->and($testimonial->avatar_crop)->toBe(['x' => 15, 'y' => 25, 'width' => 80, 'height' => 80]);
        Storage::disk('public')->assertExists('testimonials/avatars/existing.jpg');
    });

    test('clears crop data when avatar is removed', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => 'testimonials/avatars/existing.jpg',
            'avatar_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
        ]);
        Storage::disk('public')->put('testimonials/avatars/existing.jpg', 'content');

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => $testimonial->content,
            'remove_avatar' => true,
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->avatar_path)->toBeNull()
            ->and($testimonial->avatar_crop)->toBeNull();
    });

    test('preserves avatar when updating without avatar changes', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => 'testimonials/avatars/existing.jpg',
        ]);
        Storage::disk('public')->put('testimonials/avatars/existing.jpg', 'content');

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), [
            'content' => 'Updated content only.',
        ])->assertRedirect();

        $testimonial->refresh();

        expect($testimonial->avatar_path)->toBe('testimonials/avatars/existing.jpg');
        Storage::disk('public')->assertExists('testimonials/avatars/existing.jpg');
    });
});

describe('validation', function () {
    test('validates required and invalid data', function (array $data, array $invalidFields) {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        put(route('staff.testimonials.update', $testimonial), $data)
            ->assertInvalid($invalidFields);
    })->with([
        'content is required' => [
            [],
            ['content'],
        ],
        'content must be a string' => [
            ['content' => 123],
            ['content'],
        ],
        'content max 1000 characters' => [
            ['content' => str_repeat('a', 1001)],
            ['content'],
        ],
        'name max 255 characters' => [
            ['content' => 'Valid', 'name' => str_repeat('a', 256)],
            ['name'],
        ],
        'user_id must exist' => [
            ['content' => 'Valid', 'user_id' => 99999],
            ['user_id'],
        ],
        'display_order min 0' => [
            ['content' => 'Valid', 'display_order' => -1],
            ['display_order'],
        ],
        'avatar must be an image' => [
            ['content' => 'Valid', 'avatar' => UploadedFile::fake()->create('document.pdf', 100)],
            ['avatar'],
        ],
    ]);
});
