<?php

use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

describe('auth', function () {
    test('requires authentication', function () {
        $tag = Tag::factory()->create();

        delete(route('staff.metadata.tags.destroy', $tag))
            ->assertRedirect(route('login'));
    });

    test('allows admin to delete tags', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        actingAs($admin);

        delete(route('staff.metadata.tags.destroy', $tag))
            ->assertRedirect();
    });

    test('does not allow moderators to delete tags', function () {
        $moderator = User::factory()->moderator()->create();
        $tag = Tag::factory()->create();

        actingAs($moderator);

        delete(route('staff.metadata.tags.destroy', $tag))
            ->assertForbidden();
    });

    test('does not allow regular users to delete tags', function () {
        /** @var User */
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        actingAs($user);

        delete(route('staff.metadata.tags.destroy', $tag))
            ->assertForbidden();
    });
});

describe('destroy', function () {
    test('deletes the tag and redirects back', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        actingAs($admin);

        delete(route('staff.metadata.tags.destroy', $tag))
            ->assertRedirect();

        expect(Tag::find($tag->id))->toBeNull();
    });

    test('returns 404 for non-existent tag', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        delete(route('staff.metadata.tags.destroy', ['tag' => 99999]))
            ->assertNotFound();
    });
});
