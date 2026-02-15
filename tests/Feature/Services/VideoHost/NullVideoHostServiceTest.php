<?php

use App\Services\VideoHost\NullVideoHostService;
use App\Services\VideoHost\ValueObjects\AssetData;

describe('getAsset', function () {
    it('returns stub AssetData with correct ID and defaults', function () {
        $service = new NullVideoHostService;
        $result = $service->getAsset(externalId: 'any-asset-id');

        expect($result)->toBeInstanceOf(AssetData::class);
        expect($result->externalId)->toBe('any-asset-id');
        expect($result->duration)->toBeNull();
        expect($result->playbackId)->toBeNull();
        expect($result->captionTrackId)->toBeNull();
    });
});

describe('getTranscriptTxt', function () {
    it('returns an empty string', function () {
        $asset = new AssetData(externalId: 'any');
        $service = new NullVideoHostService;

        expect($service->getTranscriptTxt(asset: $asset))->toBe('');
    });
});

describe('getTranscriptVtt', function () {
    it('returns an empty string', function () {
        $asset = new AssetData(externalId: 'any');
        $service = new NullVideoHostService;

        expect($service->getTranscriptVtt(asset: $asset))->toBe('');
    });
});
