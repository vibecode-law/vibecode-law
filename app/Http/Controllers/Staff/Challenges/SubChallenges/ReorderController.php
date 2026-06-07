<?php

namespace App\Http\Controllers\Staff\Challenges\SubChallenges;

use App\Http\Controllers\BaseController;
use App\Models\Challenge\Challenge;
use App\Models\Challenge\SubChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReorderController extends BaseController
{
    public function __invoke(Request $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('manageSubChallenges', $challenge);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:sub_challenges,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            SubChallenge::where('id', $item['id'])
                ->where('challenge_id', $challenge->id)
                ->update([
                    'order' => $item['order'],
                ]);
        }

        return Redirect::back();
    }
}
