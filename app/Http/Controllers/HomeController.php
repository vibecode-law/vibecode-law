<?php

namespace App\Http\Controllers;

use App\Http\Resources\Showcase\ShowcaseResource;
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
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted')
                ->toArray()
            )
            ->toArray();

        return Inertia::render('home', [
            'showcasesByMonth' => $showcasesByMonth,
        ]);
    }
}
