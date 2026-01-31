<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\PracticeAreaResource;
use App\Models\PracticeArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseCreateController extends BaseController
{
    public function __invoke(): Response
    {
        Log::channel('showcaseUX')->info('User accessed showcase page', ['name' => Auth::user()->first_name.' '.Auth::user()->last_name]);

        return Inertia::render('showcase/user/create', [
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
        ]);
    }
}
