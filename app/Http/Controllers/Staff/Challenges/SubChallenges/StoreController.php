<?php

namespace App\Http\Controllers\Staff\Challenges\SubChallenges;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\SubChallengeStoreRequest;
use App\Models\Challenge\Challenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(SubChallengeStoreRequest $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('manageSubChallenges', $challenge);

        $challenge->subChallenges()->create([
            ...$request->validated(),
            'order' => (int) $challenge->subChallenges()->max('order') + 1,
        ]);

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Sub-challenge created successfully.', 'type' => 'success'],
        ]);
    }
}
