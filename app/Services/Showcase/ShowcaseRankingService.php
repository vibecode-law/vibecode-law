<?php

namespace App\Services\Showcase;

use App\Models\Challenge\Challenge;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseUpvote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ShowcaseRankingService
{
    private ?int $upvotesCount = null;

    public function __construct(
        private readonly Showcase $showcase,
    ) {}

    private function getUpvotesCount(): int
    {
        if ($this->upvotesCount === null) {
            $this->upvotesCount = $this->showcase->upvoters()->count();
        }

        return $this->upvotesCount;
    }

    private function upvoteCountSubquery(): QueryBuilder
    {
        return ShowcaseUpvote::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('showcase_upvotes.showcase_id', 'showcases.id')
            ->toBase();
    }

    /**
     * @return Builder<Showcase>
     */
    private function baseRankingQuery(): Builder
    {
        return Showcase::query()
            ->approved()
            ->where('id', '!=', $this->showcase->id);
    }

    /**
     * @param  Builder<Showcase>  $query
     */
    private function calculateRank(Builder $query): int
    {
        $upvotesCount = $this->getUpvotesCount();

        $higherRanked = (clone $query)
            ->where($this->upvoteCountSubquery(), '>', $upvotesCount) // @phpstan-ignore argument.type
            ->count();

        $tiedButEarlier = (clone $query)
            ->where('submitted_date', '<', $this->showcase->submitted_date)
            ->where($this->upvoteCountSubquery(), '=', $upvotesCount) // @phpstan-ignore argument.type
            ->count();

        return $higherRanked + $tiedButEarlier + 1;
    }

    public function getLifetimeRank(): ?int
    {
        if ($this->showcase->submitted_date === null) {
            return null;
        }

        return $this->calculateRank($this->baseRankingQuery());
    }

    public function getMonthlyRank(): ?int
    {
        if ($this->showcase->submitted_date === null) {
            return null;
        }

        $query = $this->baseRankingQuery()
            ->whereNotNull('submitted_date')
            ->whereYear('submitted_date', $this->showcase->submitted_date->year)
            ->whereMonth('submitted_date', $this->showcase->submitted_date->month);

        return $this->calculateRank($query);
    }

    public function getChallengeRank(Challenge $challenge): int
    {
        $showcaseIds = $challenge->showcases()->pluck('showcases.id');

        $query = $this->baseRankingQuery()
            ->whereIn('id', $showcaseIds);

        return $this->calculateRank($query);
    }
}
