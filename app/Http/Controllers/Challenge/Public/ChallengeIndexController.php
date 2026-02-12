<?php

namespace App\Http\Controllers\Challenge\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Models\Challenge\Challenge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ChallengeIndexController extends BaseController
{
    public function __invoke(): Response
    {
        if (Config::get('app.challenges_enabled') === false && Auth::user()?->is_admin !== true) {
            abort(404);
        }

        $activeChallenges = Challenge::query()
            ->where('is_active', true)
            ->with('organisation')
            ->withCount('showcases')
            ->withTotalUpvotesCount()
            ->orderBy('total_upvotes_count', 'desc')
            ->get();

        $featuredChallenges = $activeChallenges->where('is_featured', true)->values();
        $nonFeaturedChallenges = $activeChallenges->where('is_featured', '!==', true)->values();

        return Inertia::render('challenge/public/index', [
            'featuredChallenges' => ChallengeResource::collect($featuredChallenges, DataCollection::class)
                ->include('organisation', 'showcases_count', 'total_upvotes_count')
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'thumbnail_url',
                    'thumbnail_rect_strings',
                    'starts_at',
                    'ends_at',
                    'is_featured',
                    'organisation',
                    'showcases_count',
                    'total_upvotes_count',
                ),
            'activeChallenges' => ChallengeResource::collect($nonFeaturedChallenges, DataCollection::class)
                ->include('organisation', 'showcases_count', 'total_upvotes_count')
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'thumbnail_url',
                    'thumbnail_rect_strings',
                    'starts_at',
                    'ends_at',
                    'organisation',
                    'showcases_count',
                    'total_upvotes_count',
                ),
        ]);
    }
}
