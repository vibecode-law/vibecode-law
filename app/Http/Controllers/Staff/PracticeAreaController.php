<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PracticeArea\PracticeAreaStoreRequest;
use App\Http\Requests\PracticeArea\PracticeAreaUpdateRequest;
use App\Http\Resources\PracticeAreaResource;
use App\Models\PracticeArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PracticeAreaController extends BaseController
{
    public function index(): Response
    {
        $practiceAreas = PracticeArea::query()
            ->withCount('showcases')
            ->orderBy('name')
            ->get();

        return Inertia::render('staff-area/practice-areas/index', [
            'practiceAreas' => PracticeAreaResource::collect($practiceAreas),
        ]);
    }

    public function store(PracticeAreaStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', PracticeArea::class);

        PracticeArea::create($request->validated());

        return Redirect::route('staff.metadata.practice-areas.index')
            ->with('flash', [
                'message' => ['message' => 'Practice area created successfully.', 'type' => 'success'],
            ]);
    }

    public function update(PracticeAreaUpdateRequest $request, PracticeArea $practiceArea): RedirectResponse
    {
        $this->authorize('update', $practiceArea);

        $practiceArea->update($request->validated());

        return Redirect::route('staff.metadata.practice-areas.index')
            ->with('flash', [
                'message' => ['message' => 'Practice area updated successfully.', 'type' => 'success'],
            ]);
    }
}
