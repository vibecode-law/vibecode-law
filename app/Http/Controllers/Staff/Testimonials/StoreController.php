<?php

namespace App\Http\Controllers\Staff\Testimonials;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\TestimonialStoreRequest;
use App\Models\Testimonial;
use App\Services\Testimonial\TestimonialAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class StoreController extends BaseController
{
    public function __invoke(TestimonialStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Testimonial::class);

        $testimonial = Testimonial::create($request->safe()->except(['avatar']));

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarService = new TestimonialAvatarService(testimonial: $testimonial);
            $avatarService->fromUploadedFile(file: $request->file('avatar'));
        }

        return redirect()->back();
    }
}
