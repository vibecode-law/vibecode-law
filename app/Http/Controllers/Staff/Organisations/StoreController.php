<?php

namespace App\Http\Controllers\Staff\Organisations;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\OrganisationStoreRequest;
use App\Models\Organisation\Organisation;
use App\Services\Organisation\OrganisationThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(OrganisationStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Organisation::class);

        $organisation = Organisation::create(
            $request->safe()->except(['thumbnail', 'thumbnail_crops'])
        );

        $this->handleThumbnail(request: $request, organisation: $organisation);

        return Redirect::back()
            ->with('flash', [
                'message' => ['message' => 'Organisation created successfully.', 'type' => 'success'],
                'created_organisation' => [
                    'id' => $organisation->id,
                    'name' => $organisation->name,
                ],
            ]);
    }

    private function handleThumbnail(OrganisationStoreRequest $request, Organisation $organisation): void
    {
        if ($request->hasFile('thumbnail') === false) {
            return;
        }

        new OrganisationThumbnailService(organisation: $organisation)
            ->fromUploadedFile(
                file: $request->file('thumbnail'),
                crops: $request->validated('thumbnail_crops'),
            );
    }
}
