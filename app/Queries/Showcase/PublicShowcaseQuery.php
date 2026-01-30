<?php

namespace App\Queries\Showcase;

use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PublicShowcaseQuery
{
    /**
     * @return Builder<Showcase>
     */
    public function __invoke(): Builder
    {
        $userId = Auth::id();

        $relations = array_filter([
            'user',
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
        ]);

        return Showcase::query()
            ->publiclyVisible()
            ->with($relations)
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count');
    }
}
