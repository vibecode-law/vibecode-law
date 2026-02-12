<?php

namespace App\Http\Controllers\Staff\Challenges;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\ChallengeUpdateRequest;
use App\Models\Challenge\Challenge;
use App\Services\Challenge\ChallengeThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(ChallengeUpdateRequest $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('update', $challenge);

        $challenge->update(
            $request->safe()->except(['thumbnail', 'thumbnail_crops', 'remove_thumbnail'])
        );

        $this->handleThumbnail(request: $request, challenge: $challenge);

        return Redirect::route('staff.challenges.edit', $challenge)
            ->with('flash', [
                'message' => ['message' => 'Challenge updated successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(ChallengeUpdateRequest $request, Challenge $challenge): void
    {
        $thumbnailService = new ChallengeThumbnailService(challenge: $challenge);

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
