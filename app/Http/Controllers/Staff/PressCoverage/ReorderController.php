<?php

namespace App\Http\Controllers\Staff\PressCoverage;

use App\Http\Controllers\BaseController;
use App\Models\PressCoverage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReorderController extends BaseController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('create', PressCoverage::class);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:press_coverage,id',
            'items.*.display_order' => 'required|integer|min:0',
        ]);

        foreach ($request->items as $item) {
            PressCoverage::where('id', $item['id'])->update([
                'display_order' => $item['display_order'],
            ]);
        }

        return redirect()->back();
    }
}
