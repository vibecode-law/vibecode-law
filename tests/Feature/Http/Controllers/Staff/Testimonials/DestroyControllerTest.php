<?php

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $testimonial = Testimonial::factory()->create();

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect(route('login'));
    });

    test('forbids regular users', function () {
        /** @var User */
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($user);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertForbidden();
    });

    test('allows moderators to delete testimonials', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();
    });
});

describe('destroying', function () {
    test('deletes the testimonial from the database', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($moderator);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();

        assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    });

    test('deletes the avatar file via model event when testimonial has avatar', function () {
        $moderator = User::factory()->moderator()->create();
        $avatarPath = 'testimonials/avatars/test-avatar.jpg';

        Storage::disk('public')->put($avatarPath, 'avatar content');

        $testimonial = Testimonial::factory()->create([
            'avatar_path' => $avatarPath,
        ]);

        actingAs($moderator);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($avatarPath);
        assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    });

    test('does not error when deleting testimonial without avatar', function () {
        $moderator = User::factory()->moderator()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => null,
        ]);

        actingAs($moderator);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();

        assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    });
});
