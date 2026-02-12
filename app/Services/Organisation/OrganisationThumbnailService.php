<?php

namespace App\Services\Organisation;

use App\Models\Organisation\Organisation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OrganisationThumbnailService
{
    public function __construct(
        protected Organisation $organisation
    ) {}

    /**
     * Store a thumbnail from an uploaded file.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function fromUploadedFile(UploadedFile $file, ?array $crops = null): void
    {
        $extension = $file->extension();
        $path = "organisation/{$this->organisation->id}/thumbnail.{$extension}";

        // Delete old thumbnail if exists
        if ($this->organisation->thumbnail_extension !== null) {
            $oldPath = "organisation/{$this->organisation->id}/thumbnail.{$this->organisation->thumbnail_extension}";
            Storage::disk('public')->delete($oldPath);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $file->getContent()
        );

        $this->organisation->thumbnail_extension = $extension;
        $this->organisation->thumbnail_crops = $crops;
        $this->organisation->save();
    }

    /**
     * Update crop data for the existing thumbnail.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function updateCrops(?array $crops): void
    {
        $this->organisation->thumbnail_crops = $crops;
        $this->organisation->save();
    }

    /**
     * Delete the organisation's thumbnail.
     */
    public function delete(): void
    {
        if ($this->organisation->thumbnail_extension === null) {
            return;
        }

        $path = "organisation/{$this->organisation->id}/thumbnail.{$this->organisation->thumbnail_extension}";
        Storage::disk('public')->delete($path);

        $this->organisation->thumbnail_extension = null;
        $this->organisation->thumbnail_crops = null;
        $this->organisation->save();
    }
}
