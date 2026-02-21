<?php

use App\Enums\TagType;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        post(route('staff.metadata.tags.store'), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertRedirect(route('login'));
    });

    test('allows admin to create tags', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.metadata.tags.store'), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertRedirect();
    });

    test('does not allow moderators to create tags', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        post(route('staff.metadata.tags.store'), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertForbidden();
    });

    test('does not allow regular users to create tags', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        post(route('staff.metadata.tags.store'), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertForbidden();
    });
});

describe('store', function () {
    test('creates a new tag and redirects back', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.metadata.tags.store'), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertRedirect();

        $tag = Tag::query()->where('name', 'React')->firstOrFail();

        expect($tag->name)->toBe('React')
            ->and($tag->slug)->toBe('react')
            ->and($tag->type)->toBe(TagType::TechStack);
    });

    test('auto-generates slug from name', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.metadata.tags.store'), [
            'name' => 'Claude Code',
            'type' => TagType::Tool->value,
        ])->assertRedirect();

        $tag = Tag::query()->where('name', 'Claude Code')->firstOrFail();

        expect($tag->slug)->toBe('claude-code');
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        post(route('staff.metadata.tags.store'), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing name' => [
            ['type' => TagType::Tool->value],
            ['name'],
        ],
        'missing type' => [
            ['name' => 'React'],
            ['type'],
        ],
        'name too long' => [
            ['name' => str_repeat('a', 256), 'type' => TagType::Tool->value],
            ['name'],
        ],
        'invalid type' => [
            ['name' => 'React', 'type' => 999],
            ['type'],
        ],
    ]);

    test('validates unique name', function () {
        $admin = User::factory()->admin()->create();
        Tag::factory()->create(['name' => 'React']);

        actingAs($admin);

        post(route('staff.metadata.tags.store'), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertSessionHasErrors(['name']);
    });
});
