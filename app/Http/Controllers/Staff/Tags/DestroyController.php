<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\BaseController;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DestroyController extends BaseController
{
    public function __invoke(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return Redirect::back();
    }
}
