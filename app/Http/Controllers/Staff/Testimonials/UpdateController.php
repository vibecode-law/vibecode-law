<?php

namespace App\Http\Controllers\Staff\Testimonials;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\TestimonialUpdateRequest;
use App\Models\Testimonial;
use App\Services\Testimonial\TestimonialAvatarService;
use Illuminate\Http\RedirectResponse;

class UpdateController extends BaseController
{
    public function __invoke(TestimonialUpdateRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $testimonial->update($request->safe()->except(['avatar', 'avatar_crop', 'remove_avatar']));

        $avatarService = new TestimonialAvatarService(testimonial: $testimonial);

        // Handle avatar removal
        if ($request->boolean('remove_avatar')) {
            $avatarService->delete();
        }
        // Handle avatar upload
        elseif ($request->hasFile('avatar')) {
            $avatarService->fromUploadedFile(
                file: $request->file('avatar'),
                crop: $request->validated('avatar_crop'),
            );
        }
        // Handle crop-only update (re-crop existing avatar)
        elseif ($request->has('avatar_crop') && $testimonial->avatar_path !== null) {
            $testimonial->update(['avatar_crop' => $request->validated('avatar_crop')]);
        }

        return redirect()->back();
    }
}
