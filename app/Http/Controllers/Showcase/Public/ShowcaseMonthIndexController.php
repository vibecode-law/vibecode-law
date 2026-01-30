<?php

namespace App\Http\Controllers\Showcase\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Showcase\Showcase;
use App\Queries\Showcase\PublicShowcaseQuery;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\PaginatedDataCollection;

class ShowcaseMonthIndexController extends BaseController
{
    public function __invoke(string $month, PublicShowcaseQuery $query): Response
    {
        $this->validateMonthFormat(month: $month);

        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $showcases = $query()
            ->whereBetween('submitted_date', [$startOfMonth, $endOfMonth])
            ->paginate(perPage: 20);

        return Inertia::render('showcase/public/index', [
            'showcases' => ShowcaseResource::collect($showcases, PaginatedDataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted'),
            'availableFilters' => [
                'months' => $this->getAvailableMonths(),
            ],
            'activeFilter' => [
                'type' => 'month',
                'month' => $month,
            ],
        ]);
    }

    private function validateMonthFormat(string $month): void
    {
        if (preg_match(pattern: '/^\d{4}-(0[1-9]|1[0-2])$/', subject: $month) !== 1) {
            abort(404);
        }
    }

    /**
     * @return array<string>
     */
    private function getAvailableMonths(): array
    {
        return Showcase::query()
            ->publiclyVisible()
            ->pluck('submitted_date')
            ->map(fn (Carbon $date) => $date->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }
}
