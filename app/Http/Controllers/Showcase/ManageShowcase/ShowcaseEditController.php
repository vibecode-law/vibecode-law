<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Queries\Challenge\SubmittableChallengesQuery;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ShowcaseEditController extends BaseController
{
    public function __invoke(Showcase $showcase, SubmittableChallengesQuery $submittableChallenges): Response
    {
        $this->authorize('update', $showcase);

        $showcase->load(['images', 'practiceAreas', 'challenges.subChallenges']);

        $currentChallenge = $showcase->challenges->first();

        // Resolve the owner without caching the relation onto the showcase, so it
        // is not surfaced in the ShowcaseResource.
        $owner = User::findOrFail($showcase->user_id);

        $availableChallenges = $submittableChallenges($owner);

        // Keep the currently associated challenge selectable even if it has now closed.
        if ($currentChallenge !== null && $availableChallenges->contains('id', $currentChallenge->id) === false) {
            $availableChallenges->push($currentChallenge);
        }

        return Inertia::render('showcase/user/create', [
            'showcase' => ShowcaseResource::from($showcase)->include('thumbnail_crop'),
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
            'availableChallenges' => ChallengeResource::collect($availableChallenges, DataCollection::class)
                ->include('sub_challenges')
                ->only('id', 'title', 'slug', 'sub_challenges'),
            'selectedChallengeId' => $currentChallenge?->id,
            'selectedSubChallengeId' => $currentChallenge?->pivot->sub_challenge_id,
        ]);
    }
}
