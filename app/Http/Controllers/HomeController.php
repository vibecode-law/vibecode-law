<?php

namespace App\Http\Controllers;

use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class HomeController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $userId = Auth::id();

        $relations = array_filter([
            'user',
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
        ]);

        $currentMonth = now()->format('Y-m');

        $showcasesByMonth = Showcase::query()
            ->publiclyVisible()
            ->whereBetween('submitted_date', [
                now()->subMonthsNoOverflow(2)->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->with($relations)
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count')
            ->get()
            ->groupBy(fn (Showcase $showcase): string => $showcase->submitted_date->format('Y-m'))
            ->map(fn ($showcases) => ShowcaseResource::collect($showcases, DataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted', 'view_count', 'user')
                ->toArray()
            )
            ->toArray();

        if (! array_key_exists($currentMonth, $showcasesByMonth)) {
            $showcasesByMonth = [$currentMonth => []] + $showcasesByMonth;
        }

        $recentShowcases = Showcase::query()
            ->publiclyVisible()
            ->with('user')
            ->orderByDesc('submitted_date')
            ->limit(3)
            ->get();

        $recentShowcases = ShowcaseResource::collect($recentShowcases, DataCollection::class)
            ->only('id', 'slug', 'title', 'thumbnail_url', 'thumbnail_rect_string', 'user')
            ->toArray();

        $activeChallenges = Challenge::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->withCount('showcases')
            ->orderByDesc('showcases_count')
            ->get();

        $activeChallenges = ChallengeResource::collect($activeChallenges, DataCollection::class)
            ->include('showcases_count')
            ->only('id', 'slug', 'title', 'thumbnail_url', 'thumbnail_rect_strings', 'showcases_count')
            ->toArray();

        return Inertia::render('home', [
            'showcasesByMonth' => $showcasesByMonth,
            'recentShowcases' => $recentShowcases,
            'activeChallenges' => $activeChallenges,
        ]);
    }
}
