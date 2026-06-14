<?php

namespace App\Http\Controllers\Challenge\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ChallengeLiveController extends BaseController
{
    public function __invoke(Request $request, Challenge $challenge): Response
    {
        if ($challenge->is_active === false || $challenge->live_view_enabled === false) {
            abort(404);
        }

        if ($challenge->live_view_access_token !== null
            && hash_equals($challenge->live_view_access_token, (string) $request->query('key')) === false) {
            abort(404);
        }

        $challenge->load('subChallenges', 'partnerLogos');

        $showcases = $this->getShowcases(challenge: $challenge);

        return Inertia::render('challenge/public/live', [
            'challenge' => ChallengeResource::from($challenge)
                ->include('live_view_heading', 'live_view_subheading', 'sub_challenges', 'partner_logos')
                ->only('id', 'slug', 'title', 'tagline', 'live_view_heading', 'live_view_subheading', 'sub_challenges', 'partner_logos'),
            'showcases' => ShowcaseResource::collect($showcases, DataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'view_count', 'user', 'sub_challenge_id'),
        ]);
    }

    /**
     * @return Collection<int, Showcase>
     */
    private function getShowcases(Challenge $challenge): Collection
    {
        return $challenge->showcases()
            ->publiclyVisible()
            ->with('user')
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count')
            ->get();
    }
}
