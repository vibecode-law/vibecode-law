<?php

use App\Enums\VideoHost;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\User;
use App\Services\VideoHost\Contracts\VideoHostService;
use App\Services\VideoHost\Exceptions\VideoHostException;
use App\Services\VideoHost\ValueObjects\AssetData;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

describe('auth', function () {
    test('requires authentication', function () {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset-id',
        ])->assertRedirect(route('login'));
    });

    test('allows admin to sync video host', function () {
        Storage::fake();

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $mock = mock(VideoHostService::class);
        $mock->shouldReceive('host')
            ->andReturn(VideoHost::Mux);
        $mock->shouldReceive('getAsset')
            ->once()
            ->andReturn(new AssetData(
                externalId: 'test-asset-id',
                duration: 120.5,
                playbackId: 'playback-123',
                captionTrackId: 'caption-456',
            ));
        $mock->shouldReceive('getTranscriptVtt')
            ->once()
            ->andReturn('WEBVTT');

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset-id',
        ])->assertRedirect();
    });

    test('does not allow moderators to sync video host', function () {
        $moderator = User::factory()->moderator()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($moderator);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset-id',
        ])->assertForbidden();
    });

    test('does not allow regular users to sync video host', function () {
        /** @var User */
        $user = User::factory()->create();

        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($user);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset-id',
        ])->assertForbidden();
    });
});

describe('sync', function () {
    test('syncs lesson with video host and updates all fields', function () {
        Storage::fake();
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
            'playback_id' => null,
            'duration_seconds' => null,
        ]);

        $mock = mock(VideoHostService::class);
        $mock->shouldReceive('host')
            ->andReturn(VideoHost::Mux);
        $mock->shouldReceive('getAsset')
            ->once()
            ->with('new-asset-id')
            ->andReturn(new AssetData(
                externalId: 'new-asset-id',
                duration: 300.7,
                playbackId: 'playback-abc',
                captionTrackId: 'caption-def',
            ));
        $mock->shouldReceive('getTranscriptVtt')
            ->once()
            ->andReturn("WEBVTT\n\n00:00:00.000 --> 00:00:05.000\nHello world");
        $mock->shouldReceive('getTranscriptTxt')
            ->once()
            ->andReturn('Hello world');
        $mock->shouldReceive('getThumbnail')
            ->once()
            ->andReturn('fake-image-contents');

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'new-asset-id',
        ])->assertRedirect(route('staff.academy.courses.lessons.edit', [$course, $lesson]))
            ->assertSessionHas('flash.message', [
                'message' => 'Synced with video host successfully.',
                'type' => 'success',
            ]);

        $lesson->refresh();

        expect($lesson->asset_id)->toBe('new-asset-id')
            ->and($lesson->host)->toBe(VideoHost::Mux)
            ->and($lesson->playback_id)->toBe('playback-abc')
            ->and($lesson->duration_seconds)->toBe(301)
            ->and($lesson->transcriptLines)->toHaveCount(1)
            ->and($lesson->transcriptLines->first()->text)->toBe('Hello world')
            ->and($lesson->thumbnail_filename)->not->toBeNull();

        Storage::assertExists("lessons/{$lesson->id}/transcript.vtt");
        Storage::assertExists("lessons/{$lesson->id}/transcript.txt");
        Storage::disk('public')->assertExists("lesson/{$lesson->id}/{$lesson->thumbnail_filename}");
    });

    test('returns user-friendly error when asset is not found', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        mock(VideoHostService::class)
            ->shouldReceive('getAsset')
            ->once()
            ->andThrow(VideoHostException::assetNotFound());

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'invalid-asset-id',
        ])->assertRedirect()
            ->assertSessionHasErrors([
                'asset_id' => 'No asset found with that ID. Please check the asset ID and try again.',
            ]);

        $lesson->refresh();

        expect($lesson->asset_id)->toBeNull()
            ->and($lesson->host)->toBeNull();
    });

    test('returns user-friendly error when api credentials are invalid', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        mock(VideoHostService::class)
            ->shouldReceive('getAsset')
            ->once()
            ->andThrow(VideoHostException::authenticationFailed());

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset-id',
        ])->assertRedirect()
            ->assertSessionHasErrors([
                'asset_id' => 'Unable to authenticate with the video host. Please check the API credentials.',
            ]);
    });

    test('returns user-friendly error when asset has no playback id', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        mock(VideoHostService::class)
            ->shouldReceive('getAsset')
            ->once()
            ->andReturn(new AssetData(
                externalId: 'test-asset',
                duration: 120.0,
                playbackId: null,
                captionTrackId: 'caption-456',
            ));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset',
        ])->assertRedirect()
            ->assertSessionHasErrors([
                'asset_id' => 'The asset does not have a playback ID. It may still be processing.',
            ]);

        $lesson->refresh();

        expect($lesson->asset_id)->toBeNull()
            ->and($lesson->host)->toBeNull();
    });

    test('returns user-friendly error when asset has no caption track', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        mock(VideoHostService::class)
            ->shouldReceive('getAsset')
            ->once()
            ->andReturn(new AssetData(
                externalId: 'test-asset',
                duration: 120.0,
                playbackId: 'playback-123',
                captionTrackId: null,
            ));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset',
        ])->assertRedirect()
            ->assertSessionHasErrors([
                'asset_id' => 'The asset does not have a subtitle track. Please add captions before syncing.',
            ]);

        $lesson->refresh();

        expect($lesson->asset_id)->toBeNull()
            ->and($lesson->host)->toBeNull();
    });

    test('returns generic error for unexpected failures', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        mock(VideoHostService::class)
            ->shouldReceive('getAsset')
            ->once()
            ->andThrow(new \Exception('Something unexpected'));

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset',
        ])->assertRedirect()
            ->assertSessionHasErrors([
                'asset_id' => 'An unexpected error occurred while syncing. Please try again.',
            ]);

        $lesson->refresh();

        expect($lesson->asset_id)->toBeNull()
            ->and($lesson->host)->toBeNull();
    });

    test('returns user-friendly error when transcript download fails', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
        ]);

        $mock = mock(VideoHostService::class);
        $mock->shouldReceive('getAsset')
            ->once()
            ->andReturn(new AssetData(
                externalId: 'test-asset',
                duration: 120.0,
                playbackId: 'playback-123',
                captionTrackId: 'caption-456',
            ));
        $mock->shouldReceive('getTranscriptVtt')
            ->once()
            ->andThrow(VideoHostException::requestFailed());

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset',
        ])->assertRedirect()
            ->assertSessionHasErrors('asset_id');

        $lesson->refresh();

        expect($lesson->asset_id)->toBeNull()
            ->and($lesson->host)->toBeNull();
    });

    test('does not fetch thumbnail when lesson already has one', function () {
        Storage::fake();
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->withStockThumbnail()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
            'playback_id' => null,
            'duration_seconds' => null,
        ]);

        $originalFilename = $lesson->thumbnail_filename;

        $mock = mock(VideoHostService::class);
        $mock->shouldReceive('host')
            ->andReturn(VideoHost::Mux);
        $mock->shouldReceive('getAsset')
            ->once()
            ->andReturn(new AssetData(
                externalId: 'test-asset',
                duration: 120.0,
                playbackId: 'playback-123',
                captionTrackId: 'caption-456',
            ));
        $mock->shouldReceive('getTranscriptVtt')
            ->once()
            ->andReturn("WEBVTT\n\n00:00:00.000 --> 00:00:05.000\nHello");
        $mock->shouldReceive('getTranscriptTxt')
            ->once()
            ->andReturn('Hello');
        $mock->shouldNotReceive('getThumbnail');

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset',
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->thumbnail_filename)->toBe($originalFilename);
    });

    test('does not save thumbnail when video host returns null', function () {
        Storage::fake();
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'asset_id' => null,
            'host' => null,
            'playback_id' => null,
            'duration_seconds' => null,
        ]);

        $mock = mock(VideoHostService::class);
        $mock->shouldReceive('host')
            ->andReturn(VideoHost::Mux);
        $mock->shouldReceive('getAsset')
            ->once()
            ->andReturn(new AssetData(
                externalId: 'test-asset',
                duration: 120.0,
                playbackId: 'playback-123',
                captionTrackId: 'caption-456',
            ));
        $mock->shouldReceive('getTranscriptVtt')
            ->once()
            ->andReturn("WEBVTT\n\n00:00:00.000 --> 00:00:05.000\nHello");
        $mock->shouldReceive('getTranscriptTxt')
            ->once()
            ->andReturn('Hello');
        $mock->shouldReceive('getThumbnail')
            ->once()
            ->andReturnNull();

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset',
        ])->assertRedirect();

        $lesson->refresh();

        expect($lesson->thumbnail_filename)->toBeNull();
    });

    test('enforces scope binding', function () {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $otherCourse = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $otherCourse->id]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), [
            'asset_id' => 'test-asset-id',
        ])->assertNotFound();
    });
});

describe('validation', function () {
    test('validates required and invalid fields', function ($data, $invalid) {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        actingAs($admin);

        patch(route('staff.academy.courses.lessons.sync-video-host', [$course, $lesson]), $data)
            ->assertSessionHasErrors($invalid);
    })->with([
        'missing asset_id' => [
            [],
            ['asset_id'],
        ],
        'empty asset_id' => [
            ['asset_id' => ''],
            ['asset_id'],
        ],
        'asset_id too long' => [
            ['asset_id' => str_repeat('a', 256)],
            ['asset_id'],
        ],
    ]);
});
