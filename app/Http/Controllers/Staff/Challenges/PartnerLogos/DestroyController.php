<?php

namespace App\Http\Controllers\Staff\Challenges\PartnerLogos;

use App\Http\Controllers\BaseController;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use App\Services\Challenge\ChallengePartnerLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DestroyController extends BaseController
{
    public function __invoke(Challenge $challenge, ChallengePartnerLogo $partnerLogo): RedirectResponse
    {
        $this->authorize('managePartnerLogos', $challenge);

        $service = new ChallengePartnerLogoService(challenge: $challenge);
        $service->delete(logo: $partnerLogo);

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Partner logo deleted.', 'type' => 'success'],
        ]);
    }
}
