<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Actions\Showcase\ResolveSubmissionChallengeAction;
use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\PracticeAreaResource;
use App\Models\PracticeArea;
use App\Queries\Challenge\SubmittableChallengesQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ShowcaseCreateController extends BaseController
{
    public function __invoke(Request $request, SubmittableChallengesQuery $submittableChallenges, ResolveSubmissionChallengeAction $resolveChallenge): Response
    {
        $user = Auth::user();

        Log::channel('showcaseUX')->info('User accessed showcase page', ['name' => $user->first_name.' '.$user->last_name]);

        $availableChallenges = $submittableChallenges($user);

        $challengeSlug = $request->query('challenge');

        $selection = $resolveChallenge->resolve(
            challengeSlug: is_string($challengeSlug) ? $challengeSlug : null,
            user: $user,
            availableChallenges: $availableChallenges,
        );

        return Inertia::render('showcase/user/create', [
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
            'availableChallenges' => ChallengeResource::collect($availableChallenges, DataCollection::class)
                ->include('sub_challenges')
                ->only('id', 'title', 'slug', 'sub_challenges'),
            'selectedChallengeId' => $selection->challenge?->id,
            'challengeWarning' => $selection->warning,
        ]);
    }
}
