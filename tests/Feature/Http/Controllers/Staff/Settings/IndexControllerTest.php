<?php

use App\Models\SiteSetting;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.settings.index'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view settings', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.settings.index'))
            ->assertOk();
    });

    test('does not allow moderators to view settings', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.settings.index'))
            ->assertForbidden();
    });

    test('does not allow regular users to view settings', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.settings.index'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns null announcement markdown when none is set', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.settings.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/settings/index', shouldExist: false)
                ->where('announcementMarkdown', null)
            );
    });

    test('returns the raw announcement markdown when one is set', function () {
        $admin = User::factory()->admin()->create();
        SiteSetting::factory()->announcement(value: 'Hello **world**')->create();

        actingAs($admin);

        get(route('staff.settings.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/settings/index', shouldExist: false)
                ->where('announcementMarkdown', 'Hello **world**')
            );
    });
});
