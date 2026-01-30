<?php

namespace App\Http\Controllers\Showcase\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Queries\Showcase\PublicShowcaseQuery;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\PaginatedDataCollection;

class ShowcaseIndexController extends BaseController
{
    public function __invoke(PublicShowcaseQuery $query): Response
    {
        $showcases = $query()
            ->paginate(perPage: 20);

        return Inertia::render('showcase/public/index', [
            'showcases' => ShowcaseResource::collect($showcases, PaginatedDataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted'),
            'availableFilters' => [
                'months' => $this->getAvailableMonths(),
                'practiceAreas' => PracticeAreaResource::collect($this->getPracticeAreas()),
            ],
            'activeFilter' => null,
        ]);
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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PracticeArea>
     */
    private function getPracticeAreas(): \Illuminate\Database\Eloquent\Collection
    {
        return PracticeArea::query()
            ->orderBy('name')
            ->get();
    }
}
