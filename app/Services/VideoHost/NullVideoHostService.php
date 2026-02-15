<?php

namespace App\Services\VideoHost;

use App\Services\VideoHost\Contracts\VideoHostService;
use App\Services\VideoHost\ValueObjects\AssetData;

/**
 * Null implementation of VideoHostService for testing and environments without Mux.
 */
class NullVideoHostService implements VideoHostService
{
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
}
