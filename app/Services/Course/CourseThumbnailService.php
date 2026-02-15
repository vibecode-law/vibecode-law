<?php

namespace App\Services\Course;

use App\Models\Course\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CourseThumbnailService
{
    public function __construct(
        protected Course $course
    ) {}

    /**
     * Store a thumbnail from an uploaded file.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function fromUploadedFile(UploadedFile $file, ?array $crops = null): void
    {
        $extension = $file->extension();
        $path = "course/{$this->course->id}/thumbnail.{$extension}";

        // Delete old thumbnail if exists
        if ($this->course->thumbnail_extension !== null) {
            $oldPath = "course/{$this->course->id}/thumbnail.{$this->course->thumbnail_extension}";
            Storage::disk('public')->delete($oldPath);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $file->getContent()
        );

        $this->course->thumbnail_extension = $extension;
        $this->course->thumbnail_crops = $crops;
        $this->course->save();
    }

    /**
     * Update crop data for the existing thumbnail.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function updateCrops(?array $crops): void
    {
        $this->course->thumbnail_crops = $crops;
        $this->course->save();
    }

    /**
     * Delete the course's thumbnail.
     */
    public function delete(): void
    {
        if ($this->course->thumbnail_extension === null) {
            return;
        }

        $path = "course/{$this->course->id}/thumbnail.{$this->course->thumbnail_extension}";
        Storage::disk('public')->delete($path);

        $this->course->thumbnail_extension = null;
        $this->course->thumbnail_crops = null;
        $this->course->save();
    }
}
