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

    test('allows a user with testimonial.delete permission', function () {
        $user = userWithPermissions(['testimonial.view', 'testimonial.delete']);
        $testimonial = Testimonial::factory()->create();

        actingAs($user);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();
    });

    test('forbids a staff user without testimonial.delete permission', function () {
        $user = userWithPermissions(['testimonial.view']);
        $testimonial = Testimonial::factory()->create();

        actingAs($user);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertForbidden();
    });
});

describe('destroying', function () {
    test('deletes the testimonial from the database', function () {
        $marketingManager = User::factory()->marketingManager()->create();
        $testimonial = Testimonial::factory()->create();

        actingAs($marketingManager);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();

        assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    });

    test('deletes the avatar file via model event when testimonial has avatar', function () {
        $marketingManager = User::factory()->marketingManager()->create();
        $avatarPath = 'testimonials/avatars/test-avatar.jpg';

        Storage::disk('public')->put($avatarPath, 'avatar content');

        $testimonial = Testimonial::factory()->create([
            'avatar_path' => $avatarPath,
        ]);

        actingAs($marketingManager);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($avatarPath);
        assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    });

    test('does not error when deleting testimonial without avatar', function () {
        $marketingManager = User::factory()->marketingManager()->create();
        $testimonial = Testimonial::factory()->create([
            'avatar_path' => null,
        ]);

        actingAs($marketingManager);

        delete(route('staff.testimonials.destroy', $testimonial))
            ->assertRedirect();

        assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    });
});
