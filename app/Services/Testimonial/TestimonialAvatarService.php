<?php

namespace App\Services\Testimonial;

use App\Models\Testimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestimonialAvatarService
{
    public function __construct(
        protected Testimonial $testimonial
    ) {}

    /**
     * Store an avatar from an uploaded file.
     * Follows the same pattern as UserAvatarService.
     *
     * @param  array{x: int, y: int, width: int, height: int}|null  $crop
     */
    public function fromUploadedFile(UploadedFile $file, ?array $crop = null): void
    {
        $extension = $file->extension();
        $path = 'testimonials/avatars/'.Str::uuid()->toString().'.'.$extension;

        // Delete old avatar if exists
        if ($this->testimonial->avatar_path !== null) {
            Storage::disk('public')->delete($this->testimonial->avatar_path);
        }

        Storage::disk('public')->put(
            path: $path,
            contents: $file->getContent()
        );

        $this->testimonial->avatar_path = $path;
        $this->testimonial->avatar_crop = $crop;
        $this->testimonial->save();
    }

    /**
     * Delete the testimonial's avatar.
     */
    public function delete(): void
    {
        if ($this->testimonial->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($this->testimonial->avatar_path);

        $this->testimonial->avatar_path = null;
        $this->testimonial->avatar_crop = null;
        $this->testimonial->save();
    }
}
