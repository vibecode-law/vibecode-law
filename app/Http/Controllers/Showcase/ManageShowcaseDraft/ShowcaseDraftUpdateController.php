<?php

namespace App\Http\Controllers\Showcase\ManageShowcaseDraft;

use App\Actions\ShowcaseDraft\SubmitShowcaseDraftAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Showcase\ShowcaseDraftWriteRequest;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use App\Models\User;
use App\Notifications\ShowcaseDraft\ShowcaseDraftSubmittedForApproval;
use App\Services\Showcase\ShowcaseMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class ShowcaseDraftUpdateController extends BaseController
{
    public function __invoke(ShowcaseDraftWriteRequest $request, ShowcaseDraft $draft, ShowcaseMediaService $mediaService): RedirectResponse
    {
        $excludeFields = ['images', 'practice_area_ids', 'thumbnail', 'remove_thumbnail', 'removed_images', 'deleted_new_images', 'submit'];

        $draft->update($request->safe()->except($excludeFields));

        $draft->practiceAreas()->sync($request->validated()['practice_area_ids']);

        // Handle thumbnail upload or removal
        if ($request->hasFile('thumbnail')) {
            $mediaService->storeThumbnail(
                model: $draft,
                file: $request->file('thumbnail'),
                crop: $request->validated('thumbnail_crop'),
            );
        } elseif ($request->boolean('remove_thumbnail') === true) {
            $mediaService->removeThumbnail(model: $draft);
        }

        // Handle marking kept images as removed
        if ($request->validated('removed_images') !== null) {
            $draft->images()
                ->whereIn('original_image_id', $request->validated('removed_images'))
                ->update(['action' => ShowcaseDraftImage::ACTION_REMOVE]);
        }

        // Handle deleting new draft images
        if ($request->validated('deleted_new_images') !== null) {
            $draft->images()
                ->whereIn('id', $request->validated('deleted_new_images'))
                ->get()
                ->each->delete();
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $mediaService->storeImages(model: $draft, files: $request->file('images'));
        }

        // Handle submit flag
        if ($request->boolean('submit') === true && $draft->canBeSubmitted() === true) {
            app(SubmitShowcaseDraftAction::class)->submit(draft: $draft);

            // Notify staff about the submission
            $this->notifyStaffAboutSubmission(draft: $draft);

            return Redirect::route('user-area.showcases.index')->with('flash', [
                'message' => ['message' => 'Draft submitted for approval.', 'type' => 'success'],
            ]);
        }

        return Redirect::route('showcase.draft.edit', $draft)->with('flash', [
            'message' => ['message' => 'Draft updated successfully.', 'type' => 'success'],
        ]);
    }

    private function notifyStaffAboutSubmission(ShowcaseDraft $draft): void
    {
        $staffToNotify = User::query()
            ->where('is_admin', true)
            ->orWhereHas('permissions', fn ($query) => $query->where('name', 'showcase.approve-reject'))
            ->get();

        Notification::send($staffToNotify, new ShowcaseDraftSubmittedForApproval($draft));
    }
}
