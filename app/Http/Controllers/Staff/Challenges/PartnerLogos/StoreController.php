<?php

namespace App\Http\Controllers\Staff\Challenges\PartnerLogos;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\PartnerLogoStoreRequest;
use App\Models\Challenge\Challenge;
use App\Services\Challenge\ChallengePartnerLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(PartnerLogoStoreRequest $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('managePartnerLogos', $challenge);

        $service = new ChallengePartnerLogoService(challenge: $challenge);
        $service->store(files: $request->file('logos'));

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Partner logos uploaded successfully.', 'type' => 'success'],
        ]);
    }
}
