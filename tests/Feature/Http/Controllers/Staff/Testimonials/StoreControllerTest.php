<?php

use App\Models\Testimonial;
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
        post(route('staff.testimonials.store'), [
            'content' => 'A great testimonial.',
        ])->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.testimonials.store'), [
            'content' => 'A great testimonial.',
        ])->assertForbidden();
    });

    test('allows moderators to store testimonials', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), [
            'name' => 'Jane Doe',
            'content' => 'A great testimonial.',
        ])->assertRedirect();
    });
});

describe('storing', function () {
    test('creates a testimonial with all fields', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), [
            'user_id' => $user->id,
            'name' => 'Jane Doe',
            'job_title' => 'Lawyer',
            'organisation' => 'Acme Corp',
            'content' => 'A great testimonial.',
            'is_published' => true,
            'display_order' => 3,
        ])->assertRedirect();

        $testimonial = Testimonial::latest('id')->first();

        expect($testimonial->user_id)->toBe($user->id)
            ->and($testimonial->name)->toBe('Jane Doe')
            ->and($testimonial->job_title)->toBe('Lawyer')
            ->and($testimonial->organisation)->toBe('Acme Corp')
            ->and($testimonial->content)->toBe('A great testimonial.')
            ->and($testimonial->is_published)->toBeTrue()
            ->and($testimonial->display_order)->toBe(3);
    });

    test('creates a testimonial with only required fields', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), [
            'content' => 'Minimal testimonial.',
        ])->assertRedirect();

        $testimonial = Testimonial::latest('id')->first();

        expect($testimonial->content)->toBe('Minimal testimonial.')
            ->and($testimonial->user_id)->toBeNull()
            ->and($testimonial->name)->toBeNull()
            ->and($testimonial->job_title)->toBeNull()
            ->and($testimonial->organisation)->toBeNull();
    });

    test('handles avatar upload', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), [
            'content' => 'With avatar.',
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
        ])->assertRedirect();

        $testimonial = Testimonial::latest('id')->first();

        expect($testimonial->avatar_path)->toStartWith('testimonials/avatars/')
            ->and($testimonial->avatar_path)->toEndWith('.jpg');
        Storage::disk('public')->assertExists($testimonial->avatar_path);
    });
});

describe('validation', function () {
    test('validates required and invalid data', function (array $data, array $invalidFields) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), $data)
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
        'job_title max 255 characters' => [
            ['content' => 'Valid', 'job_title' => str_repeat('a', 256)],
            ['job_title'],
        ],
        'organisation max 255 characters' => [
            ['content' => 'Valid', 'organisation' => str_repeat('a', 256)],
            ['organisation'],
        ],
        'user_id must exist' => [
            ['content' => 'Valid', 'user_id' => 99999],
            ['user_id'],
        ],
        'display_order must be integer' => [
            ['content' => 'Valid', 'display_order' => 'abc'],
            ['display_order'],
        ],
        'display_order min 0' => [
            ['content' => 'Valid', 'display_order' => -1],
            ['display_order'],
        ],
        'avatar must be an image' => [
            ['content' => 'Valid', 'avatar' => UploadedFile::fake()->create('document.pdf', 100)],
            ['avatar'],
        ],
        'avatar max 2MB' => [
            ['content' => 'Valid', 'avatar' => UploadedFile::fake()->image('large.jpg')->size(3000)],
            ['avatar'],
        ],
    ]);

    test('rejects invalid avatar file types', function (string $extension) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), [
            'content' => 'Valid content',
            'avatar' => UploadedFile::fake()->create("file.{$extension}", 100),
        ])->assertInvalid(['avatar']);
    })->with([
        'svg',
        'bmp',
        'tiff',
    ]);

    test('accepts valid avatar file types', function (string $extension) {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.testimonials.store'), [
            'content' => 'Valid content',
            'avatar' => UploadedFile::fake()->image("avatar.{$extension}", 100, 100),
        ])->assertSessionHasNoErrors();
    })->with([
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
    ]);
});
