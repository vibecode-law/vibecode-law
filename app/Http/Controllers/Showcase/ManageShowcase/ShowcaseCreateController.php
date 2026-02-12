<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

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

        $challenge = null;

        if ($request->has('challenge')) {
            $challenge = Challenge::query()
                ->where('slug', $request->query('challenge'))
                ->where('is_active', true)
                ->first();
        }

        return Inertia::render('showcase/user/create', [
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
            'challenge' => $challenge !== null
                ? ChallengeResource::from($challenge)->only('id', 'title', 'slug')
                : null,
        ]);
    }
}
