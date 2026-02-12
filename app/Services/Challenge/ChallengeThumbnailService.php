<?php

namespace App\Services\Challenge;

use App\Models\Challenge\Challenge;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ChallengeThumbnailService
{
    public function __construct(
        protected Challenge $challenge
    ) {}

    /**
     * Store a thumbnail from an uploaded file.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function fromUploadedFile(UploadedFile $file, ?array $crops = null): void
    {
        $extension = $file->extension();
        $path = "challenge/{$this->challenge->id}/thumbnail.{$extension}";

        // Delete old thumbnail if exists
        if ($this->challenge->thumbnail_extension !== null) {
            $oldPath = "challenge/{$this->challenge->id}/thumbnail.{$this->challenge->thumbnail_extension}";
            Storage::disk('public')->delete($oldPath);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $file->getContent()
        );

        $this->challenge->thumbnail_extension = $extension;
        $this->challenge->thumbnail_crops = $crops;
        $this->challenge->save();
    }

    /**
     * Update crop data for the existing thumbnail.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function updateCrops(?array $crops): void
    {
        $this->challenge->thumbnail_crops = $crops;
        $this->challenge->save();
    }

    /**
     * Delete the challenge's thumbnail.
     */
    public function delete(): void
    {
        if ($this->challenge->thumbnail_extension === null) {
            return;
        }

        $path = "challenge/{$this->challenge->id}/thumbnail.{$this->challenge->thumbnail_extension}";
        Storage::disk('public')->delete($path);

        $this->challenge->thumbnail_extension = null;
        $this->challenge->thumbnail_crops = null;
        $this->challenge->save();
    }
}
