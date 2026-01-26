<?php

namespace App\Services\Showcase;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShowcaseMediaService
{
    /**
     * @param  array{x: int, y: int, width: int, height: int}|null  $crop
     */
    public function storeThumbnail(Showcase|ShowcaseDraft $model, UploadedFile $file, ?array $crop): void
    {
        $extension = $file->getClientOriginalExtension();
        $storagePath = $this->getStoragePath(model: $model);

        // Delete old thumbnail if exists
        if ($model->thumbnail_extension !== null) {
            Storage::disk('public')->delete("{$storagePath}/thumbnail.{$model->thumbnail_extension}");
        }

        $file->storeAs(
            path: $storagePath,
            name: 'thumbnail.'.$extension,
            options: 'public'
        );

        $model->update([
            'thumbnail_extension' => $extension,
            'thumbnail_crop' => $crop,
        ]);
    }

    public function removeThumbnail(Showcase|ShowcaseDraft $model): void
    {
        if ($model->thumbnail_extension === null) {
            return;
        }

        $storagePath = $this->getStoragePath(model: $model);
        Storage::disk('public')->delete("{$storagePath}/thumbnail.{$model->thumbnail_extension}");

        $model->update([
            'thumbnail_extension' => null,
            'thumbnail_crop' => null,
        ]);
    }

    /**
     * @param  array<UploadedFile>  $files
     */
    public function storeImages(Showcase|ShowcaseDraft $model, array $files): void
    {
        $order = $model->images()->max('order') ?? 0;
        $storagePath = $this->getStoragePath(model: $model).'/images';
        $isDraft = $model instanceof ShowcaseDraft;

        foreach ($files as $file) {
            $path = $file->storeAs(
                path: $storagePath,
                name: Str::uuid().'.'.$file->getClientOriginalExtension(),
                options: 'public'
            );

            $imageData = [
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'order' => ++$order,
            ];

            if ($isDraft === true) {
                $imageData['original_image_id'] = null;
                $imageData['action'] = ShowcaseDraftImage::ACTION_ADD;
            }

            $model->images()->create($imageData);
        }
    }

    private function getStoragePath(Showcase|ShowcaseDraft $model): string
    {
        if ($model instanceof ShowcaseDraft) {
            return 'showcase-drafts/'.$model->id;
        }

        return 'showcase/'.$model->id;
    }
}
