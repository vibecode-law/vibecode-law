<?php

namespace App\Services\VideoHost;

use App\Enums\VideoHost;
use App\Services\VideoHost\Contracts\VideoHostService;
use App\Services\VideoHost\ValueObjects\AssetData;

/**
 * Null implementation of VideoHostService for testing and environments without video hosting.
 */
class NullVideoHostService implements VideoHostService
{
    public function host(): VideoHost
    {
        return VideoHost::Mux;
    }

    public function getAsset(string $externalId): AssetData
    {
        return new AssetData(
            externalId: $externalId,
        );
    }

    public function getTranscriptTxt(AssetData $asset): string
    {
        return '';
    }

    public function getTranscriptVtt(AssetData $asset): string
    {
        return '';
    }

    public function getThumbnail(AssetData $asset): ?string
    {
        return null;
    }

    /**
     * @return array<string, string>
     */
    public function generatePlaybackTokens(string $playbackId): array
    {
        return [];
    }
}
