<?php

use App\Models\Testimonial;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

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
