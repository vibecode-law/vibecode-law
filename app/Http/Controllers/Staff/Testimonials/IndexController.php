<?php

namespace App\Http\Controllers\Staff\Testimonials;

use App\Http\Controllers\BaseController;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class IndexController extends BaseController
{
    public function __invoke(): Response
    {
        $testimonials = Testimonial::query()
            ->with('user')
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('staff-area/testimonials/index', [
            'testimonials' => TestimonialResource::collect($testimonials, DataCollection::class)->include('name', 'job_title', 'organisation', 'avatar_crop'),
        ]);
    }
}
