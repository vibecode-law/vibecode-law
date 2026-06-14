<?php

namespace App\Http\Controllers\Staff\Challenges\PartnerLogos;

use App\Http\Controllers\BaseController;
use App\Models\Challenge\Challenge;
use App\Services\Challenge\ChallengePartnerLogoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReorderController extends BaseController
{
    public function __invoke(Request $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('managePartnerLogos', $challenge);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:challenge_partner_logos,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        $service = new ChallengePartnerLogoService(challenge: $challenge);
        $service->reorder(items: $validated['items']);

        return Redirect::back();
    }
}
