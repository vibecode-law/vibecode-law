<?php

namespace App\Services\VideoHost\Contracts;

use App\Services\VideoHost\ValueObjects\AssetData;

interface VideoHostService
{
    public function getAsset(string $externalId): AssetData;

    public function getTranscriptTxt(AssetData $asset): string;

    public function getTranscriptVtt(AssetData $asset): string;
}
