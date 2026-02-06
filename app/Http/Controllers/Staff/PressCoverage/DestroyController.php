<?php

namespace App\Http\Controllers\Staff\PressCoverage;

use App\Http\Controllers\BaseController;
use App\Models\PressCoverage;
use Illuminate\Http\RedirectResponse;

class DestroyController extends BaseController
{
    public function __invoke(PressCoverage $pressCoverage): RedirectResponse
    {
        $this->authorize('delete', $pressCoverage);

        // Thumbnail will be deleted via model observer
        $pressCoverage->delete();

        return redirect()->back();
    }
}
