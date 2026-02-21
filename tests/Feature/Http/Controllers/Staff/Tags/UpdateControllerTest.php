<?php

use App\Enums\TagType;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $tag = Tag::factory()->create();

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated',
            'type' => TagType::Tool->value,
        ])->assertRedirect(route('login'));
    });

    test('allows admin to update tags', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated',
            'type' => TagType::Tool->value,
        ])->assertRedirect();
    });

    test('does not allow moderators to update tags', function () {
        $moderator = User::factory()->moderator()->create();
        $tag = Tag::factory()->create();

        actingAs($moderator);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated',
            'type' => TagType::Tool->value,
        ])->assertForbidden();
    });

    test('does not allow regular users to update tags', function () {
        /** @var User */
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        actingAs($user);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated',
            'type' => TagType::Tool->value,
        ])->assertForbidden();
    });
});

describe('update', function () {
    test('updates a tag and redirects back', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create([
            'name' => 'Old Name',
            'type' => TagType::Tool,
        ]);

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'New Name',
            'type' => TagType::Skill->value,
        ])->assertRedirect();

        $tag->refresh();

        expect($tag->name)->toBe('New Name')
            ->and($tag->slug)->toBe('new-name')
            ->and($tag->type)->toBe(TagType::Skill);
    });

    test('regenerates slug when name changes and tag has no content', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create([
            'name' => 'Original',
            'slug' => 'original',
        ]);

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated Tag Name',
            'type' => $tag->type->value,
        ])->assertRedirect();

        $tag->refresh();

        expect($tag->slug)->toBe('updated-tag-name');
    });

    test('does not update slug when tag has courses', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create([
            'name' => 'Original',
            'slug' => 'original',
        ]);
        $tag->courses()->attach(Course::factory()->create());

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated Tag Name',
            'type' => $tag->type->value,
        ])->assertRedirect();

        $tag->refresh();

        expect($tag->name)->toBe('Updated Tag Name')
            ->and($tag->slug)->toBe('original');
    });

    test('does not update slug when tag has lessons', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create([
            'name' => 'Original',
            'slug' => 'original',
        ]);
        $tag->lessons()->attach(Lesson::factory()->create());

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'Updated Tag Name',
            'type' => $tag->type->value,
        ])->assertRedirect();

        $tag->refresh();

        expect($tag->name)->toBe('Updated Tag Name')
            ->and($tag->slug)->toBe('original');
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), $data)
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

    test('validates unique name ignoring current tag', function () {
        $admin = User::factory()->admin()->create();
        Tag::factory()->create(['name' => 'React']);
        $tag = Tag::factory()->create(['name' => 'Vue']);

        actingAs($admin);

        // Should fail - name taken by another tag
        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'React',
            'type' => TagType::TechStack->value,
        ])->assertSessionHasErrors(['name']);
    });

    test('allows keeping the same name on update', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create(['name' => 'React']);

        actingAs($admin);

        patch(route('staff.metadata.tags.update', $tag), [
            'name' => 'React',
            'type' => TagType::Skill->value,
        ])->assertRedirect()
            ->assertSessionDoesntHaveErrors();
    });
});
