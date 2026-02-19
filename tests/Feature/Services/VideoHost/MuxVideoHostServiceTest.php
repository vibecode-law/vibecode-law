<?php

use App\Services\VideoHost\Exceptions\VideoHostException;
use App\Services\VideoHost\MuxVideoHostService;
use App\Services\VideoHost\ValueObjects\AssetData;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;

function testSigningPrivateKey(): string
{
    $key = openssl_pkey_new([
        'digest_alg' => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);

    openssl_pkey_export($key, $privateKey);

    return base64_encode($privateKey);
}

function createService(): MuxVideoHostService
{
    return new MuxVideoHostService(
        tokenId: 'test-token-id',
        tokenSecret: 'test-token-secret',
    );
}

function createSignedService(): MuxVideoHostService
{
    return new MuxVideoHostService(
        tokenId: 'test-token-id',
        tokenSecret: 'test-token-secret',
        signingKeyId: 'test-signing-key-id',
        signingPrivateKey: testSigningPrivateKey(),
    );
}

/**
 * @return array<string, mixed>
 */
function muxAssetResponse(array $overrides = []): array
{
    $defaults = [
        'id' => 'asset-id-123',
        'status' => 'ready',
        'duration' => 120.5,
        'playback_ids' => [
            ['id' => 'playback-id-456', 'policy' => 'public'],
        ],
        'tracks' => [
            ['id' => 'video-track-1', 'type' => 'video'],
            ['id' => 'audio-track-1', 'type' => 'audio'],
            ['id' => 'caption-track-1', 'type' => 'text', 'text_type' => 'subtitles', 'text_source' => 'generated_vod'],
        ],
    ];

    return ['data' => array_merge($defaults, $overrides)];
}

describe('getAsset', function () {
    it('maps Mux asset response to AssetData', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse()),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result)->toBeInstanceOf(AssetData::class);
        expect($result->externalId)->toBe('asset-id-123');
        expect($result->duration)->toBe(120.5);
        expect($result->playbackId)->toBe('playback-id-456');
        expect($result->captionTrackId)->toBe('caption-track-1');
        expect($result->signedPlayback)->toBeFalse();

        Http::assertSentCount(1);
    });

    it('handles asset with no playback IDs', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse(overrides: [
                'playback_ids' => [],
            ])),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result->playbackId)->toBeNull();
        expect($result->signedPlayback)->toBeFalse();
    });

    it('handles asset with null playback IDs array', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse(overrides: [
                'playback_ids' => null,
            ])),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result->playbackId)->toBeNull();
    });

    it('extracts caption track ID from text tracks', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse(overrides: [
                'tracks' => [
                    ['id' => 'video-track-1', 'type' => 'video'],
                    ['id' => 'caption-track-1', 'type' => 'text', 'text_type' => 'subtitles'],
                ],
            ])),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result->captionTrackId)->toBe('caption-track-1');
    });

    it('handles asset with no text tracks', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse(overrides: [
                'tracks' => [
                    ['id' => 'video-track-1', 'type' => 'video'],
                    ['id' => 'audio-track-1', 'type' => 'audio'],
                ],
            ])),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result->captionTrackId)->toBeNull();
    });

    it('handles asset with null tracks array', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse(overrides: [
                'tracks' => null,
            ])),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result->captionTrackId)->toBeNull();
    });

    it('detects signed playback policy', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(muxAssetResponse(overrides: [
                'playback_ids' => [
                    ['id' => 'playback-id-456', 'policy' => 'signed'],
                ],
            ])),
        ]);

        $result = createService()->getAsset(externalId: 'asset-id-123');

        expect($result->signedPlayback)->toBeTrue();
    });

    it('throws authentication failed for 401 response', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(status: 401),
        ]);

        createService()->getAsset(externalId: 'asset-id-123');
    })->throws(VideoHostException::class, 'Unable to authenticate');

    it('throws asset not found for 404 response', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(status: 404),
        ]);

        createService()->getAsset(externalId: 'asset-id-123');
    })->throws(VideoHostException::class, 'No asset found');

    it('throws request failed for other error responses', function () {
        Http::fake([
            'api.mux.com/video/v1/assets/asset-id-123' => Http::response(status: 500),
        ]);

        createService()->getAsset(externalId: 'asset-id-123');
    })->throws(VideoHostException::class, 'returned an error');
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

        $result = createService()->getTranscriptTxt(asset: $asset);

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

        $result = createService()->getTranscriptVtt(asset: $asset);

        expect($result)->toBe($vttContent);

        Http::assertSentCount(1);
    });
});

describe('generatePlaybackTokens', function () {
    it('returns playback, thumbnail, and storyboard tokens when signing keys are configured', function () {
        $service = createSignedService();

        $tokens = $service->generatePlaybackTokens(playbackId: 'playback-id-456');

        expect($tokens)
            ->toHaveKeys(['playback', 'thumbnail', 'storyboard'])
            ->each->toBeString();
    });

    it('generates tokens with correct claims', function () {
        $signingPrivateKey = testSigningPrivateKey();

        $service = new MuxVideoHostService(
            tokenId: 'test-token-id',
            tokenSecret: 'test-token-secret',
            signingKeyId: 'test-signing-key-id',
            signingPrivateKey: $signingPrivateKey,
        );

        $tokens = $service->generatePlaybackTokens(playbackId: 'playback-id-456');

        $publicKey = openssl_pkey_get_details(
            openssl_pkey_get_private(base64_decode($signingPrivateKey))
        )['key'];

        $playbackClaims = JWT::decode($tokens['playback'], new Key($publicKey, 'RS256'));

        expect($playbackClaims->sub)->toBe('playback-id-456');
        expect($playbackClaims->aud)->toBe('v');
        expect($playbackClaims->kid)->toBe('test-signing-key-id');
        expect($playbackClaims->exp)->toBeGreaterThan(time() + (11 * 60 * 60));

        $thumbnailClaims = JWT::decode($tokens['thumbnail'], new Key($publicKey, 'RS256'));

        expect($thumbnailClaims->aud)->toBe('t');

        $storyboardClaims = JWT::decode($tokens['storyboard'], new Key($publicKey, 'RS256'));

        expect($storyboardClaims->aud)->toBe('s');
    });

    it('returns empty array when signing keys are not configured', function () {
        $tokens = createService()->generatePlaybackTokens(playbackId: 'playback-id-456');

        expect($tokens)->toBe([]);
    });
});
