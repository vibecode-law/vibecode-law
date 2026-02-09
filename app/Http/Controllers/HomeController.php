<?php

namespace App\Http\Controllers;

use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class HomeController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $userId = Auth::id();

        $showcasesByMonth = collect([0, 1, 2])
            ->mapWithKeys(fn (int $offset): array => $this
                ->getShowcasesForMonth(
                    monthStart: now()->subMonthsNoOverflow($offset)->startOfMonth(),
                    userId: $userId,
                )
            )
            ->filter()
            ->toArray();

        return Inertia::render('home', [
            'showcasesByMonth' => $showcasesByMonth,
        ]);
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    private function getShowcasesForMonth(Carbon $monthStart, ?int $userId): array
    {
        $showcases = $this->queryTopShowcases(
            monthStart: $monthStart,
            monthEnd: $monthStart->copy()->endOfMonth(),
            userId: $userId,
        );

        if ($showcases->isEmpty()) {
            return [];
        }

        return [
            $monthStart->format('Y-m') => ShowcaseResource::collect($showcases, DataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted')
                ->toArray(),
        ];
    }

    /**
     * @return Collection<int,Showcase>
     */
    private function queryTopShowcases(Carbon $monthStart, Carbon $monthEnd, ?int $userId): Collection
    {
        $relations = array_filter([
            'user',
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
        ]);

        return Showcase::query()
            ->publiclyVisible()
            ->whereBetween('submitted_date', [$monthStart, $monthEnd])
            ->with($relations)
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count')
            ->limit(5)
            ->get();
    }
}
