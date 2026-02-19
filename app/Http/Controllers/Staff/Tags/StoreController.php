<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\TagStoreRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class StoreController extends BaseController
{
    public function __invoke(TagStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Tag::class);

        Tag::create([
            ...$request->validated(),
            'slug' => Str::slug($request->validated('name')),
        ]);

        return Redirect::back();
    }
}
