<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

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
