<?php

namespace App\Services\PressCoverage;

use App\Models\PressCoverage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PressCoverageMediaService
{
    /**
     * Store thumbnail - follows ShowcaseMediaService pattern exactly.
     *
     * @param  array{x: int, y: int, width: int, height: int}|null  $crop
     */
    public function storeThumbnail(PressCoverage $pressCoverage, UploadedFile $file, ?array $crop): void
    {
        $extension = $file->getClientOriginalExtension();
        $storagePath = "press-coverage/{$pressCoverage->id}";

        // Delete old thumbnail if exists
        if ($pressCoverage->thumbnail_extension !== null) {
            Storage::disk('public')->delete("{$storagePath}/thumbnail.{$pressCoverage->thumbnail_extension}");
        }

        $file->storeAs(
            path: $storagePath,
            name: 'thumbnail.'.$extension,
            options: 'public'
        );

        $pressCoverage->update([
            'thumbnail_extension' => $extension,
            'thumbnail_crop' => $crop,
        ]);
    }

    public function removeThumbnail(PressCoverage $pressCoverage): void
    {
        if ($pressCoverage->thumbnail_extension === null) {
            return;
        }

        $storagePath = "press-coverage/{$pressCoverage->id}";
        Storage::disk('public')->delete("{$storagePath}/thumbnail.{$pressCoverage->thumbnail_extension}");

        $pressCoverage->update([
            'thumbnail_extension' => null,
            'thumbnail_crop' => null,
        ]);
    }
}
