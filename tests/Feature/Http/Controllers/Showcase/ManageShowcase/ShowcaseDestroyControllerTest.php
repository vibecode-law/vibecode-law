<?php

use App\Models\Showcase\Showcase;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\delete;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->create();

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertRedirect(route('login'));
    });

    test('allows owner to delete their showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertRedirect();
    });

    test('requires email verification', function () {
        /** @var User */
        $user = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertRedirect(route('verification.notice'));
    });

    test('allows admin to delete any showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->for($otherUser, 'user')->create();

        actingAs($admin);

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertRedirect();
    });

    test('prevents non-owner from deleting showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->for($otherUser, 'user')->create();

        actingAs($user);

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertForbidden();
    });
});

describe('showcase deletion', function () {
    test('soft deletes showcase from database', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        delete(route('showcase.manage.destroy', $showcase));

        assertSoftDeleted('showcases', [
            'id' => $showcase->id,
        ]);
    });

    test('admin can delete any showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->for($otherUser, 'user')->create();

        actingAs($admin);

        delete(route('showcase.manage.destroy', $showcase));

        assertSoftDeleted('showcases', [
            'id' => $showcase->id,
        ]);
    });
});

describe('response', function () {
    test('redirects to authenticated showcase index page', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertRedirect();
    });

    test('includes success message', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user, 'user')->create();

        actingAs($user);

        $response = delete(route('showcase.manage.destroy', $showcase));

        $response->assertSessionHas('flash.message', ['message' => 'Showcase deleted successfully.', 'type' => 'success']);
    });
});
