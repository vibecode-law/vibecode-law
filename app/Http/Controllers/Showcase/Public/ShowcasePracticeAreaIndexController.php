<?php

namespace App\Http\Controllers\Showcase\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\PracticeArea;
use App\Queries\Showcase\PublicShowcaseQuery;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\PaginatedDataCollection;

class ShowcasePracticeAreaIndexController extends BaseController
{
    public function __invoke(PracticeArea $practiceArea, PublicShowcaseQuery $query): Response
    {
        $showcases = $query()
            ->whereHas(
                relation: 'practiceAreas',
                callback: fn (Builder $query) => $query->where('practice_areas.id', $practiceArea->id),
            )
            ->paginate(perPage: 20);

        return Inertia::render('showcase/public/index', [
            'showcases' => ShowcaseResource::collect($showcases, PaginatedDataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted', 'view_count', 'user'),
            'availableFilters' => [
                'practiceAreas' => PracticeAreaResource::collect($this->getPracticeAreas()),
            ],
            'activeFilter' => [
                'type' => 'practice_area',
                'practiceArea' => PracticeAreaResource::from($practiceArea),
            ],
        ]);
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
