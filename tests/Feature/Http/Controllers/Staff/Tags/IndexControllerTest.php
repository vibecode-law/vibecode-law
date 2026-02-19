<?php

use App\Enums\TagType;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        get(route('staff.metadata.tags.index'))
            ->assertRedirect(route('login'));
    });

    test('allows admin to view the tags list', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.metadata.tags.index'))
            ->assertOk();
    });

    test('does not allow moderators to view tags', function () {
        $moderator = User::factory()->moderator()->create();

        actingAs($moderator);

        get(route('staff.metadata.tags.index'))
            ->assertForbidden();
    });

    test('does not allow regular users to view tags', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('staff.metadata.tags.index'))
            ->assertForbidden();
    });
});

describe('data', function () {
    test('returns all tags', function () {
        $admin = User::factory()->admin()->create();
        Tag::factory()->count(5)->create();

        actingAs($admin);

        get(route('staff.metadata.tags.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/tags/index', shouldExist: false)
                ->has('tags', 5)
            );
    });

    test('returns tags with correct structure and values', function () {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create([
            'name' => 'React',
            'slug' => 'react',
            'type' => TagType::TechStack,
        ]);

        actingAs($admin);

        get(route('staff.metadata.tags.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('staff-area/tags/index', shouldExist: false)
                ->has('tags.0', fn (AssertableInertia $data) => $data
                    ->where('id', $tag->id)
                    ->where('name', 'React')
                    ->where('slug', 'react')
                    ->where('type.value', (string) TagType::TechStack->value)
                    ->where('type.label', 'Tech Stack')
                    ->where('type.name', 'TechStack')
                )
            );
    });

    test('returns tag types', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        get(route('staff.metadata.tags.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('tagTypes', count(TagType::cases()))
                ->where('tagTypes.0.label', 'Tool')
            );
    });

    test('orders tags by type then name', function () {
        $admin = User::factory()->admin()->create();
        $tagB = Tag::factory()->create(['name' => 'Zebra', 'type' => TagType::Tool]);
        $tagA = Tag::factory()->create(['name' => 'Alpha', 'type' => TagType::Tool]);
        $tagC = Tag::factory()->create(['name' => 'Beta', 'type' => TagType::Skill]);

        actingAs($admin);

        get(route('staff.metadata.tags.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('tags.0.id', $tagA->id)
                ->where('tags.1.id', $tagB->id)
                ->where('tags.2.id', $tagC->id)
            );
    });
});
