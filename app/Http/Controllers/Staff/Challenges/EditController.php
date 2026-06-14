<?php

namespace App\Http\Controllers\Staff\Challenges;

use App\Enums\ChallengeVisibility;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Models\Challenge\Challenge;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends BaseController
{
    public function __invoke(Challenge $challenge): Response
    {
        $this->authorize('view', $challenge);

        $challenge->load('organisation');
        $challenge->loadCount('inviteCodes');

        return Inertia::render('staff-area/challenges/edit', [
            'challenge' => ChallengeResource::fromModel($challenge)
                ->include('organisation', 'organisation.about', 'organisation.thumbnail_crops', 'thumbnail_crops', 'visibility', 'live_view_enabled', 'live_view_access_token', 'live_view_heading', 'live_view_subheading')
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'description',
                    'involvement_instructions',
                    'participant_instructions',
                    'starts_at',
                    'ends_at',
                    'is_active',
                    'is_featured',
                    'live_view_enabled',
                    'live_view_access_token',
                    'live_view_heading',
                    'live_view_subheading',
                    'visibility',
                    'thumbnail_url',
                    'thumbnail_rect_strings',
                    'thumbnail_crops',
                    'organisation',
                ),
            'inviteCodesCount' => $challenge->invite_codes_count,
            'visibilityOptions' => collect(ChallengeVisibility::cases())->map(
                fn (ChallengeVisibility $visibility) => $visibility->forFrontend(),
            ),
        ]);
    }
}
