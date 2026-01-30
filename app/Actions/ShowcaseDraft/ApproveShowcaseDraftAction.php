<?php

namespace App\Actions\ShowcaseDraft;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApproveShowcaseDraftAction
{
    public function approve(ShowcaseDraft $draft): Showcase
    {
        /** @var Showcase */
        return DB::transaction(function () use ($draft): Showcase {
            /** @var Showcase $showcase */
            $showcase = $draft->showcase;

            // Update showcase fields
            $showcase->update([
                'title' => $draft->title,
                'tagline' => $draft->tagline,
                'description' => $draft->description,
                'key_features' => $draft->key_features,
                'help_needed' => $draft->help_needed,
                'url' => $draft->url,
                'video_url' => $draft->video_url,
                'source_status' => $draft->source_status,
                'source_url' => $draft->source_url,
            ]);

            // Handle thumbnail
            $this->handleThumbnail(draft: $draft, showcase: $showcase);

            // Sync practice areas
            $showcase->practiceAreas()->sync($draft->practiceAreas->pluck('id'));

            // Handle images
            $this->handleImages(draft: $draft, showcase: $showcase);

            // Delete the draft (this also cleans up draft files via model event)
            $draft->delete();

            /** @var Showcase */
            return $showcase->fresh();
        });
    }

    private function handleThumbnail(ShowcaseDraft $draft, Showcase $showcase): void
    {
        // If draft has a different thumbnail, replace the showcase thumbnail
        $draftPath = "showcase-drafts/{$draft->id}/thumbnail.{$draft->thumbnail_extension}";
        $showcasePath = "showcase/{$showcase->id}/thumbnail.{$draft->thumbnail_extension}";

        if ($draft->thumbnail_extension !== null && Storage::disk('public')->exists($draftPath)) {
            // Delete old showcase thumbnail (always delete to ensure move succeeds)
            if ($showcase->thumbnail_extension !== null) {
                Storage::disk('public')->delete("showcase/{$showcase->id}/thumbnail.{$showcase->thumbnail_extension}");
            }

            // Move draft thumbnail to showcase
            Storage::disk('public')->move($draftPath, $showcasePath);

            $showcase->update([
                'thumbnail_extension' => $draft->thumbnail_extension,
                'thumbnail_crop' => $draft->thumbnail_crop,
            ]);
        } elseif ($draft->thumbnail_extension === null && $showcase->thumbnail_extension !== null) {
            // Thumbnail was removed in draft
            Storage::disk('public')->delete("showcase/{$showcase->id}/thumbnail.{$showcase->thumbnail_extension}");
            $showcase->update([
                'thumbnail_extension' => null,
                'thumbnail_crop' => null,
            ]);
        } else {
            // Just update crop if thumbnail not changed
            $showcase->update([
                'thumbnail_crop' => $draft->thumbnail_crop,
            ]);
        }
    }

    private function handleImages(ShowcaseDraft $draft, Showcase $showcase): void
    {
        $draft->load(['images.originalImage']);

        /** @var ShowcaseDraftImage $draftImage */
        foreach ($draft->images as $draftImage) {
            match ($draftImage->action) {
                ShowcaseDraftImage::ACTION_KEEP => $this->handleKeepImage(draftImage: $draftImage),
                ShowcaseDraftImage::ACTION_REMOVE => $this->handleRemoveImage(draftImage: $draftImage),
                ShowcaseDraftImage::ACTION_ADD => $this->handleAddImage(draftImage: $draftImage, showcase: $showcase),
                default => null,
            };
        }
    }

    private function handleKeepImage(ShowcaseDraftImage $draftImage): void
    {
        // Update order and alt_text on the original image
        $draftImage->originalImage?->update([
            'order' => $draftImage->order,
            'alt_text' => $draftImage->alt_text,
        ]);
    }

    private function handleRemoveImage(ShowcaseDraftImage $draftImage): void
    {
        // Delete the original image (model event handles file deletion)
        $draftImage->originalImage?->delete();
    }

    private function handleAddImage(ShowcaseDraftImage $draftImage, Showcase $showcase): void
    {
        if ($draftImage->path === null) {
            return;
        }

        // Move the image from draft folder to showcase folder
        $newPath = "showcase/{$showcase->id}/images/".Str::uuid().'.'.pathinfo($draftImage->path, PATHINFO_EXTENSION);

        Storage::disk('public')->move($draftImage->path, $newPath);

        // Create new ShowcaseImage
        $showcase->images()->create([
            'path' => $newPath,
            'filename' => $draftImage->filename,
            'alt_text' => $draftImage->alt_text,
            'order' => $draftImage->order,
        ]);
    }
}
