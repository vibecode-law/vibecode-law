<?php

namespace App\Actions\ShowcaseDraft;

use App\Enums\ShowcaseDraftStatus;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateShowcaseDraftAction
{
    public function create(Showcase $showcase): ShowcaseDraft
    {
        return DB::transaction(function () use ($showcase) {
            /** @var ShowcaseDraft $draft */
            $draft = ShowcaseDraft::query()->create([
                'showcase_id' => $showcase->id,
                'title' => $showcase->title,
                'tagline' => $showcase->tagline,
                'description' => $showcase->description,
                'key_features' => $showcase->key_features,
                'help_needed' => $showcase->help_needed,
                'url' => $showcase->url,
                'video_url' => $showcase->video_url,
                'source_status' => $showcase->source_status,
                'source_url' => $showcase->source_url,
                'thumbnail_extension' => $showcase->thumbnail_extension,
                'thumbnail_crop' => $showcase->thumbnail_crop,
                'status' => ShowcaseDraftStatus::Draft,
            ]);

            // Copy thumbnail if exists
            if ($showcase->thumbnail_extension !== null) {
                $sourcePath = "showcase/{$showcase->id}/thumbnail.{$showcase->thumbnail_extension}";
                $destPath = "showcase-drafts/{$draft->id}/thumbnail.{$showcase->thumbnail_extension}";

                if (Storage::disk('public')->exists($sourcePath)) {
                    Storage::disk('public')->copy($sourcePath, $destPath);
                }
            }

            // Copy practice areas
            $draft->practiceAreas()->sync($showcase->practiceAreas->pluck('id'));

            // Create draft images for existing showcase images
            $showcase->load('images');
            foreach ($showcase->images as $image) {
                $draft->images()->create([
                    'original_image_id' => $image->id,
                    'action' => ShowcaseDraftImage::ACTION_KEEP,
                    'path' => null,
                    'filename' => $image->filename,
                    'alt_text' => $image->alt_text,
                    'order' => $image->order,
                ]);
            }

            return $draft;
        });
    }
}
