<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Enums\InviteCodeScope;
use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Challenge\ChallengeResource;
use App\Http\Resources\PracticeAreaResource;
use App\Models\Challenge\Challenge;
use App\Models\PracticeArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseCreateController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        Log::channel('showcaseUX')->info('User accessed showcase page', ['name' => Auth::user()->first_name.' '.Auth::user()->last_name]);

        ['challenge' => $challenge, 'warning' => $challengeWarning] = $this->resolveChallenge(request: $request);

        return Inertia::render('showcase/user/create', [
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
            'challenge' => $challenge !== null
                ? ChallengeResource::from($challenge)->only('id', 'title', 'slug')
                : null,
            'challengeWarning' => $challengeWarning,
        ]);
    }

    /**
     * @return array{challenge: ?Challenge, warning: ?string}
     */
    private function resolveChallenge(Request $request): array
    {
        if ($request->has('challenge') === false) {
            return ['challenge' => null, 'warning' => null];
        }

        $challenge = Challenge::query()
            ->where('slug', $request->query('challenge'))
            ->where('is_active', true)
            ->first();

        if ($challenge === null) {
            return ['challenge' => null, 'warning' => null];
        }

        if ($challenge->requiresInviteToSubmit() === true) {
            if (Auth::user()->hasChallengeAccess($challenge, InviteCodeScope::ViewAndSubmit) === false) {
                return [
                    'challenge' => null,
                    'warning' => "You don't have permission to submit to the {$challenge->title} challenge. An invite code with submit access is required.",
                ];
            }
        }

        return ['challenge' => $challenge, 'warning' => null];
    }
}
