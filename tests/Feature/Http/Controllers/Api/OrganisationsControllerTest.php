<?php

use App\Models\Organisation\Organisation;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('api.organisations'))
            ->assertRedirect(route('login'));
    });

    test('requires email verification', function () {
        $user = User::factory()->unverified()->admin()->create();

        actingAs($user);

        get(route('api.organisations'))
            ->assertRedirect(route('verification.notice'));
    });

    test('requires access-staff permission', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('api.organisations'))
            ->assertForbidden();
    });

    test('allows admin to access', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('api.organisations'))
            ->assertOk();
    });

    test('allows moderator to access', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('api.organisations'))
            ->assertOk();
    });
});

describe('data', function () {
    test('returns organisations as json with correct structure', function () {
        $admin = User::factory()->admin()->create();
        $organisation = Organisation::factory()->create(['name' => 'Acme Corp']);

        actingAs($admin);

        get(route('api.organisations'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $organisation->id,
                'name' => 'Acme Corp',
            ]);
    });

    test('returns organisations ordered by name', function () {
        $admin = User::factory()->admin()->create();
        Organisation::factory()->create(['name' => 'Zeta Corp']);
        Organisation::factory()->create(['name' => 'Alpha Inc']);

        actingAs($admin);

        get(route('api.organisations'))
            ->assertOk()
            ->assertJsonPath('0.name', 'Alpha Inc')
            ->assertJsonPath('1.name', 'Zeta Corp');
    });

    test('limits results to 50', function () {
        $admin = User::factory()->admin()->create();
        Organisation::factory()->count(55)->create();

        actingAs($admin);

        get(route('api.organisations'))
            ->assertOk()
            ->assertJsonCount(50);
    });

    test('searches organisations by name', function () {
        $admin = User::factory()->admin()->create();
        Organisation::factory()->create(['name' => 'Acme Corp']);
        Organisation::factory()->create(['name' => 'Beta Inc']);

        actingAs($admin);

        get(route('api.organisations', ['search' => 'Acme']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Acme Corp']);
    });

    test('returns empty array when no search matches', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('api.organisations', ['search' => 'nonexistent-org-xyz']))
            ->assertOk()
            ->assertJsonCount(0);
    });
});
