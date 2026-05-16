<?php

namespace App\Http\Controllers\Showcase\ManageShowcaseDraft;

use App\Actions\ShowcaseDraft\SubmitShowcaseDraftAction;
use App\Http\Controllers\BaseController;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use App\Notifications\ShowcaseDraft\ShowcaseDraftSubmittedForApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class ShowcaseDraftSubmitController extends BaseController
{
    public function __invoke(ShowcaseDraft $draft, SubmitShowcaseDraftAction $action): RedirectResponse
    {
        $this->authorize('submit', $draft);

        $action->submit(draft: $draft);

        // Notify staff about the submission
        $this->notifyStaffAboutSubmission(draft: $draft);

        return Redirect::route('user-area.showcases.index')->with('flash', [
            'message' => ['message' => 'Draft submitted for approval.', 'type' => 'success'],
        ]);
    }

    private function notifyStaffAboutSubmission(ShowcaseDraft $draft): void
    {
        $staffToNotify = User::query()
            ->where('is_superadmin', true)
            ->orWhereHas('permissions', fn ($query) => $query->where('name', 'showcase.approve-reject'))
            ->get();

        Notification::send($staffToNotify, new ShowcaseDraftSubmittedForApproval($draft));
    }
}
