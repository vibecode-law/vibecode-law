<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReorderController extends BaseController
{
    public function __invoke(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('create', Lesson::class);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:lessons,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            Lesson::where('id', $item['id'])
                ->where('course_id', $course->id)
                ->update([
                    'order' => $item['order'],
                ]);
        }

        return Redirect::back();
    }
}
