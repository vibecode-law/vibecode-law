<?php

namespace App\Http\Controllers\Staff\Challenges\InviteCodes;

use App\Enums\InviteCodeScope;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeInviteCodeResource;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Models\Challenge\Challenge;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(Challenge $challenge): Response
    {
        $this->authorize('manageInviteCodes', $challenge);

        $inviteCodes = $challenge->inviteCodes()
            ->withCount('users')
            ->latest()
            ->get();

        return Inertia::render('staff-area/challenges/invite-codes/index', [
            'challenge' => ChallengeResource::from($challenge)
                ->include('visibility')
                ->only('id', 'slug', 'title', 'visibility'),
            'inviteCodes' => ChallengeInviteCodeResource::collect($inviteCodes, DataCollection::class)
                ->include('users_count'),
            'scopeOptions' => collect(InviteCodeScope::cases())->map->forFrontend()->values()->all(),
        ]);
    }
}
