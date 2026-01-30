<?php

use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->create();

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertRedirect(route('login'));
    });

    test('allows owner to access their showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('requires email verification', function () {
        /** @var User */
        $user = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertRedirect(route('verification.notice'));
    });

    test('allows admin to access any showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create();

        actingAs($admin);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('prevents non-owner from accessing other users showcase', function () {
        /** @var User */
        $owner = User::factory()->create();
        /** @var User */
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->for($owner)->create();

        actingAs($otherUser);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertForbidden();
    });

    test('prevents owner from editing pending showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertForbidden();
    });

    test('prevents owner from editing approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertForbidden();
    });

    test('allows owner to edit draft showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->draft()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows owner to edit rejected showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->rejected()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows admin to edit pending showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($user)->create();

        actingAs($admin);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows admin to edit approved showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user)->create();

        actingAs($admin);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows moderator to access other users showcase', function () {
        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create();

        actingAs($moderator);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows moderator to edit pending showcase', function () {
        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($user)->create();

        actingAs($moderator);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows moderator to edit approved showcase', function () {
        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user)->create();

        actingAs($moderator);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });

    test('allows moderator to edit rejected showcase', function () {
        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();
        $showcase = Showcase::factory()->rejected()->for($user)->create();

        actingAs($moderator);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertOk();
    });
});

describe('data structure', function () {
    test('returns correct component', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/create')
        );
    });

    test('returns showcase with images loaded', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create();
        $images = ShowcaseImage::factory()->count(3)->for($showcase)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/create')
            ->has('showcase')
            ->where('showcase.id', $showcase->id)
            ->where('showcase.title', $showcase->title)
            ->where('showcase.description', $showcase->description)
            ->where('showcase.url', $showcase->url)
            ->where('showcase.status', $showcase->status->forFrontend())
            ->has('showcase.images', 3)
            ->where('showcase.images.0.id', $images[0]->id)
            ->where('showcase.images.1.id', $images[1]->id)
            ->where('showcase.images.2.id', $images[2]->id)
        );
    });

    test('returns correct data structure and no additional data', function () {
        /** @var User */
        $user = User::factory()->create();
        $practiceArea1 = PracticeArea::factory()->create(['name' => 'Web Development']);
        $practiceArea2 = PracticeArea::factory()->create(['name' => 'Mobile Apps']);

        $showcase = Showcase::factory()->withoutPracticeAreas()->for($user)->create([
            'title' => 'Complete Test',
            'tagline' => 'A complete tagline',
            'description' => 'Full description',
            'key_features' => 'Feature list here',
            'help_needed' => 'Help wanted text',
            'url' => 'https://complete.test',
            'video_url' => 'https://youtube.com/watch?v=complete',
            'source_status' => \App\Enums\SourceStatus::OpenSource,
            'source_url' => 'https://github.com/test/complete',
            'status' => \App\Enums\ShowcaseStatus::Draft,
            'view_count' => 75,
            'submitted_date' => null,
            'rejection_reason' => null,
        ]);

        $showcase->practiceAreas()->sync([$practiceArea1->id, $practiceArea2->id]);

        $image1 = ShowcaseImage::factory()->for($showcase)->create([
            'path' => "showcase/{$showcase->id}/images/image1.jpg",
            'filename' => 'image1.jpg',
            'order' => 1,
            'alt_text' => 'First image',
        ]);

        $image2 = ShowcaseImage::factory()->for($showcase)->create([
            'path' => "showcase/{$showcase->id}/images/image2.jpg",
            'filename' => 'image2.jpg',
            'order' => 2,
            'alt_text' => 'Second image',
        ]);

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/create')
            ->has('showcase', fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->where('id', $showcase->id)
                ->where('slug', $showcase->slug)
                ->where('title', 'Complete Test')
                ->where('tagline', 'A complete tagline')
                ->where('description', 'Full description')
                ->where('key_features', 'Feature list here')
                ->where('help_needed', 'Help wanted text')
                ->where('url', 'https://complete.test')
                ->where('video_url', 'https://youtube.com/watch?v=complete')
                ->where('source_status', \App\Enums\SourceStatus::OpenSource->forFrontend())
                ->where('source_url', 'https://github.com/test/complete')
                ->where('status', \App\Enums\ShowcaseStatus::Draft->forFrontend())
                ->where('submitted_date', null)
                ->where('rejection_reason', null)
                ->where('view_count', 75)
                ->has('thumbnail_url')
                ->has('thumbnail_crop')
                ->has('thumbnail_rect_string')
                ->has('images', 2, fn (AssertableInertia $image) => $image
                    ->has('id')
                    ->has('filename')
                    ->has('order')
                    ->has('alt_text')
                    ->has('url')
                )
                ->where('images.0.id', $image1->id)
                ->where('images.0.filename', 'image1.jpg')
                ->where('images.0.order', 1)
                ->where('images.0.alt_text', 'First image')
                ->where('images.0.url', $image1->url)
                ->where('images.1.id', $image2->id)
                ->where('images.1.filename', 'image2.jpg')
                ->where('images.1.order', 2)
                ->where('images.1.alt_text', 'Second image')
                ->where('images.1.url', $image2->url)
                ->has('practiceAreas', 2, fn (AssertableInertia $pa) => $pa
                    ->has('id')
                    ->has('name')
                    ->has('slug')
                )
                ->has('created_at')
                ->has('updated_at')
            )
        );
    });

    test('returns showcase with no images when showcase has no images', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create();

        actingAs($user);

        $response = get(route('showcase.manage.edit', $showcase));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/create')
            ->has('showcase')
            ->where('showcase.id', $showcase->id)
            ->has('showcase.images', 0)
        );
    });
});
