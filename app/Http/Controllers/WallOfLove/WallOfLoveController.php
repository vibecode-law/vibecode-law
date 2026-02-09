<?php

namespace App\Http\Controllers\WallOfLove;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PressCoverageResource;
use App\Http\Resources\TestimonialResource;
use App\Models\PressCoverage;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;

class WallOfLoveController extends BaseController
{
    public function __invoke(): Response
    {
        $testimonials = Testimonial::query()
            ->published()
            ->with('user')
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        $pressCoverage = PressCoverage::query()
            ->published()
            ->orderBy('display_order')
            ->orderByDesc('publication_date')
            ->get();

        return Inertia::render('wall-of-love', [
            'testimonials' => TestimonialResource::collect($testimonials),
            'pressCoverage' => PressCoverageResource::collect($pressCoverage),
        ]);
    }
}
