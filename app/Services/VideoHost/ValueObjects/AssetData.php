<?php

namespace App\Services\VideoHost\ValueObjects;

readonly class AssetData
{
    public function __construct(
        public string $externalId,
        public ?float $duration = null,
        public ?string $playbackId = null,
        public ?string $captionTrackId = null,
        public bool $signedPlayback = false,
    ) {}
}
