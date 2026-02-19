<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReorderController extends BaseController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:courses,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            Course::where('id', $item['id'])->update([
                'order' => $item['order'],
            ]);
        }

        return Redirect::back();
    }
}
