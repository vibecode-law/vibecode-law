<?php

use App\Enums\ShowcaseDraftStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertRedirect(route('login'));
    });

    test('requires email verification', function () {
        /** @var User */
        $owner = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertRedirect(route('verification.notice'));
    });

    test('only showcase owner can access edit page', function () {
        /** @var User */
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($otherUser);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertForbidden();
    });

    test('owner can access edit page for their draft', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertOk();
    });

    test('admin can access edit page for any draft', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($admin);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertOk();
    });

    test('cannot access pending draft as owner', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->pending()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertForbidden();
    });

    test('admin can access pending draft', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->pending()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($admin);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertOk();
    });

    test('owner can access rejected draft', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->rejected()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertOk();
    });

    test('blocked user cannot access edit page', function () {
        /** @var User */
        $owner = User::factory()->blockedFromSubmissions()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertForbidden();
    });
});

describe('response', function () {
    test('renders correct Inertia component', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/edit-draft')
        );
    });

    test('includes draft data with correct values', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->withoutPracticeAreas()->for($showcase, 'showcase')->create([
            'title' => 'Draft Title',
            'tagline' => 'Draft Tagline',
            'description' => 'Draft Description',
            'key_features' => 'Draft Key Features',
            'help_needed' => 'Draft Help Needed',
            'url' => 'https://draft.example.com',
            'video_url' => 'https://youtube.com/watch?v=draft',
            'source_status' => SourceStatus::OpenSource,
            'source_url' => 'https://github.com/test/draft',
            'status' => ShowcaseDraftStatus::Draft,
        ]);

        $practiceArea = PracticeArea::factory()->create(['name' => 'Web Development']);
        $draft->practiceAreas()->attach($practiceArea);

        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create([
            'filename' => 'test-image.jpg',
            'order' => 1,
        ]);

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/edit-draft')
            ->has('draft', fn (AssertableInertia $draftProp) => $draftProp
                ->where('id', $draft->id)
                ->where('showcase_id', $showcase->id)
                ->where('showcase_slug', $showcase->slug)
                ->where('showcase_title', $showcase->title)
                ->where('title', 'Draft Title')
                ->where('tagline', 'Draft Tagline')
                ->where('description', 'Draft Description')
                ->where('key_features', 'Draft Key Features')
                ->where('help_needed', 'Draft Help Needed')
                ->where('url', 'https://draft.example.com')
                ->where('video_url', 'https://youtube.com/watch?v=draft')
                ->where('source_status', SourceStatus::OpenSource->forFrontend())
                ->where('source_url', 'https://github.com/test/draft')
                ->where('status', ShowcaseDraftStatus::Draft->forFrontend())
                ->has('thumbnail_url')
                ->has('thumbnail_crop')
                ->has('thumbnail_rect_string')
                ->has('images', 1)
                ->has('practiceAreas', 1)
                ->where('practiceAreas.0.id', $practiceArea->id)
                ->where('practiceAreas.0.name', 'Web Development')
                ->has('submitted_at')
                ->has('rejection_reason')
                ->has('created_at')
                ->has('updated_at')
            )
        );
    });

    test('includes images with kept and added actions', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $originalImage = ShowcaseImage::factory()->for($showcase, 'showcase')->create([
            'filename' => 'original.jpg',
            'order' => 1,
        ]);
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        // Keep action for original image
        $keptImage = ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->keep($originalImage)->create();

        // Add action for new image
        $addedImage = ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create([
            'filename' => 'new-image.jpg',
            'order' => 2,
        ]);

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/edit-draft')
            ->has('draft.images', 2)
            ->where('draft.images.0.id', $keptImage->id)
            ->where('draft.images.0.filename', 'original.jpg')
            ->where('draft.images.0.action', ShowcaseDraftImage::ACTION_KEEP)
            ->where('draft.images.1.id', $addedImage->id)
            ->where('draft.images.1.filename', 'new-image.jpg')
            ->where('draft.images.1.action', ShowcaseDraftImage::ACTION_ADD)
        );
    });

    test('includes practice areas sorted by name', function () {
        // Clear any existing practice areas first
        PracticeArea::query()->delete();

        /** @var User */
        $owner = User::factory()->create();

        // Create practice areas first, before the factories potentially create more
        PracticeArea::factory()->create(['name' => 'Zebra Development']);
        PracticeArea::factory()->create(['name' => 'API Design']);
        PracticeArea::factory()->create(['name' => 'Mobile Apps']);

        $showcase = Showcase::factory()->approved()->withoutPracticeAreas()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->withoutPracticeAreas()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/edit-draft')
            ->has('practiceAreas', 3)
            ->where('practiceAreas.0.name', 'API Design')
            ->where('practiceAreas.1.name', 'Mobile Apps')
            ->where('practiceAreas.2.name', 'Zebra Development')
        );
    });

    test('includes all source statuses', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/edit-draft')
            ->has('sourceStatuses', 3)
            ->where('sourceStatuses.0', SourceStatus::NotAvailable->forFrontend())
            ->where('sourceStatuses.1', SourceStatus::SourceAvailable->forFrontend())
            ->where('sourceStatuses.2', SourceStatus::OpenSource->forFrontend())
        );
    });

    test('includes thumbnail crop data when present', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create([
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
        ]);
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = get(route('showcase.draft.edit', $draft));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/edit-draft')
            ->where('draft.thumbnail_crop.x', 10)
            ->where('draft.thumbnail_crop.y', 20)
            ->where('draft.thumbnail_crop.width', 100)
            ->where('draft.thumbnail_crop.height', 100)
        );
    });
});
