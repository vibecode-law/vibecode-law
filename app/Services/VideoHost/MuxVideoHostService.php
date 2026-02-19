<?php

namespace App\Services\VideoHost;

use App\Enums\VideoHost;
use App\Services\VideoHost\Contracts\VideoHostService;
use App\Services\VideoHost\Exceptions\VideoHostException;
use App\Services\VideoHost\ValueObjects\AssetData;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class MuxVideoHostService implements VideoHostService
{
    public function __construct(
        private string $tokenId,
        private string $tokenSecret,
        private ?string $signingKeyId = null,
        private ?string $signingPrivateKey = null,
    ) {}

    public function host(): VideoHost
    {
        return VideoHost::Mux;
    }

    public function getAsset(string $externalId): AssetData
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withBasicAuth(username: $this->tokenId, password: $this->tokenSecret)
                ->get("https://api.mux.com/video/v1/assets/{$externalId}");

            $response->throw();

            $data = $response->json('data');
        } catch (RequestException $e) {
            throw match ($e->response->status()) {
                401, 403 => VideoHostException::authenticationFailed(previous: $e),
                404 => VideoHostException::assetNotFound(previous: $e),
                default => VideoHostException::requestFailed(previous: $e),
            };
        }

        $playbackIds = $data['playback_ids'] ?? [];
        $firstPlayback = count($playbackIds) > 0 ? $playbackIds[0] : null;

        $captionTrackId = null;
        $tracks = $data['tracks'] ?? [];

        foreach ($tracks as $track) {
            if ($track['type'] === 'text' && $track['text_type'] === 'subtitles') {
                $captionTrackId = $track['id'];

                break;
            }
        }

        return new AssetData(
            externalId: $data['id'],
            duration: $data['duration'] ?? null,
            playbackId: $firstPlayback['id'] ?? null,
            captionTrackId: $captionTrackId,
            signedPlayback: ($firstPlayback['policy'] ?? null) === 'signed',
        );
    }

    public function getTranscriptTxt(AssetData $asset): string
    {
        try {
            $url = "https://stream.mux.com/{$asset->playbackId}/text/{$asset->captionTrackId}.txt";

            return Http::get($url, $this->buildSignedQuery(asset: $asset))
                ->throw()
                ->body();
        } catch (\Throwable $e) {
            throw VideoHostException::requestFailed(previous: $e);
        }
    }

    public function getTranscriptVtt(AssetData $asset): string
    {
        try {
            $url = "https://stream.mux.com/{$asset->playbackId}/text/{$asset->captionTrackId}.vtt";

            return Http::get($url, $this->buildSignedQuery(asset: $asset))
                ->throw()
                ->body();
        } catch (\Throwable $e) {
            throw VideoHostException::requestFailed(previous: $e);
        }
    }

    public function getThumbnail(AssetData $asset): ?string
    {
        try {
            $url = "https://image.mux.com/{$asset->playbackId}/thumbnail.webp";

            return Http::get($url, $this->buildSignedThumbnailQuery(asset: $asset))
                ->throw()
                ->body();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    public function generatePlaybackTokens(string $playbackId): array
    {
        if ($this->signingKeyId === null || $this->signingPrivateKey === null) {
            return [];
        }

        $expiry = 12 * 60 * 60;

        return [
            'playback' => $this->signToken(playbackId: $playbackId, audience: 'v', expirySeconds: $expiry),
            'thumbnail' => $this->signToken(playbackId: $playbackId, audience: 't', expirySeconds: $expiry),
            'storyboard' => $this->signToken(playbackId: $playbackId, audience: 's', expirySeconds: $expiry),
        ];
    }

    private function signToken(string $playbackId, string $audience, int $expirySeconds): string
    {
        $privateKey = base64_decode($this->signingPrivateKey);

        return JWT::encode(
            payload: [
                'sub' => $playbackId,
                'aud' => $audience,
                'exp' => time() + $expirySeconds,
                'kid' => $this->signingKeyId,
            ],
            key: $privateKey,
            alg: 'RS256',
            keyId: $this->signingKeyId,
        );
    }

    /**
     * @return array<string, string>
     */
    private function buildSignedQuery(AssetData $asset): array
    {
        if ($asset->signedPlayback === false || $this->signingKeyId === null || $this->signingPrivateKey === null) {
            return [];
        }

        return ['token' => $this->signToken(playbackId: $asset->playbackId, audience: 'v', expirySeconds: 300)];
    }

    /**
     * @return array<string, string>
     */
    private function buildSignedThumbnailQuery(AssetData $asset): array
    {
        if ($asset->signedPlayback === false || $this->signingKeyId === null || $this->signingPrivateKey === null) {
            return [];
        }

        return ['token' => $this->signToken(playbackId: $asset->playbackId, audience: 't', expirySeconds: 300)];
    }
}
