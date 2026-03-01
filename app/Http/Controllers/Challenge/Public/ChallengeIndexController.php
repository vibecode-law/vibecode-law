<?php

namespace App\Http\Controllers\Challenge\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Models\Challenge\Challenge;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ChallengeIndexController extends BaseController
{
    public function __invoke(): Response
    {
        $challenges = $this->getActiveChallenges();

        return Inertia::render('challenge/public/index', [
            'featuredChallenges' => $this->buildFeaturedCollection($challenges),
            'activeChallenges' => $this->buildActiveCollection($challenges),
        ]);
    }

    /**
     * @return Collection<int, Challenge>
     */
    private function getActiveChallenges(): Collection
    {
        return Challenge::query()
            ->where('is_active', true)
            ->publiclyVisible()
            ->with('organisation')
            ->withCount('showcases')
            ->withTotalUpvotesCount()
            ->orderBy('total_upvotes_count', 'desc')
            ->get();
    }

    /**
     * @param  Collection<int, Challenge>  $challenges
     */
    private function buildFeaturedCollection(Collection $challenges): DataCollection
    {
        $featured = $challenges->where('is_featured', true)->values();

        return ChallengeResource::collect($featured, DataCollection::class)
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
            );
    }

    /**
     * @param  Collection<int, Challenge>  $challenges
     */
    private function buildActiveCollection(Collection $challenges): DataCollection
    {
        $nonFeatured = $challenges->where('is_featured', '!==', true)->values();

        return ChallengeResource::collect($nonFeatured, DataCollection::class)
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
            );
    }
}
