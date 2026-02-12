<?php

namespace App\Http\Controllers\Staff\Organisations;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\OrganisationUpdateRequest;
use App\Models\Organisation\Organisation;
use App\Services\Organisation\OrganisationThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(OrganisationUpdateRequest $request, Organisation $organisation): RedirectResponse
    {
        $this->authorize('update', $organisation);

        $organisation->update(
            $request->safe()->except(['thumbnail', 'thumbnail_crops', 'remove_thumbnail'])
        );

        $this->handleThumbnail(request: $request, organisation: $organisation);

        return Redirect::back()
            ->with('flash', [
                'message' => ['message' => 'Organisation updated successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(OrganisationUpdateRequest $request, Organisation $organisation): void
    {
        $thumbnailService = new OrganisationThumbnailService(organisation: $organisation);

        if ($request->boolean('remove_thumbnail') === true) {
            $thumbnailService->delete();

            return;
        }

        if ($request->hasFile('thumbnail') === true) {
            $thumbnailService->fromUploadedFile(
                file: $request->file('thumbnail'),
                crops: $request->validated('thumbnail_crops'),
            );

            return;
        }

        if ($request->has('thumbnail_crops') === true) {
            $thumbnailService->updateCrops(
                crops: $request->validated('thumbnail_crops'),
            );
        }
    }
}
