<?php

namespace App\Actions\Showcase;

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Notifications\Showcase\ShowcaseSubmittedForApproval;
use Illuminate\Support\Facades\Notification;

class SubmitShowcaseAction
{
    public function submit(Showcase $showcase): void
    {
        $showcase->update([
            'status' => ShowcaseStatus::Pending,
            'submitted_date' => now(),
        ]);

        $this->notifyStaff(showcase: $showcase);
    }

    private function notifyStaff(Showcase $showcase): void
    {
        $admins = User::query()->where('is_superadmin', '=', true)->get();
        $moderators = User::role('Moderator')->get();

        $staff = $admins->merge($moderators)->unique('id');

        Notification::send(
            notifiables: $staff,
            notification: new ShowcaseSubmittedForApproval(showcase: $showcase),
        );
    }
}
