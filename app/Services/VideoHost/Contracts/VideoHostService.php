<?php

namespace App\Services\VideoHost\Contracts;

use App\Enums\VideoHost;
use App\Services\VideoHost\ValueObjects\AssetData;

interface VideoHostService
{
    public function host(): VideoHost;

    public function getAsset(string $externalId): AssetData;

    public function getTranscriptTxt(AssetData $asset): string;

    public function getTranscriptVtt(AssetData $asset): string;

    /**
     * Fetch the thumbnail image contents for an asset, or null if unavailable.
     */
    public function getThumbnail(AssetData $asset): ?string;

    /**
     * Generate tokens required for client-side video playback.
     *
     * @return array<string, string>
     */
    public function generatePlaybackTokens(string $playbackId): array;
}
