<?php

namespace App\Services\Course;

use App\Models\Course\Lesson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $filename = Str::random(40).'.'.$file->extension();
        $path = "lesson/{$this->lesson->id}/{$filename}";

        // Delete old thumbnail if exists
        if ($this->lesson->thumbnail_filename !== null) {
            $oldPath = "lesson/{$this->lesson->id}/{$this->lesson->thumbnail_filename}";
            Storage::disk('public')->delete($oldPath);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $file->getContent()
        );

        $this->lesson->thumbnail_filename = $filename;
        $this->lesson->thumbnail_crops = $crops;
        $this->lesson->save();
    }

    /**
     * Store a thumbnail from raw image contents.
     */
    public function fromContents(string $contents, string $extension): void
    {
        $filename = Str::random(40).'.'.$extension;
        $path = "lesson/{$this->lesson->id}/{$filename}";

        if ($this->lesson->thumbnail_filename !== null) {
            $oldPath = "lesson/{$this->lesson->id}/{$this->lesson->thumbnail_filename}";
            Storage::disk('public')->delete($oldPath);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $contents
        );

        $this->lesson->thumbnail_filename = $filename;
        $this->lesson->thumbnail_crops = null;
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
        if ($this->lesson->thumbnail_filename === null) {
            return;
        }

        $path = "lesson/{$this->lesson->id}/{$this->lesson->thumbnail_filename}";
        Storage::disk('public')->delete($path);

        $this->lesson->thumbnail_filename = null;
        $this->lesson->thumbnail_crops = null;
        $this->lesson->save();
    }
}
