<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Actions\Showcase\SubmitShowcaseAction;
use App\Enums\ShowcaseStatus;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Showcase\ShowcaseWriteRequest;
use App\Models\Showcase\Showcase;
use App\Services\Showcase\ShowcaseMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ShowcaseUpdateController extends BaseController
{
    public function __invoke(ShowcaseWriteRequest $request, Showcase $showcase, ShowcaseMediaService $mediaService): RedirectResponse
    {
        $wasApproved = $showcase->isApproved();
        $isAdmin = $request->user()->is_admin;

        $excludeFields = ['images', 'practice_area_ids', 'thumbnail', 'remove_thumbnail', 'removed_images', 'submit'];

        if ($wasApproved === true) {
            $excludeFields[] = 'slug';
        }

        $showcase->update($request->safe()->except($excludeFields));

        $showcase->practiceAreas()->sync($request->validated()['practice_area_ids']);

        // Only revert to pending if a non-admin user edits an approved showcase
        if ($wasApproved === true && $isAdmin === false) {
            $showcase->update(['status' => ShowcaseStatus::Pending]);
        }

        // Handle thumbnail upload or removal
        if ($request->hasFile('thumbnail')) {
            $mediaService->storeThumbnail(
                model: $showcase,
                file: $request->file('thumbnail'),
                crop: $request->validated('thumbnail_crop'),
            );
        } elseif ($request->boolean('remove_thumbnail') === true) {
            $mediaService->removeThumbnail(model: $showcase);
        }

        // Handle removed images
        if ($request->validated('removed_images') !== null) {
            $showcase->images()->whereIn('id', $request->validated('removed_images'))->get()->each->delete();
        }

        if ($request->hasFile('images')) {
            $mediaService->storeImages(model: $showcase, files: $request->file('images'));
        }

        // Handle submit flag - only for draft or rejected showcases
        if ($request->boolean('submit') === true && ($showcase->isDraft() === true || $showcase->isRejected() === true)) {
            app(SubmitShowcaseAction::class)->submit(showcase: $showcase);

            return Redirect::route('user-area.showcases.index')->with('flash', [
                'message' => ['message' => 'Showcase updated and submitted for approval.', 'type' => 'success'],
            ]);
        }

        $message = $wasApproved === true && $isAdmin === false
            ? 'Showcase updated. It will need re-approval before being visible.'
            : 'Showcase updated successfully.';

        return Redirect::route('showcase.manage.edit', $showcase)->with('flash', [
            'message' => ['message' => $message, 'type' => 'success'],
        ]);
    }
}
