<?php

use App\Models\Testimonial;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\put;

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
