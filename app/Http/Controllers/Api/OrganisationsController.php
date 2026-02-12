<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Organisation\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationsController extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = Organisation::query()
            ->orderBy('name');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']);
        }

        $organisations = $query->limit(50)->get()->map(fn (Organisation $org) => [
            'id' => $org->id,
            'name' => $org->name,
            'tagline' => $org->tagline,
            'about' => $org->about,
        ]);

        return response()->json($organisations);
    }
}
