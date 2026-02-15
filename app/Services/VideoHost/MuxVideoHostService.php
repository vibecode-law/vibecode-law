<?php

namespace App\Services\VideoHost;

use App\Services\VideoHost\Contracts\VideoHostService;
use App\Services\VideoHost\ValueObjects\AssetData;
use Illuminate\Support\Facades\Http;
use MuxPhp\Api\AssetsApi;

class MuxVideoHostService implements VideoHostService
{
    public function __construct(private AssetsApi $assetsApi) {}

    public function getAsset(string $externalId): AssetData
    {
        $response = $this->assetsApi->getAsset(asset_id: $externalId);
        $asset = $response->getData();

        $playbackIds = $asset->getPlaybackIds() ?? [];
        $playbackId = count($playbackIds) > 0
            ? $playbackIds[0]->getId()
            : null;

        $captionTrackId = null;
        $tracks = $asset->getTracks() ?? [];

        foreach ($tracks as $track) {
            if ($track->getType() === 'text' && $track->getTextType() === 'subtitles') {
                $captionTrackId = $track->getId();

                break;
            }
        }

        return new AssetData(
            externalId: $asset->getId(),
            duration: $asset->getDuration(),
            playbackId: $playbackId,
            captionTrackId: $captionTrackId,
        );
    }

    public function getTranscriptTxt(AssetData $asset): string
    {
        return Http::get("https://stream.mux.com/{$asset->playbackId}/text/{$asset->captionTrackId}.txt")
            ->throw()
            ->body();
    }

    public function getTranscriptVtt(AssetData $asset): string
    {
        return Http::get("https://stream.mux.com/{$asset->playbackId}/text/{$asset->captionTrackId}.vtt")
            ->throw()
            ->body();
    }
}
