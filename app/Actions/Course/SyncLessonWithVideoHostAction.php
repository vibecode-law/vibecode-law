<?php

namespace App\Actions\Course;

use App\Models\Course\Lesson;
use App\Services\Course\LessonThumbnailService;
use App\Services\Course\VttParserService;
use App\Services\VideoHost\Contracts\VideoHostService;
use Illuminate\Support\Facades\Storage;

class SyncLessonWithVideoHostAction
{
    public function __construct(private VideoHostService $videoHostService, private VttParserService $vttParserService) {}

    public function handle(Lesson $lesson, string $assetId): void
    {
        $asset = $this->videoHostService->getAsset(externalId: $assetId);

        if ($asset->playbackId === null) {
            throw new \RuntimeException('The asset does not have a playback ID. It may still be processing.');
        }

        if ($asset->captionTrackId === null) {
            throw new \RuntimeException('The asset does not have a subtitle track. Please add captions before syncing.');
        }

        $vtt = $this->videoHostService->getTranscriptVtt(asset: $asset);
        $txt = $this->videoHostService->getTranscriptTxt(asset: $asset);

        if (Storage::put(path: "lessons/{$lesson->id}/transcript.vtt", contents: $vtt) === false) {
            throw new \RuntimeException('Failed to store the VTT transcript file.');
        }

        if (Storage::put(path: "lessons/{$lesson->id}/transcript.txt", contents: $txt) === false) {
            throw new \RuntimeException('Failed to store the TXT transcript file.');
        }

        $this->vttParserService->parseAndPersist(vttContent: $vtt, lesson: $lesson);

        if ($lesson->transcriptLines()->exists() === false) {
            throw new \RuntimeException('Failed to parse transcript lines from the VTT file.');
        }

        if ($lesson->thumbnail_filename === null) {
            $thumbnailContents = $this->videoHostService->getThumbnail(asset: $asset);

            if ($thumbnailContents !== null) {
                $thumbnailService = new LessonThumbnailService(lesson: $lesson);
                $thumbnailService->fromContents(contents: $thumbnailContents, extension: 'webp');
            }
        }

        $lesson->update([
            'asset_id' => $asset->externalId,
            'host' => $this->videoHostService->host(),
            'playback_id' => $asset->playbackId,
            'duration_seconds' => $asset->duration !== null ? (int) round($asset->duration) : null,
        ]);
    }
}
