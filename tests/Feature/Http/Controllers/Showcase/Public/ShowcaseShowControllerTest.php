<?php

use App\Enums\ShowcaseStatus;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('show returns approved showcase for guests', function () {
    $showcase = Showcase::factory()->approved()->create();

    $response = get(route('showcase.show', $showcase));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('showcase/public/show')
        ->has('showcase')
    );
});

test('show increments view count', function () {
    $showcase = Showcase::factory()->approved()->create([
        'view_count' => 10,
    ]);

    get(route('showcase.show', $showcase));

    expect($showcase->fresh()->view_count)->toBe(11);
});

test('show returns correct showcase data', function () {
    $user = User::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'organisation' => 'Test Org',
        'job_title' => 'Developer',
        'avatar_path' => 'avatars/test.jpg',
        'linkedin_url' => 'https://linkedin.com/in/testuser',
        'bio' => 'Test user is a developer at Test Org.',
    ]);

    $showcase = Showcase::factory()->approved()->for($user)->create([
        'title' => 'Test Showcase',
        'tagline' => 'A compelling test tagline',
        'description' => 'Test Description',
        'key_features' => 'Key features content',
        'help_needed' => 'Help needed content',
        'url' => 'https://example.com',
        'video_url' => 'https://youtube.com/watch?v=dQw4w9WgXcQ',
        'source_status' => \App\Enums\SourceStatus::NotAvailable,
        'source_url' => null,
        'view_count' => 0,
        'is_featured' => true,
        'thumbnail_extension' => 'jpg',
        'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 300, 'height' => 200],
    ]);

    $image1 = ShowcaseImage::factory()->for($showcase)->create([
        'path' => "showcase/{$showcase->id}/images/test1.jpg",
        'filename' => 'test1.jpg',
        'order' => 0,
        'alt_text' => 'Test image 1',
    ]);

    $image2 = ShowcaseImage::factory()->for($showcase)->create([
        'path' => "showcase/{$showcase->id}/images/test2.jpg",
        'filename' => 'test2.jpg',
        'order' => 1,
        'alt_text' => 'Test image 2',
    ]);

    $image3 = ShowcaseImage::factory()->for($showcase)->create([
        'path' => "showcase/{$showcase->id}/images/test3.jpg",
        'filename' => 'test3.jpg',
        'order' => 2,
        'alt_text' => 'Test image 3',
    ]);

    $response = get(route('showcase.show', $showcase));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('showcase/public/show')
        ->has('showcase', fn (AssertableInertia $showcaseProp) => $showcaseProp
            ->where('id', $showcase->id)
            ->where('slug', $showcase->slug)
            ->where('title', 'Test Showcase')
            ->where('tagline', 'A compelling test tagline')
            ->where('description', 'Test Description')
            ->where('description_html', "<p>Test Description</p>\n")
            ->missing('key_features')
            ->where('key_features_html', "<p>Key features content</p>\n")
            ->missing('help_needed')
            ->where('help_needed_html', "<p>Help needed content</p>\n")
            ->where('url', 'https://example.com')
            ->where('video_url', 'https://youtube.com/watch?v=dQw4w9WgXcQ')
            ->where('youtube_id', 'dQw4w9WgXcQ')
            ->where('source_status', \App\Enums\SourceStatus::NotAvailable->forFrontend())
            ->where('source_url', null)
            ->where('thumbnail_url', \Illuminate\Support\Facades\Storage::disk('public')->url("showcase/{$showcase->id}/thumbnail.jpg"))
            ->where('thumbnail_rect_string', 'rect=10,20,300,200')
            ->where('view_count', 1) // Incremented by viewing
            ->where('status', ShowcaseStatus::Approved->forFrontend())
            ->has('user', fn (AssertableInertia $userProp) => $userProp
                ->where('first_name', 'Test')
                ->where('last_name', 'User')
                ->where('handle', $user->handle)
                ->where('organisation', 'Test Org')
                ->where('job_title', 'Developer')
                ->where('avatar', \Illuminate\Support\Facades\Storage::disk('public')->url('avatars/test.jpg'))
                ->where('linkedin_url', 'https://linkedin.com/in/testuser')
                ->where('team_role', null)
                ->missing('bio')
                ->where('bio_html', "<p>Test user is a developer at Test Org.</p>\n")
            )
            ->has('images', 3, fn (AssertableInertia $imageProp) => $imageProp
                ->has('id')
                ->has('filename')
                ->has('order')
                ->has('alt_text')
                ->has('url')
            )
            ->where('images.0.id', $image1->id)
            ->where('images.0.filename', 'test1.jpg')
            ->where('images.0.order', 0)
            ->where('images.0.alt_text', 'Test image 1')
            ->where('images.0.url', $image1->url)
            ->where('images.1.id', $image2->id)
            ->where('images.1.filename', 'test2.jpg')
            ->where('images.1.order', 1)
            ->where('images.1.alt_text', 'Test image 2')
            ->where('images.1.url', $image2->url)
            ->where('images.2.id', $image3->id)
            ->where('images.2.filename', 'test3.jpg')
            ->where('images.2.order', 2)
            ->where('images.2.alt_text', 'Test image 3')
            ->where('images.2.url', $image3->url)
            ->where('upvotes_count', 0)
            ->has('practiceAreas', 1, fn (AssertableInertia $paProp) => $paProp
                ->has('id')
                ->has('name')
                ->has('slug')
            )
            ->has('submitted_date')
            ->has('created_at')
            ->has('updated_at')
            ->has('linkedin_share_url')
        )
    );
});

test('youtube_id is null when video_url is null', function () {
    $showcase = Showcase::factory()->approved()->create([
        'video_url' => null,
    ]);

    get(route('showcase.show', $showcase))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/public/show')
            ->where('showcase.video_url', null)
            ->where('showcase.youtube_id', null)
        );
});

test('youtube_id is null when video_url is not a valid youtube url', function () {
    $showcase = Showcase::factory()->approved()->create([
        'video_url' => 'https://vimeo.com/123456789',
    ]);

    get(route('showcase.show', $showcase))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/public/show')
            ->where('showcase.video_url', 'https://vimeo.com/123456789')
            ->where('showcase.youtube_id', null)
        );
});

test('show returns null for key_features_html and help_needed_html when fields are null', function () {
    $showcase = Showcase::factory()->approved()->create([
        'key_features' => null,
        'help_needed' => null,
    ]);

    $response = get(route('showcase.show', $showcase));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('showcase/public/show')
        ->has('showcase', fn (AssertableInertia $showcaseProp) => $showcaseProp
            ->missing('key_features')
            ->where('key_features_html', null)
            ->missing('help_needed')
            ->where('help_needed_html', null)
            ->etc()
        )
    );
});

test('show eager loads user and images relationships', function () {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->approved()->for($user)->create();
    ShowcaseImage::factory()->count(3)->for($showcase)->create();

    $response = get(route('showcase.show', $showcase));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->has('showcase.user', fn (AssertableInertia $userProp) => $userProp
            ->where('first_name', $user->first_name)
            ->etc()
        )
        ->has('showcase.images', 3)
    );
});

test('show loads and returns practice areas', function () {
    $showcase = Showcase::factory()->approved()->create();

    $practiceArea1 = \App\Models\PracticeArea::factory()->create(['name' => 'AI & Machine Learning']);
    $practiceArea2 = \App\Models\PracticeArea::factory()->create(['name' => 'Web Development']);

    // Sync to replace any factory-attached practice areas with our specific ones
    $showcase->practiceAreas()->sync([$practiceArea1->id, $practiceArea2->id]);

    get(route('showcase.show', $showcase))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcase.practiceAreas', 2)
            ->where('showcase.practiceAreas.0.id', $practiceArea1->id)
            ->where('showcase.practiceAreas.0.name', 'AI & Machine Learning')
            ->where('showcase.practiceAreas.0.slug', $practiceArea1->slug)
            ->where('showcase.practiceAreas.1.id', $practiceArea2->id)
            ->where('showcase.practiceAreas.1.name', 'Web Development')
            ->where('showcase.practiceAreas.1.slug', $practiceArea2->slug)
        );
});

test('show returns approved showcase for authenticated users', function () {
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->approved()->create();

    actingAs($user);

    $response = get(route('showcase.show', $showcase));

    $response->assertOk();
});

test('view count is not incremented for non-approved showcases', function () {
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->draft()->for($user)->create([
        'view_count' => 0,
    ]);

    actingAs($user);

    get(route('showcase.show', $showcase));

    expect($showcase->fresh()->view_count)->toBe(0);
});

test('view_count is visible to owner', function () {
    /** @var User */
    $user = User::factory()->create();

    $showcase = Showcase::factory()->approved()->for($user)->create([
        'view_count' => 5,
    ]);

    actingAs($user);

    get(route('showcase.show', $showcase))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcase.view_count', 6) // Incremented by viewing
        );
});

test('view_count is visible to admin', function () {
    /** @var User */
    $admin = User::factory()->admin()->create();

    $showcase = Showcase::factory()->approved()->create([
        'view_count' => 10,
    ]);

    actingAs($admin);

    get(route('showcase.show', $showcase))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcase.view_count', 11) // Incremented by viewing
        );
});

test('view_count is visible to all users', function () {
    /** @var User */
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $showcase = Showcase::factory()->approved()->for($otherUser)->create([
        'view_count' => 5,
    ]);

    actingAs($user);

    get(route('showcase.show', $showcase))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcase.view_count', 6) // Incremented by viewing
        );
});

describe('auth', function () {
    test('show returns 404 for draft showcase when user is guest', function () {
        $showcase = Showcase::factory()->draft()->create();

        $response = get(route('showcase.show', $showcase));

        $response->assertNotFound();
    });

    test('show returns 404 for pending showcase when user is guest', function () {
        $showcase = Showcase::factory()->pending()->create();

        $response = get(route('showcase.show', $showcase));

        $response->assertNotFound();
    });

    test('show returns 404 for rejected showcase when user is guest', function () {
        $showcase = Showcase::factory()->rejected()->create();

        $response = get(route('showcase.show', $showcase));

        $response->assertNotFound();
    });

    test('show returns 404 for draft showcase when user is not owner or admin', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->draft()->create();

        actingAs($user);

        $response = get(route('showcase.show', $showcase));

        $response->assertNotFound();
    });

    test('show returns 404 for pending showcase when user is not owner or admin', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->pending()->create();

        actingAs($user);

        $response = get(route('showcase.show', $showcase));

        $response->assertNotFound();
    });

    test('show returns 404 for rejected showcase when user is not owner or admin', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->rejected()->create();

        actingAs($user);

        $response = get(route('showcase.show', $showcase));

        $response->assertNotFound();
    });

    test('owner can view their own draft showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->draft()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.show', $showcase));

        $response->assertOk();
    });

    test('owner can view their own pending showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->pending()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.show', $showcase));

        $response->assertOk();
    });

    test('owner can view their own rejected showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->rejected()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.show', $showcase));

        $response->assertOk();
    });

    test('admin can view any draft showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();

        $showcase = Showcase::factory()->draft()->create();

        actingAs($admin);

        $response = get(route('showcase.show', $showcase));

        $response->assertOk();
    });

    test('admin can view any pending showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();

        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = get(route('showcase.show', $showcase));

        $response->assertOk();
    });

    test('admin can view any rejected showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();

        $showcase = Showcase::factory()->rejected()->create();

        actingAs($admin);

        $response = get(route('showcase.show', $showcase));

        $response->assertOk();
    });
});

describe('ranks', function () {
    test('lifetime rank is calculated based on upvotes across all approved showcases', function () {
        // Create upvoters
        $upvoters = User::factory()->count(5)->create();

        // Create showcases with different upvote counts
        $showcase1 = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
        $showcase1->upvoters()->attach($upvoters->take(5)); // 5 upvotes - Rank 1

        $showcase2 = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-20']);
        $showcase2->upvoters()->attach($upvoters->take(3)); // 3 upvotes - Rank 2

        $showcase3 = Showcase::factory()->approved()->create(['submitted_date' => '2025-02-10']);
        $showcase3->upvoters()->attach($upvoters->take(1)); // 1 upvote - Rank 3

        // Test showcase1 has lifetime rank 1
        get(route('showcase.show', $showcase1))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lifetimeRank', 1)
            );

        // Test showcase2 has lifetime rank 2
        get(route('showcase.show', $showcase2))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lifetimeRank', 2)
            );

        // Test showcase3 has lifetime rank 3
        get(route('showcase.show', $showcase3))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lifetimeRank', 3)
            );
    });

    test('monthly rank is calculated based on upvotes within the same submission month', function () {
        // Create upvoters
        $upvoters = User::factory()->count(5)->create();

        // Create showcases submitted in January 2025
        $januaryShowcase1 = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
        $januaryShowcase1->upvoters()->attach($upvoters->take(5)); // 5 upvotes - Jan Rank 1

        $januaryShowcase2 = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-20']);
        $januaryShowcase2->upvoters()->attach($upvoters->take(2)); // 2 upvotes - Jan Rank 2

        // Create showcase submitted in February 2025
        $februaryShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-02-10']);
        $februaryShowcase->upvoters()->attach($upvoters->take(3)); // 3 upvotes - Feb Rank 1

        // Test January showcase monthly ranks
        get(route('showcase.show', $januaryShowcase1))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('monthlyRank', 1)
            );

        get(route('showcase.show', $januaryShowcase2))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('monthlyRank', 2)
            );

        // Test February showcase has monthly rank 1 (only showcase in Feb)
        get(route('showcase.show', $februaryShowcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('monthlyRank', 1)
            );
    });

    test('monthly rank is null when submitted_date is null', function () {
        $showcase = Showcase::factory()->approved()->create(['submitted_date' => null]);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('monthlyRank', null)
            );
    });

    test('showcases with same upvotes have consistent ranking', function () {
        // Create two showcases with zero upvotes submitted in the same month
        $showcase1 = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
        $showcase2 = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-20']);

        // Both should have valid ranks (either 1 or 2)
        get(route('showcase.show', $showcase1))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('lifetimeRank')
                ->has('monthlyRank')
            );

        get(route('showcase.show', $showcase2))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('lifetimeRank')
                ->has('monthlyRank')
            );
    });

    test('draft showcases are excluded from rank calculations', function () {
        $upvoters = User::factory()->count(5)->create();

        // Create approved showcase with 2 upvotes
        $approvedShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
        $approvedShowcase->upvoters()->attach($upvoters->take(2));

        // Create draft showcase with more upvotes (shouldn't affect ranking)
        $draftOwner = User::factory()->create();
        $draftShowcase = Showcase::factory()->draft()->for($draftOwner)->create();
        $draftShowcase->upvoters()->attach($upvoters->take(5));

        // Approved showcase should be rank 1 (draft excluded)
        get(route('showcase.show', $approvedShowcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('lifetimeRank', 1)
                ->where('monthlyRank', 1)
            );
    });
});

describe('canEdit', function () {
    test('canEdit is false for guests', function () {
        $showcase = Showcase::factory()->approved()->create();

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', false)
            );
    });

    test('canEdit is false for non-owner users', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', false)
            );
    });

    test('canEdit is true for owner of draft showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->draft()->for($user)->create();

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', true)
            );
    });

    test('canEdit is true for owner of rejected showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->rejected()->for($user)->create();

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', true)
            );
    });

    test('canEdit is false for owner of pending showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->pending()->for($user)->create();

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', false)
            );
    });

    test('canEdit is false for owner of approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->approved()->for($user)->create();

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', false)
            );
    });

    test('canEdit is true for admin on any showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();

        $showcase = Showcase::factory()->approved()->create();

        actingAs($admin);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canEdit', true)
            );
    });
});

describe('approval celebration', function () {
    test('show_approval_celebration is true for owner of approved showcase that has not celebrated', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->approved()->for($user)->create([
            'approval_celebrated_at' => null,
        ]);

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('showcase.show_approval_celebration', true)
                ->has('showcase.linkedin_share_url')
            );
    });

    test('show_approval_celebration is missing for owner who has already celebrated', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->approved()->for($user)->create([
            'approval_celebrated_at' => now(),
        ]);

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->missing('showcase.show_approval_celebration')
                ->has('showcase.linkedin_share_url')
            );
    });

    test('show_approval_celebration is missing for non-owner of approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $showcase = Showcase::factory()->approved()->for($otherUser)->create([
            'approval_celebrated_at' => null,
        ]);

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->missing('showcase.show_approval_celebration')
                ->has('showcase.linkedin_share_url')
            );
    });

    test('show_approval_celebration is missing for guest viewing approved showcase', function () {
        $showcase = Showcase::factory()->approved()->create([
            'approval_celebrated_at' => null,
        ]);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->missing('showcase.show_approval_celebration')
                ->has('showcase.linkedin_share_url')
            );
    });

    test('show_approval_celebration is missing for owner of non-approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->pending()->for($user)->create();

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->missing('showcase.show_approval_celebration')
                ->has('showcase.linkedin_share_url')
            );
    });

    test('linkedin_share_url contains correct showcase url', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->approved()->for($user)->create([
            'approval_celebrated_at' => null,
        ]);

        actingAs($user);

        $expectedShowcaseUrl = route('showcase.show', $showcase);
        $expectedLinkedInUrl = 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($expectedShowcaseUrl);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('showcase.linkedin_share_url', $expectedLinkedInUrl)
            );
    });
});

describe('challengeEntries', function () {
    test('challengeEntries includes active challenge entries', function () {
        $showcase = Showcase::factory()->approved()->create();
        $challenge = Challenge::factory()->active()->create();
        $challenge->showcases()->attach($showcase);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('challengeEntries', 1)
            );
    });

    test('challengeEntries is empty when showcase has no active challenges', function () {
        $showcase = Showcase::factory()->approved()->create();

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('challengeEntries', 0)
            );
    });

    test('challengeEntries excludes inactive challenges', function () {
        $showcase = Showcase::factory()->approved()->create();
        $activeChallenge = Challenge::factory()->active()->create();
        $inactiveChallenge = Challenge::factory()->create(['is_active' => false]);
        $activeChallenge->showcases()->attach($showcase);
        $inactiveChallenge->showcases()->attach($showcase);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('challengeEntries', 1)
                ->where('challengeEntries.0.challenge.id', $activeChallenge->id)
            );
    });

    test('challengeEntries contains correct challenge data and rank', function () {
        $upvoters = User::factory()->count(3)->create();

        $showcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
        $showcase->upvoters()->attach($upvoters->take(3)); // 3 upvotes

        $otherShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-10']);
        $otherShowcase->upvoters()->attach($upvoters->take(1)); // 1 upvote

        $challenge = Challenge::factory()->active()->create([
            'title' => 'Test Challenge',
            'thumbnail_extension' => null,
        ]);
        $challenge->showcases()->attach([$showcase->id, $otherShowcase->id]);

        get(route('showcase.show', $showcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('challengeEntries', 1, fn (AssertableInertia $entry) => $entry
                    ->where('rank', 1)
                    ->has('challenge', fn (AssertableInertia $challengeProp) => $challengeProp
                        ->where('id', $challenge->id)
                        ->where('slug', $challenge->slug)
                        ->where('title', 'Test Challenge')
                        ->where('thumbnail_url', null)
                        ->where('thumbnail_rect_strings', null)
                    )
                )
            );
    });

    test('challengeEntries shows correct rank for lower-ranked showcase', function () {
        $upvoters = User::factory()->count(5)->create();

        $topShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-10']);
        $topShowcase->upvoters()->attach($upvoters->take(5)); // 5 upvotes

        $lowerShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
        $lowerShowcase->upvoters()->attach($upvoters->take(2)); // 2 upvotes

        $challenge = Challenge::factory()->active()->create();
        $challenge->showcases()->attach([$topShowcase->id, $lowerShowcase->id]);

        get(route('showcase.show', $lowerShowcase))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('challengeEntries.0.rank', 2)
            );
    });
});
