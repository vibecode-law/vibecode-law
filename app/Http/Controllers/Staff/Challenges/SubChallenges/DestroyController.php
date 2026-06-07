<?php

namespace App\Http\Controllers\Staff\Challenges\SubChallenges;

use App\Http\Controllers\BaseController;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DestroyController extends BaseController
{
    public function __invoke(Challenge $challenge, SubChallenge $subChallenge): RedirectResponse
    {
        $this->authorize('manageSubChallenges', $challenge);

        $subChallenge->delete();

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Sub-challenge deleted.', 'type' => 'success'],
        ]);
    }
}
