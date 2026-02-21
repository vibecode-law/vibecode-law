<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\TagUpdateRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class UpdateController extends BaseController
{
    public function __invoke(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);

        $hasContent = $tag->courses()->exists() || $tag->lessons()->exists();

        $tag->update([
            ...$request->validated(),
            ...($hasContent === false ? ['slug' => Str::slug($request->validated('name'))] : []),
        ]);

        return Redirect::back();
    }
}
