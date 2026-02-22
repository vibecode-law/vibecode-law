<?php

namespace App\Http\Controllers\Challenge\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Http\Resources\User\UserResource;
use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class ChallengeShowController extends BaseController
{
    public function __invoke(Challenge $challenge): Response
    {
        if ($challenge->is_active === false) {
            abort(404);
        }

        $challenge = Challenge::query()
            ->where('id', $challenge->id)
            ->with('organisation')
            ->firstOrFail();

        $userId = Auth::id();

        $relations = array_filter([
            'user',
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
        ]);

        $showcaseIds = $challenge->showcases()->pluck('showcases.id');

        $showcases = Showcase::query()
            ->whereIn('id', $showcaseIds)
            ->publiclyVisible()
            ->with($relations)
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count')
            ->get();

        $participants = $showcases
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();

        return Inertia::render('challenge/public/show', [
            'challenge' => ChallengeResource::from($challenge)
                ->include('description_html', 'organisation')
                ->exclude('description'),
            'showcases' => ShowcaseResource::collect($showcases, DataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted', 'view_count', 'user'),
            'participants' => UserResource::collect($participants, DataCollection::class)
                ->only('first_name', 'avatar', 'handle'),
        ]);
    }
}
