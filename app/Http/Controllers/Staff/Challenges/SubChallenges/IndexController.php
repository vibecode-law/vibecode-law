<?php

namespace App\Http\Controllers\Staff\Challenges\SubChallenges;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\Challenge\SubChallengeResource;
use App\Models\Challenge\Challenge;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(Challenge $challenge): Response
    {
        $this->authorize('manageSubChallenges', $challenge);

        $subChallenges = $challenge->subChallenges()->get();

        return Inertia::render('staff-area/challenges/sub-challenges/index', [
            'challenge' => ChallengeResource::from($challenge)
                ->only('id', 'slug', 'title'),
            'subChallenges' => SubChallengeResource::collect($subChallenges, DataCollection::class),
        ]);
    }
}
