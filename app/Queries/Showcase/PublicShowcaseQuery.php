<?php

namespace App\Queries\Showcase;

use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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

        $query = Showcase::query()
            ->publiclyVisible()
            ->with($relations)
            ->withCount('upvoters')
            ->orderByDesc('upvoters_count');

        if (Config::get('app.launched') === false) {
            $query->where('is_featured', true);
        }

        return $query;
    }
}
