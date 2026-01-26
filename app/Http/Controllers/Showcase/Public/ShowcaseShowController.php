<?php

namespace App\Http\Controllers\Showcase\Public;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Showcase\Showcase;
use App\Services\Showcase\ShowcaseRankingService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseShowController extends BaseController
{
    public function __invoke(Showcase $showcase): Response
    {
        $this->ensureShowcaseIsAccessible(showcase: $showcase);

        if ($showcase->isApproved() === true) {
            $showcase->increment('view_count');
        }

        $this->loadShowcaseRelations(showcase: $showcase);

        $rankingService = new ShowcaseRankingService(showcase: $showcase);

        return Inertia::render('showcase/public/show', [
            'showcase' => $this->buildShowcaseResource(showcase: $showcase),
            'lifetimeRank' => $rankingService->getLifetimeRank(),
            'monthlyRank' => $rankingService->getMonthlyRank(),
            'canEdit' => Auth::user()?->can('update', $showcase) ?? false,
            'canCreateDraft' => Auth::user()?->can('createDraft', $showcase) ?? false,
        ]);
    }

    private function ensureShowcaseIsAccessible(Showcase $showcase): void
    {
        if (
            $showcase->isApproved() === false &&
            (Auth::check() === false || (Auth::id() !== $showcase->user_id && Auth::user()->can('toggleApproval', $showcase) === false))
        ) {
            abort(404);
        }
    }

    private function loadShowcaseRelations(Showcase $showcase): void
    {
        $userId = Auth::id();
        $isOwner = $userId !== null && $userId === $showcase->user_id;

        $relations = array_filter([
            'user',
            'images',
            'practiceAreas',
            'upvoters' => $userId === null ? null : fn ($query) => $query->where('user_id', $userId),
            'draft' => $isOwner && $showcase->isApproved() ? fn () => null : null,
        ]);

        // Load draft for owner of approved showcases
        if ($isOwner === true && $showcase->isApproved() === true) {
            $showcase->load('draft');
        }

        $showcase->load($relations);
        $showcase->loadCount('upvoters');
    }

    private function buildShowcaseResource(Showcase $showcase): ShowcaseResource
    {
        $userId = Auth::id();
        $isOwner = $userId !== null && $userId === $showcase->user_id;

        $resource = ShowcaseResource::from($showcase)
            ->include('description_html', 'key_features_html', 'help_needed_html', 'user.bio', 'user.bio_html', 'linkedin_share_url', 'youtube_id')
            ->exclude('key_features', 'help_needed', 'user.bio');

        // Include draft fields for owner
        if ($isOwner === true && $showcase->isApproved() === true) {
            $resource = $resource->include('has_draft', 'draft_id', 'draft_status');
        }

        return $resource;
    }
}
