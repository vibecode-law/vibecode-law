<?php

namespace App\Observers;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;

class TestimonialObserver
{
    public function deleted(Testimonial $testimonial): void
    {
        // Clean up avatar when testimonial is deleted
        if ($testimonial->avatar_path !== null) {
            Storage::disk('public')->delete($testimonial->avatar_path);
        }
    }
}
