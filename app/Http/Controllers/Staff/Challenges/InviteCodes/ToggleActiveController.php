<?php

namespace App\Http\Controllers\Staff\Challenges\InviteCodes;

use App\Http\Controllers\BaseController;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengeInviteCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ToggleActiveController extends BaseController
{
    public function __invoke(Challenge $challenge, ChallengeInviteCode $inviteCode): RedirectResponse
    {
        $this->authorize('manageInviteCodes', $challenge);

        $inviteCode->update([
            'is_active' => $inviteCode->is_active === false,
        ]);

        $status = $inviteCode->is_active === true ? 'enabled' : 'disabled';

        return Redirect::back()->with('flash', [
            'message' => ['message' => "Invite code {$status}.", 'type' => 'success'],
        ]);
    }
}
