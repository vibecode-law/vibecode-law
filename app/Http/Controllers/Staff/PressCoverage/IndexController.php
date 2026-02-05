<?php

namespace App\Http\Controllers\Staff\PressCoverage;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PressCoverageResource;
use App\Models\PressCoverage;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends BaseController
{
    public function __invoke(): Response
    {
        $pressCoverage = PressCoverage::query()
            ->orderBy('display_order')
            ->orderByDesc('publication_date')
            ->get();

        return Inertia::render('staff-area/press-coverage/index', [
            'pressCoverage' => PressCoverageResource::collect($pressCoverage),
        ]);
    }
}
