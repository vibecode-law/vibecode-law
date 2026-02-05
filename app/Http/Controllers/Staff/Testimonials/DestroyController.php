<?php

namespace App\Http\Controllers\Staff\Testimonials;

use App\Http\Controllers\BaseController;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DestroyController extends BaseController
{
    public function __invoke(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);

        // Avatar will be deleted via model observer
        $testimonial->delete();

        return redirect()->back();
    }
}
