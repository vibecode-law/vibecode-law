<?php

namespace App\Http\Controllers\Staff\Challenges\PartnerLogos;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengePartnerLogoResource;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Models\Challenge\Challenge;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(Challenge $challenge): Response
    {
        $this->authorize('managePartnerLogos', $challenge);

        $logos = $challenge->partnerLogos()->get();

        return Inertia::render('staff-area/challenges/partner-logos/index', [
            'challenge' => ChallengeResource::from($challenge)
                ->include('live_view_enabled', 'live_view_access_token')
                ->only('id', 'slug', 'title', 'live_view_enabled', 'live_view_access_token'),
            'partnerLogos' => ChallengePartnerLogoResource::collect($logos, DataCollection::class),
        ]);
    }
}
