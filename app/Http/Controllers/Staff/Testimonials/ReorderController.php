<?php

namespace App\Http\Controllers\Staff\Testimonials;

use App\Http\Controllers\BaseController;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReorderController extends BaseController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('create', Testimonial::class);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:testimonials,id',
            'items.*.display_order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            Testimonial::where('id', $item['id'])->update([
                'display_order' => $item['display_order'],
            ]);
        }

        return redirect()->back();
    }
}
