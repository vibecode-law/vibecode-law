<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('api.users'))
            ->assertRedirect(route('login'));
    });

    test('requires email verification', function () {
        $user = User::factory()->unverified()->admin()->create();

        actingAs($user);

        get(route('api.users'))
            ->assertRedirect(route('verification.notice'));
    });

    test('requires access-staff permission', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('api.users'))
            ->assertForbidden();
    });

    test('allows admin to access', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('api.users'))
            ->assertOk();
    });

    test('allows moderator to access', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('api.users'))
            ->assertOk();
    });
});

describe('data', function () {
    test('returns users as json with correct structure', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@example.com',
            'job_title' => 'Lawyer',
            'organisation' => 'Smith & Co',
        ]);

        actingAs($admin);

        get(route('api.users'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $user->id,
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'job_title' => 'Lawyer',
                'organisation' => 'Smith & Co',
            ]);
    });

    test('limits results to 50', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->count(55)->create();

        actingAs($admin);

        get(route('api.users'))
            ->assertOk()
            ->assertJsonCount(50);
    });

    test('searches users by first name', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['first_name' => 'Xanderbert']);
        User::factory()->create(['first_name' => 'Jane']);

        actingAs($admin);

        get(route('api.users', ['search' => 'Xanderbert']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Xanderbert '.User::where('first_name', 'Xanderbert')->first()->last_name]);
    });

    test('searches users by email', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'unique-searchable@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        actingAs($admin);

        get(route('api.users', ['search' => 'unique-searchable']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['email' => 'unique-searchable@example.com']);
    });

    test('returns empty array when no search matches', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('api.users', ['search' => 'nonexistent-user-xyz']))
            ->assertOk()
            ->assertJsonCount(0);
    });
});
