<?php

namespace App\Http\Controllers\Staff\PressCoverage;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\PressCoverageStoreRequest;
use App\Models\PressCoverage;
use App\Services\PressCoverage\PressCoverageMediaService;
use Illuminate\Http\RedirectResponse;

class StoreController extends BaseController
{
    public function __invoke(PressCoverageStoreRequest $request, PressCoverageMediaService $mediaService): RedirectResponse
    {
        $this->authorize('create', PressCoverage::class);

        $pressCoverage = PressCoverage::create(
            $request->safe()->except(['thumbnail', 'thumbnail_crop'])
        );

        // Handle thumbnail upload with crop
        if ($request->hasFile('thumbnail')) {
            $mediaService->storeThumbnail(
                pressCoverage: $pressCoverage,
                file: $request->file('thumbnail'),
                crop: $request->validated('thumbnail_crop')
            );
        }

        return redirect()->back();
    }
}
