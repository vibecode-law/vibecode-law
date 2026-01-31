<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Http\Controllers\BaseController;
use App\Models\Showcase\Showcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ShowcaseDestroyController extends BaseController
{
    public function __invoke(Showcase $showcase): RedirectResponse
    {
        $this->authorize('delete', $showcase);

        $showcase->delete();

        return Redirect::back()->with('flash', [
            'message' => ['message' => 'Showcase deleted successfully.', 'type' => 'success'],
        ]);
    }
}
