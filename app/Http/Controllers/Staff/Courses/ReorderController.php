<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReorderController extends BaseController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:courses,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            Course::where('id', $item['id'])->update([
                'order' => $item['order'],
            ]);
        }

        return redirect()->back();
    }
}
