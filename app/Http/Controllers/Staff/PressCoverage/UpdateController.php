<?php

namespace App\Http\Controllers\Staff\PressCoverage;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\PressCoverageUpdateRequest;
use App\Models\PressCoverage;
use App\Services\PressCoverage\PressCoverageMediaService;
use Illuminate\Http\RedirectResponse;

class UpdateController extends BaseController
{
    public function __invoke(
        PressCoverageUpdateRequest $request,
        PressCoverage $pressCoverage,
        PressCoverageMediaService $mediaService
    ): RedirectResponse {
        $this->authorize('update', $pressCoverage);

        $pressCoverage->update(
            $request->safe()->except(['thumbnail', 'thumbnail_crop', 'remove_thumbnail'])
        );

        // Handle thumbnail removal
        if ($request->boolean('remove_thumbnail')) {
            $mediaService->removeThumbnail($pressCoverage);
        }
        // Handle thumbnail upload
        elseif ($request->hasFile('thumbnail')) {
            $mediaService->storeThumbnail(
                pressCoverage: $pressCoverage,
                file: $request->file('thumbnail'),
                crop: $request->validated('thumbnail_crop')
            );
        }

        return redirect()->back();
    }
}
