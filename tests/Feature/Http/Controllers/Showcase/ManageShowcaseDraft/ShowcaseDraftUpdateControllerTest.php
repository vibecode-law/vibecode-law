<?php

use App\Actions\ShowcaseDraft\SubmitShowcaseDraftAction;
use App\Enums\ShowcaseDraftStatus;
use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use App\Models\Showcase\ShowcaseImage;
use App\Models\User;
use App\Notifications\ShowcaseDraft\ShowcaseDraftSubmittedForApproval;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
use function Pest\Laravel\put;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect(route('login'));
    });

    test('only showcase owner can update a draft', function () {
        /** @var User */
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($otherUser);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertForbidden();
    });
});

describe('removed images', function () {
    test('can remove a kept image by providing its original_image_id', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        // Create two showcase images
        $image1 = ShowcaseImage::factory()->for($showcase, 'showcase')->create(['order' => 1]);
        $image2 = ShowcaseImage::factory()->for($showcase, 'showcase')->create(['order' => 2]);

        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        // Create draft images that reference the original images (keep action)
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->keep($image1)->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->keep($image2)->create();

        actingAs($owner);

        // Remove image1 by its original_image_id (not the draft image id)
        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
            'removed_images' => [$image1->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify the draft image was marked as removed
        $draft->refresh();
        $removedImage = $draft->images()->where('original_image_id', $image1->id)->first();
        expect($removedImage->action)->toBe(ShowcaseDraftImage::ACTION_REMOVE);
    });

    test('cannot remove an image that does not belong to the draft', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $otherShowcase = Showcase::factory()->approved()->create();

        // Create an image on a different showcase
        $otherImage = ShowcaseImage::factory()->for($otherShowcase, 'showcase')->create();

        $image = ShowcaseImage::factory()->for($showcase, 'showcase')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->keep($image)->create();

        actingAs($owner);

        // Try to remove an image from another showcase
        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
            'removed_images' => [$otherImage->id],
        ]);

        $response->assertInvalid(['removed_images.0']);
    });

    test('cannot remove an image that is already marked as removed', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        $image1 = ShowcaseImage::factory()->for($showcase, 'showcase')->create(['order' => 1]);
        $image2 = ShowcaseImage::factory()->for($showcase, 'showcase')->create(['order' => 2]);

        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        // Create a kept image and a removed image
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->keep($image1)->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->remove($image2)->create();

        actingAs($owner);

        // Try to remove the already removed image
        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
            'removed_images' => [$image2->id],
        ]);

        $response->assertInvalid(['removed_images.0']);
    });
});

describe('deleted new images', function () {
    test('can delete a newly added draft image by its draft image id', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        $originalImage = ShowcaseImage::factory()->for($showcase, 'showcase')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        // Create a kept image (from original) and a new draft image
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->keep($originalImage)->create();
        $newDraftImage = ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        // Delete the new draft image
        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
            'deleted_new_images' => [$newDraftImage->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify the draft image was deleted
        expect(ShowcaseDraftImage::find($newDraftImage->id))->toBeNull();
    });
});

describe('field updates', function () {
    test('updates draft fields', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'New Title',
            'tagline' => 'New tagline',
            'description' => 'New description',
            'key_features' => 'New key features',
            'help_needed' => 'New help needed',
            'url' => 'https://new.example.com',
            'video_url' => 'https://youtube.com/watch?v=new',
            'source_status' => SourceStatus::OpenSource->value,
            'source_url' => 'https://github.com/new/repo',
        ]);

        $draft->refresh();

        expect($draft->title)->toBe('New Title');
        expect($draft->tagline)->toBe('New tagline');
        expect($draft->description)->toBe('New description');
        expect($draft->key_features)->toBe('New key features');
        expect($draft->help_needed)->toBe('New help needed');
        expect($draft->url)->toBe('https://new.example.com');
        expect($draft->video_url)->toBe('https://youtube.com/watch?v=new');
        expect($draft->source_status)->toBe(SourceStatus::OpenSource);
        expect($draft->source_url)->toBe('https://github.com/new/repo');
    });

    test('syncs practice areas', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->withoutPracticeAreas()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        $originalPracticeArea = PracticeArea::factory()->create();
        $draft->practiceAreas()->attach($originalPracticeArea);

        $newPracticeArea1 = PracticeArea::factory()->create();
        $newPracticeArea2 = PracticeArea::factory()->create();

        actingAs($owner);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => [$newPracticeArea1->id, $newPracticeArea2->id],
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $draft->refresh();

        expect($draft->practiceAreas)->toHaveCount(2);
        expect($draft->practiceAreas->pluck('id')->toArray())->toBe([$newPracticeArea1->id, $newPracticeArea2->id]);
    });

    test('redirects to edit page with success message', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated Title',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertRedirect(route('showcase.draft.edit', $draft));
        $response->assertSessionHas('flash.message', ['message' => 'Draft updated successfully.', 'type' => 'success']);
    });
});

describe('thumbnail', function () {
    test('uploads new thumbnail', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg', width: 500, height: 500);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'thumbnail' => $thumbnail,
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400],
        ]);

        $draft->refresh();

        expect($draft->thumbnail_extension)->toBe('jpg');
        Storage::disk('public')->assertExists("showcase-drafts/{$draft->id}/thumbnail.jpg");
    });

    test('stores thumbnail crop metadata', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $thumbnail = UploadedFile::fake()->image('thumbnail.png', width: 600, height: 600);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'thumbnail' => $thumbnail,
            'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 300, 'height' => 300],
        ]);

        $draft->refresh();

        expect($draft->thumbnail_crop)->toBe(['x' => 10, 'y' => 20, 'width' => 300, 'height' => 300]);
    });

    test('replaces existing thumbnail', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create([
            'thumbnail_extension' => 'jpg',
        ]);
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        // Create existing thumbnail
        Storage::disk('public')->put("showcase-drafts/{$draft->id}/thumbnail.jpg", 'old content');

        actingAs($owner);

        $newThumbnail = UploadedFile::fake()->image('new-thumbnail.png', width: 500, height: 500);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'thumbnail' => $newThumbnail,
            'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 400, 'height' => 400],
        ]);

        $draft->refresh();

        expect($draft->thumbnail_extension)->toBe('png');
        Storage::disk('public')->assertMissing("showcase-drafts/{$draft->id}/thumbnail.jpg");
        Storage::disk('public')->assertExists("showcase-drafts/{$draft->id}/thumbnail.png");
    });
});

describe('submit flag', function () {
    test('calls SubmitShowcaseDraftAction when submit is true', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        mock(SubmitShowcaseDraftAction::class)
            ->shouldReceive('submit')
            ->once()
            ->withArgs(fn (ShowcaseDraft $passedDraft) => $passedDraft->is($draft));

        actingAs($owner);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);
    });

    test('sends notification to staff when submitted', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        // Create a user with direct permission (controller queries permissions relationship directly)
        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('showcase.approve-reject');

        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);

        Notification::assertSentTo([$admin, $userWithPermission], ShowcaseDraftSubmittedForApproval::class);
    });

    test('redirects to showcases index when submitted', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => true,
        ]);

        $response->assertRedirect(route('user-area.showcases.index'));
        $response->assertSessionHas('flash.message', ['message' => 'Draft submitted for approval.', 'type' => 'success']);
    });

    test('does not submit when submit flag is false', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'submit' => false,
        ]);

        $draft->refresh();

        expect($draft->status)->toBe(ShowcaseDraftStatus::Draft);
        $response->assertRedirect(route('showcase.draft.edit', $draft));
    });
});

describe('status restrictions', function () {
    test('cannot update pending draft as owner', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->pending()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'New Title',
            'tagline' => 'New tagline',
            'description' => 'New description',
            'key_features' => 'New key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertForbidden();
    });

    test('can update rejected draft as owner', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->rejected()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Updated After Rejection',
            'tagline' => 'Updated tagline',
            'description' => 'Updated description',
            'key_features' => 'Updated key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $draft->refresh();
        expect($draft->title)->toBe('Updated After Rejection');
    });

    test('admin can update pending draft', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->pending()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($admin);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Admin Updated',
            'tagline' => 'Admin tagline',
            'description' => 'Admin description',
            'key_features' => 'Admin key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $draft->refresh();
        expect($draft->title)->toBe('Admin Updated');
    });

    test('blocked user cannot update draft', function () {
        /** @var User */
        $owner = User::factory()->blockedFromSubmissions()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => 'Blocked Update',
            'tagline' => 'Blocked tagline',
            'description' => 'Blocked description',
            'key_features' => 'Blocked key features',
            'source_status' => SourceStatus::NotAvailable->value,
        ]);

        $response->assertForbidden();
    });
});

describe('thumbnail operations', function () {
    test('removes thumbnail when remove_thumbnail flag is true', function () {
        Storage::fake('public');

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()
            ->for($showcase, 'showcase')
            ->create([
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            ]);
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        // Create the thumbnail file
        Storage::disk('public')->put("showcase-drafts/{$draft->id}/thumbnail.jpg", 'fake-image-content');
        Storage::disk('public')->assertExists("showcase-drafts/{$draft->id}/thumbnail.jpg");

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'remove_thumbnail' => true,
        ]);

        $response->assertRedirect();

        $draft->refresh();

        expect($draft->thumbnail_extension)->toBeNull();
        expect($draft->thumbnail_crop)->toBeNull();
        Storage::disk('public')->assertMissing("showcase-drafts/{$draft->id}/thumbnail.jpg");
    });

    test('does not remove thumbnail when remove_thumbnail flag is false', function () {
        Storage::fake('public');

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()
            ->for($showcase, 'showcase')
            ->create([
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            ]);
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        // Create the thumbnail file
        Storage::disk('public')->put("showcase-drafts/{$draft->id}/thumbnail.jpg", 'fake-image-content');

        actingAs($owner);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'remove_thumbnail' => false,
        ]);

        $response->assertRedirect();

        $draft->refresh();

        expect($draft->thumbnail_extension)->toBe('jpg');
        expect($draft->thumbnail_crop)->toBe(['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500]);
        Storage::disk('public')->assertExists("showcase-drafts/{$draft->id}/thumbnail.jpg");
    });

    test('new thumbnail upload takes precedence over remove_thumbnail flag', function () {
        Storage::fake('public');

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()
            ->for($showcase, 'showcase')
            ->create([
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 0, 'y' => 0, 'width' => 500, 'height' => 500],
            ]);
        ShowcaseDraftImage::factory()->for($draft, 'showcaseDraft')->add()->create();

        // Create the old thumbnail file
        Storage::disk('public')->put("showcase-drafts/{$draft->id}/thumbnail.jpg", 'fake-image-content');

        actingAs($owner);

        $newThumbnail = UploadedFile::fake()->image('new-thumbnail.png', 500, 500);

        $response = put(route('showcase.draft.update', $draft), [
            'practice_area_ids' => $draft->practiceAreas->pluck('id')->toArray(),
            'title' => $draft->title,
            'tagline' => $draft->tagline,
            'description' => $draft->description,
            'key_features' => $draft->key_features,
            'source_status' => SourceStatus::NotAvailable->value,
            'thumbnail' => $newThumbnail,
            'thumbnail_crop' => ['x' => 10, 'y' => 10, 'width' => 400, 'height' => 400],
            'remove_thumbnail' => true,
        ]);

        $response->assertRedirect();

        $draft->refresh();

        // New thumbnail should be stored
        expect($draft->thumbnail_extension)->toBe('png');
        expect($draft->thumbnail_crop)->toBe(['x' => 10, 'y' => 10, 'width' => 400, 'height' => 400]);
        Storage::disk('public')->assertExists("showcase-drafts/{$draft->id}/thumbnail.png");
        // Old thumbnail should be removed
        Storage::disk('public')->assertMissing("showcase-drafts/{$draft->id}/thumbnail.jpg");
    });
});
