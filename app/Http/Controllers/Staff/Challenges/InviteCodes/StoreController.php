<?php

namespace App\Http\Controllers\Staff\Challenges\InviteCodes;

use App\Actions\Challenge\GenerateInviteCodeAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\ChallengeInviteCodeStoreRequest;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(ChallengeInviteCodeStoreRequest $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('manageInviteCodes', $challenge);

        $code = (new GenerateInviteCodeAction)->generate();

        ChallengeInviteCode::create([
            'challenge_id' => $challenge->id,
            'code' => $code,
            'label' => $request->validated('label'),
            'scope' => $request->validated('scope'),
        ]);

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Invite code created successfully.', 'type' => 'success'],
        ]);
    }
}
