<?php

use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('visibility', function () {
    test('returns 404 for user without public profile', function () {
        $user = User::factory()->create();

        get(route('user.show', $user))
            ->assertNotFound();
    });

    test('returns profile for team member without showcases', function () {
        $user = User::factory()->coreTeam()->create();

        get(route('user.show', $user))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('user/show')
                ->has('user')
                ->has('showcases', 0)
            );
    });

    test('returns profile for collaborator without showcases', function () {
        $user = User::factory()->collaborator()->create();

        get(route('user.show', $user))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('user/show')
                ->has('user')
                ->has('showcases', 0)
            );
    });

    test('returns profile for user with approved showcase', function () {
        $user = User::factory()->create();
        Showcase::factory()->approved()->for($user)->create();

        get(route('user.show', $user))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('user/show')
                ->has('user')
                ->has('showcases', 1)
            );
    });

    test('returns 404 for user with only draft showcase', function () {
        $user = User::factory()->create();
        Showcase::factory()->draft()->for($user)->create();

        get(route('user.show', $user))
            ->assertNotFound();
    });

    test('returns 404 for user with only pending showcase', function () {
        $user = User::factory()->create();
        Showcase::factory()->pending()->for($user)->create();

        get(route('user.show', $user))
            ->assertNotFound();
    });

    test('returns 404 for user with only rejected showcase', function () {
        $user = User::factory()->create();
        Showcase::factory()->rejected()->for($user)->create();

        get(route('user.show', $user))
            ->assertNotFound();
    });
});

test('show returns user profile for guests', function () {
    $user = User::factory()->coreTeam()->create();

    get(route('user.show', $user))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/show')
            ->has('user')
            ->has('showcases')
        );
});

test('show returns correct user data', function () {
    $user = User::factory()->coreTeam(role: 'Lead Developer')->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'handle' => 'john-doe',
        'organisation' => 'ACME Corp',
        'job_title' => 'Developer',
        'avatar_path' => 'avatars/test.jpg',
        'linkedin_url' => 'https://linkedin.com/in/johndoe',
        'bio' => 'A passionate developer.',
    ]);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/show')
            ->has('user', fn (AssertableInertia $userProp) => $userProp
                ->where('first_name', 'John')
                ->where('last_name', 'Doe')
                ->where('handle', 'john-doe')
                ->where('organisation', 'ACME Corp')
                ->where('job_title', 'Developer')
                ->where('avatar', Storage::disk('public')->url('avatars/test.jpg'))
                ->where('linkedin_url', 'https://linkedin.com/in/johndoe')
                ->where('team_role', 'Lead Developer')
                ->where('bio', 'A passionate developer.')
                ->where('bio_html', "<p>A passionate developer.</p>\n")
            )
        );
});

test('show returns null bio_html when bio is null', function () {
    $user = User::factory()->coreTeam()->create([
        'bio' => null,
    ]);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user/show')
            ->has('user', fn (AssertableInertia $userProp) => $userProp
                ->where('bio', null)
                ->where('bio_html', null)
                ->etc()
            )
        );
});

test('show returns only approved showcases', function () {
    $user = User::factory()->create();

    $approvedShowcase = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Approved Showcase',
    ]);
    Showcase::factory()->draft()->for($user)->create([
        'title' => 'Draft Showcase',
    ]);
    Showcase::factory()->pending()->for($user)->create([
        'title' => 'Pending Showcase',
    ]);
    Showcase::factory()->rejected()->for($user)->create([
        'title' => 'Rejected Showcase',
    ]);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 1)
            ->where('showcases.0.id', $approvedShowcase->id)
            ->where('showcases.0.title', 'Approved Showcase')
        );
});

test('show returns showcases ordered by upvotes descending', function () {
    $user = User::factory()->create();
    $upvoters = User::factory()->count(5)->create();

    $showcase1 = Showcase::factory()->approved()->for($user)->create([
        'title' => 'First Showcase',
        'submitted_date' => '2025-01-15',
    ]);
    $showcase1->upvoters()->attach($upvoters->take(2));

    $showcase2 = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Second Showcase',
        'submitted_date' => '2025-01-20',
    ]);
    $showcase2->upvoters()->attach($upvoters->take(5));

    $showcase3 = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Third Showcase',
        'submitted_date' => '2025-01-10',
    ]);
    $showcase3->upvoters()->attach($upvoters->take(1));

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 3)
            ->where('showcases.0.id', $showcase2->id)
            ->where('showcases.0.upvotes_count', 5)
            ->where('showcases.1.id', $showcase1->id)
            ->where('showcases.1.upvotes_count', 2)
            ->where('showcases.2.id', $showcase3->id)
            ->where('showcases.2.upvotes_count', 1)
        );
});

test('show returns correct showcase properties', function () {
    $user = User::factory()->create();

    /** @var User */
    $viewer = User::factory()->create();

    $showcase = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Test Showcase',
        'tagline' => 'A test showcase',
        'thumbnail_extension' => 'jpg',
        'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 300, 'height' => 200],
    ]);

    actingAs($viewer);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 1, fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->has('id')
                ->has('slug')
                ->where('title', 'Test Showcase')
                ->where('tagline', 'A test showcase')
                ->has('thumbnail_url')
                ->has('thumbnail_rect_string')
                ->has('upvotes_count')
                ->has('has_upvoted')
                ->has('view_count')
                ->has('user')
            )
        );
});

test('show returns has_upvoted status for authenticated users', function () {
    $user = User::factory()->create();

    /** @var User */
    $viewer = User::factory()->create();

    $showcase = Showcase::factory()->approved()->for($user)->create();
    $showcase->upvoters()->attach($viewer);

    actingAs($viewer);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.has_upvoted', true)
        );
});

test('show returns has_upvoted false when viewer has not upvoted', function () {
    $user = User::factory()->create();

    /** @var User */
    $viewer = User::factory()->create();

    Showcase::factory()->approved()->for($user)->create();
    actingAs($viewer);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.0.has_upvoted', false)
        );
});

test('show returns empty showcases for team member with no approved showcases', function () {
    $user = User::factory()->coreTeam()->create();

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 0)
        );
});

test('show returns 404 for non-existent user handle', function () {
    get(route('user.show', ['user' => 'non-existent-handle']))
        ->assertNotFound();
});

test('showcases with same upvotes are ordered by submitted_date descending', function () {
    $user = User::factory()->create();

    $showcase1 = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Older Showcase',
        'submitted_date' => '2025-01-10',
    ]);

    $showcase2 = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Newer Showcase',
        'submitted_date' => '2025-01-20',
    ]);

    get(route('user.show', $user))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 2)
            ->where('showcases.0.id', $showcase2->id)
            ->where('showcases.1.id', $showcase1->id)
        );
});
