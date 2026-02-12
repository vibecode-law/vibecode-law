<?php

namespace App\Http\Controllers\Staff\Challenges;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Models\Challenge\Challenge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\PaginatedDataCollection;

class IndexController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $this->authorize('viewAny', Challenge::class);

        return Inertia::render('staff-area/challenges/index', [
            'challenges' => $this->getChallenges(),
        ]);
    }

    /**
     * @return PaginatedDataCollection<int, ChallengeResource>
     */
    private function getChallenges(): PaginatedDataCollection
    {
        $challenges = Challenge::query()
            ->with('organisation')
            ->withCount('showcases')
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: 25)
            ->withQueryString();

        return ChallengeResource::collect($challenges, PaginatedDataCollection::class)
            ->include('organisation', 'showcases_count')
            ->only(
                'id',
                'slug',
                'title',
                'tagline',
                'starts_at',
                'ends_at',
                'is_active',
                'is_featured',
                'organisation',
                'showcases_count',
            );
    }
}
