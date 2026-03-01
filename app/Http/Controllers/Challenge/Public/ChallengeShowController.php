<?php

namespace App\Http\Controllers\Challenge\Public;

use App\Enums\ChallengeVisibility;
use App\Enums\InviteCodeScope;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Http\Resources\User\UserResource;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ChallengeShowController extends BaseController
{
    public function __invoke(Challenge $challenge): Response|RedirectResponse
    {
        if ($challenge->is_active === false) {
            abort(404);
        }

        if ($challenge->isInviteOnly() === true && $this->hasInviteOnlyAccess(challenge: $challenge) === false) {
            return $this->renderInviteOnlyDenied();
        }

        $challenge = $this->loadChallenge(challenge: $challenge);
        $showcases = $this->getShowcases(challenge: $challenge);
        $participants = $this->extractParticipants(showcases: $showcases);

        return Inertia::render('challenge/public/show', [
            'challenge' => ChallengeResource::from($challenge)
                ->include('description_html', 'organisation')
                ->exclude('description'),
            'showcases' => ShowcaseResource::collect($showcases, DataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted', 'view_count', 'user'),
            'participants' => UserResource::collect($participants, DataCollection::class)
                ->only('first_name', 'avatar', 'handle'),
            'canSubmit' => $this->determineCanSubmit(challenge: $challenge),
            'requiresInviteToSubmit' => $challenge->requiresInviteToSubmit(),
        ]);
    }

    private function hasInviteOnlyAccess(Challenge $challenge): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return $user->is_admin === true
            || $user->hasChallengeAccess($challenge, InviteCodeScope::View) === true;
    }

    private function renderInviteOnlyDenied(): Response|RedirectResponse
    {
        if (Auth::guest()) {
            Session::put('url.intended', URL::current());

            return Redirect::route('login');
        }

        return Inertia::render('challenge/invite-only');
    }

    private function loadChallenge(Challenge $challenge): Challenge
    {
        $challenge->load('organisation');

        return $challenge;
    }

    /**
     * @return Collection<int, Showcase>
     */
    private function getShowcases(Challenge $challenge): Collection
    {
        $userId = Auth::id();

        $relations = array_filter([
            'user',
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
        ]);

        $showcaseIds = $challenge->showcases()->pluck('showcases.id');

        return Showcase::query()
            ->whereIn('id', $showcaseIds)
            ->publiclyVisible()
            ->with($relations)
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count')
            ->get();
    }

    /**
     * @param  Collection<int, Showcase>  $showcases
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function extractParticipants(Collection $showcases): \Illuminate\Support\Collection
    {
        return $showcases
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();
    }

    private function determineCanSubmit(Challenge $challenge): bool
    {
        if ($challenge->visibility === ChallengeVisibility::Public) {
            return true;
        }

        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        if ($user->is_admin === true) {
            return true;
        }

        return $user->hasChallengeAccess($challenge, InviteCodeScope::ViewAndSubmit);
    }
}
