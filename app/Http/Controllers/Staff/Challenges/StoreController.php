<?php

namespace App\Http\Controllers\Staff\Challenges;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\ChallengeStoreRequest;
use App\Models\Challenge\Challenge;
use App\Services\Challenge\ChallengeThumbnailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(ChallengeStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Challenge::class);

        $challenge = Challenge::create(
            $request->safe()->except(['thumbnail', 'thumbnail_crops'])
        );

        $this->handleThumbnail(request: $request, challenge: $challenge);

        return Redirect::route('staff.challenges.edit', $challenge)
            ->with('flash', [
                'message' => ['message' => 'Challenge created successfully.', 'type' => 'success'],
            ]);
    }

    private function handleThumbnail(ChallengeStoreRequest $request, Challenge $challenge): void
    {
        if ($request->hasFile('thumbnail') === false) {
            return;
        }

        new ChallengeThumbnailService(challenge: $challenge)
            ->fromUploadedFile(
                file: $request->file('thumbnail'),
                crops: $request->validated('thumbnail_crops'),
            );
    }
}
