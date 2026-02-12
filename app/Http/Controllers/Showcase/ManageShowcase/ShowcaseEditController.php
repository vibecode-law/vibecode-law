<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseEditController extends BaseController
{
    public function __invoke(Showcase $showcase): Response
    {
        $this->authorize('update', $showcase);

        $showcase->load(['images', 'practiceAreas', 'challenges']);

        $challenge = $showcase->challenges->first();

        return Inertia::render('showcase/user/create', [
            'showcase' => ShowcaseResource::from($showcase)->include('thumbnail_crop'),
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
            'challenge' => $challenge !== null
                ? ChallengeResource::from($challenge)->only('id', 'title', 'slug')
                : null,
        ]);
    }
}
