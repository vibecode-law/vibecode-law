<?php

use App\Enums\ExperienceLevel;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.academy.courses.create'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the create form', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.academy.courses.create'))
            ->assertOk();
    });

    test('does not allow moderators to create courses', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.academy.courses.create'))
            ->assertForbidden();
    });

    test('does not allow regular users to create courses', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.academy.courses.create'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns experience levels', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.academy.courses.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/courses/create', shouldExist: false)
                ->has('experienceLevels', count(ExperienceLevel::cases()))
                ->where('experienceLevels.0.value', (string) ExperienceLevel::Foundation->value)
                ->where('experienceLevels.0.label', ExperienceLevel::Foundation->label())
            );
    });
});
