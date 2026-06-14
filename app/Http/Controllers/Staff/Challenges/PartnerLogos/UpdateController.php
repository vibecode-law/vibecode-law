<?php

namespace App\Http\Controllers\Staff\Challenges\PartnerLogos;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\PartnerLogoUpdateRequest;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\ChallengePartnerLogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(PartnerLogoUpdateRequest $request, Challenge $challenge, ChallengePartnerLogo $partnerLogo): RedirectResponse
    {
        $this->authorize('managePartnerLogos', $challenge);

        $partnerLogo->update($request->validated());

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Partner logo updated.', 'type' => 'success'],
        ]);
    }
}
