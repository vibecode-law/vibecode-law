<?php

namespace App\Http\Controllers\Challenge;

use App\Actions\Challenge\AcceptChallengeInviteCodeAction;
use App\Http\Controllers\BaseController;
use App\Models\Challenge\ChallengeInviteCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class AcceptInviteCodeController extends BaseController
{
    public function __invoke(string $code): Response|RedirectResponse
    {
        $inviteCode = $this->findValidInviteCode(code: $code);

        $user = Auth::user();

        if ($user === null) {
            return $this->renderGuestInvitePage();
        }

        return $this->acceptAndRedirect(inviteCode: $inviteCode, user: $user);
    }

    private function findValidInviteCode(string $code): ChallengeInviteCode
    {
        $inviteCode = ChallengeInviteCode::query()
            ->where('code', $code)
            ->with('challenge')
            ->first();

        if ($inviteCode === null || $inviteCode->is_active === false || $inviteCode->challenge->is_active === false) {
            abort(404);
        }

        return $inviteCode;
    }

    private function renderGuestInvitePage(): Response
    {
        Session::put('url.intended', URL::current());

        return Inertia::render('challenge/invite');
    }

    private function acceptAndRedirect(ChallengeInviteCode $inviteCode, User $user): RedirectResponse
    {
        (new AcceptChallengeInviteCodeAction)->accept(
            inviteCode: $inviteCode,
            user: $user,
        );

        return Redirect::route('inspiration.challenges.show', $inviteCode->challenge);
    }
}
