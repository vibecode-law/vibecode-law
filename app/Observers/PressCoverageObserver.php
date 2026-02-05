<?php

namespace App\Observers;

use App\Models\PressCoverage;
use Illuminate\Support\Facades\Storage;

class PressCoverageObserver
{
    public function deleted(PressCoverage $pressCoverage): void
    {
        // Clean up thumbnail when press coverage is deleted
        if ($pressCoverage->thumbnail_extension !== null) {
            $storagePath = "press-coverage/{$pressCoverage->id}";
            Storage::disk('public')->delete("{$storagePath}/thumbnail.{$pressCoverage->thumbnail_extension}");
        }
    }
}
