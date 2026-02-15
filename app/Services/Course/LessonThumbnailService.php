<?php

namespace App\Services\Course;

use App\Models\Course\Lesson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LessonThumbnailService
{
    public function __construct(
        protected Lesson $lesson
    ) {}

    /**
     * Store a thumbnail from an uploaded file.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function fromUploadedFile(UploadedFile $file, ?array $crops = null): void
    {
        $extension = $file->extension();
        $path = "lesson/{$this->lesson->id}/thumbnail.{$extension}";

        // Delete old thumbnail if exists
        if ($this->lesson->thumbnail_extension !== null) {
            $oldPath = "lesson/{$this->lesson->id}/thumbnail.{$this->lesson->thumbnail_extension}";
            Storage::disk('public')->delete($oldPath);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $file->getContent()
        );

        $this->lesson->thumbnail_extension = $extension;
        $this->lesson->thumbnail_crops = $crops;
        $this->lesson->save();
    }

    /**
     * Update crop data for the existing thumbnail.
     *
     * @param  array<string, array{x: int, y: int, width: int, height: int}>|null  $crops
     */
    public function updateCrops(?array $crops): void
    {
        $this->lesson->thumbnail_crops = $crops;
        $this->lesson->save();
    }

    /**
     * Delete the lesson's thumbnail.
     */
    public function delete(): void
    {
        if ($this->lesson->thumbnail_extension === null) {
            return;
        }

        $path = "lesson/{$this->lesson->id}/thumbnail.{$this->lesson->thumbnail_extension}";
        Storage::disk('public')->delete($path);

        $this->lesson->thumbnail_extension = null;
        $this->lesson->thumbnail_crops = null;
        $this->lesson->save();
    }
}
