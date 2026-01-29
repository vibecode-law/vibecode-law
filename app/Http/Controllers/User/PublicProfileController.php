<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Http\Resources\User\UserResource;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class PublicProfileController extends BaseController
{
    public function __invoke(User $user): Response
    {
        if ($user->hasPublicProfile() === false) {
            abort(404);
        }

        return Inertia::render('user/show', [
            'user' => UserResource::from($user)
                ->include('bio', 'bio_html'),
            'showcases' => ShowcaseResource::collect($this->getShowcases($user), DataCollection::class)
                ->only('id', 'slug', 'title', 'tagline', 'thumbnail_url', 'thumbnail_rect_string', 'upvotes_count', 'has_upvoted'),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Showcase>
     */
    private function getShowcases(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Showcase::query()
            ->where('user_id', $user->id)
            ->publiclyVisible()
            ->with($this->getUpvoterRelation())
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count')
            ->orderByDesc('submitted_date')
            ->get();
    }

    /**
     * @return array<string, \Closure|null>
     */
    private function getUpvoterRelation(): array
    {
        $userId = Auth::id();

        return array_filter([
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
        ]);
    }
}
