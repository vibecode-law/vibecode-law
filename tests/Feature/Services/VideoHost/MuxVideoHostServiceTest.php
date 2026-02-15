<?php

use App\Services\VideoHost\MuxVideoHostService;
use App\Services\VideoHost\ValueObjects\AssetData;
use Illuminate\Support\Facades\Http;
use MuxPhp\Api\AssetsApi;
use MuxPhp\Models\Asset;
use MuxPhp\Models\AssetResponse;
use MuxPhp\Models\PlaybackID;
use MuxPhp\Models\Track;

function createMockAsset(array $attributes = []): Asset
{
    $defaults = [
        'id' => 'asset-id-123',
        'status' => 'ready',
        'duration' => 120.5,
        'playback_ids' => [new PlaybackID(['id' => 'playback-id-456', 'policy' => 'public'])],
        'tracks' => [
            new Track(['id' => 'video-track-1', 'type' => 'video']),
            new Track(['id' => 'audio-track-1', 'type' => 'audio']),
            new Track(['id' => 'caption-track-1', 'type' => 'text', 'text_type' => 'subtitles', 'text_source' => 'generated_vod']),
        ],
        'created_at' => '1706745600',
    ];

    return new Asset(array_merge($defaults, $attributes));
}

describe('getAsset', function () {
    it('maps Mux asset response to AssetData', function () {
        $asset = createMockAsset();
        $response = new AssetResponse(['data' => $asset]);

        $assetsApi = Mockery::mock(AssetsApi::class);
        $assetsApi->shouldReceive('getAsset')
            ->once()
            ->with('asset-id-123')
            ->andReturn($response);

        $service = new MuxVideoHostService(assetsApi: $assetsApi);
        $result = $service->getAsset(externalId: 'asset-id-123');

        expect($result)->toBeInstanceOf(AssetData::class);
        expect($result->externalId)->toBe('asset-id-123');
        expect($result->duration)->toBe(120.5);
        expect($result->playbackId)->toBe('playback-id-456');
        expect($result->captionTrackId)->toBe('caption-track-1');
    });

    it('handles asset with no playback IDs', function () {
        $asset = createMockAsset(attributes: [
            'playback_ids' => [],
        ]);
        $response = new AssetResponse(['data' => $asset]);

        $assetsApi = Mockery::mock(AssetsApi::class);
        $assetsApi->shouldReceive('getAsset')
            ->once()
            ->with('asset-id-123')
            ->andReturn($response);

        $service = new MuxVideoHostService(assetsApi: $assetsApi);
        $result = $service->getAsset(externalId: 'asset-id-123');

        expect($result->playbackId)->toBeNull();
    });

    it('handles asset with null playback IDs array', function () {
        $asset = createMockAsset(attributes: [
            'playback_ids' => null,
        ]);
        $response = new AssetResponse(['data' => $asset]);

        $assetsApi = Mockery::mock(AssetsApi::class);
        $assetsApi->shouldReceive('getAsset')
            ->once()
            ->with('asset-id-123')
            ->andReturn($response);

        $service = new MuxVideoHostService(assetsApi: $assetsApi);
        $result = $service->getAsset(externalId: 'asset-id-123');

        expect($result->playbackId)->toBeNull();
    });

    it('extracts caption track ID from text tracks', function () {
        $asset = createMockAsset(attributes: [
            'tracks' => [
                new Track(['id' => 'video-track-1', 'type' => 'video']),
                new Track(['id' => 'caption-track-1', 'type' => 'text', 'text_type' => 'subtitles']),
            ],
        ]);
        $response = new AssetResponse(['data' => $asset]);

        $assetsApi = Mockery::mock(AssetsApi::class);
        $assetsApi->shouldReceive('getAsset')
            ->once()
            ->with('asset-id-123')
            ->andReturn($response);

        $service = new MuxVideoHostService(assetsApi: $assetsApi);
        $result = $service->getAsset(externalId: 'asset-id-123');

        expect($result->captionTrackId)->toBe('caption-track-1');
    });

    it('handles asset with no text tracks', function () {
        $asset = createMockAsset(attributes: [
            'tracks' => [
                new Track(['id' => 'video-track-1', 'type' => 'video']),
                new Track(['id' => 'audio-track-1', 'type' => 'audio']),
            ],
        ]);
        $response = new AssetResponse(['data' => $asset]);

        $assetsApi = Mockery::mock(AssetsApi::class);
        $assetsApi->shouldReceive('getAsset')
            ->once()
            ->with('asset-id-123')
            ->andReturn($response);

        $service = new MuxVideoHostService(assetsApi: $assetsApi);
        $result = $service->getAsset(externalId: 'asset-id-123');

        expect($result->captionTrackId)->toBeNull();
    });

    it('handles asset with null tracks array', function () {
        $asset = createMockAsset(attributes: [
            'tracks' => null,
        ]);
        $response = new AssetResponse(['data' => $asset]);

        $assetsApi = Mockery::mock(AssetsApi::class);
        $assetsApi->shouldReceive('getAsset')
            ->once()
            ->with('asset-id-123')
            ->andReturn($response);

        $service = new MuxVideoHostService(assetsApi: $assetsApi);
        $result = $service->getAsset(externalId: 'asset-id-123');

        expect($result->captionTrackId)->toBeNull();
    });
});

describe('getTranscriptTxt', function () {
    it('fetches txt transcript from Mux stream URL', function () {
        Http::fake([
            'stream.mux.com/playback-id-456/text/track-789.txt' => Http::response('Hello, this is a transcript.'),
        ]);

        $asset = new AssetData(
            externalId: 'asset-id-123',
            playbackId: 'playback-id-456',
            captionTrackId: 'track-789',
        );

        $assetsApi = Mockery::mock(AssetsApi::class);
        $service = new MuxVideoHostService(assetsApi: $assetsApi);

        $result = $service->getTranscriptTxt(asset: $asset);

        expect($result)->toBe('Hello, this is a transcript.');

        Http::assertSentCount(1);
    });
});

describe('getTranscriptVtt', function () {
    it('fetches vtt transcript from Mux stream URL', function () {
        $vttContent = "WEBVTT\n\n00:00:00.000 --> 00:00:05.000\nHello, this is a transcript.";

        Http::fake([
            'stream.mux.com/playback-id-456/text/track-789.vtt' => Http::response($vttContent),
        ]);

        $asset = new AssetData(
            externalId: 'asset-id-123',
            playbackId: 'playback-id-456',
            captionTrackId: 'track-789',
        );

        $assetsApi = Mockery::mock(AssetsApi::class);
        $service = new MuxVideoHostService(assetsApi: $assetsApi);

        $result = $service->getTranscriptVtt(asset: $asset);

        expect($result)->toBe($vttContent);

        Http::assertSentCount(1);
    });
});
