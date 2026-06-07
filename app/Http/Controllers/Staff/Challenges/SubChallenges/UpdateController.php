<?php

namespace App\Http\Controllers\Staff\Challenges\SubChallenges;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\SubChallengeUpdateRequest;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(SubChallengeUpdateRequest $request, Challenge $challenge, SubChallenge $subChallenge): RedirectResponse
    {
        $this->authorize('manageSubChallenges', $challenge);

        $subChallenge->update($request->validated());

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Sub-challenge updated successfully.', 'type' => 'success'],
        ]);
    }
}
