<?php

namespace App\Http\Controllers\Staff\Testimonials;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\TestimonialUpdateRequest;
use App\Models\Testimonial;
use App\Services\Testimonial\TestimonialAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __invoke(TestimonialUpdateRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $testimonial->update($request->safe()->except(['avatar', 'remove_avatar']));

        $avatarService = new TestimonialAvatarService(testimonial: $testimonial);

        // Handle avatar removal
        if ($request->boolean('remove_avatar')) {
            $avatarService->delete();
        }
        // Handle avatar upload
        elseif ($request->hasFile('avatar')) {
            $avatarService->fromUploadedFile(file: $request->file('avatar'));
        }

        return redirect()->back();
    }
}
