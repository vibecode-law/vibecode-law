<?php

namespace App\Http\Controllers\Staff\UserManagement;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\UserUpdateRequest;
use App\Models\User;
use App\Services\User\ProfileService;
use App\Services\User\UserAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;

class UpdateController extends BaseController
{
    public function __construct(
        private ProfileService $profileService,
    ) {}

    public function __invoke(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $profileData = $request->safe()->except([
            'roles',
            'avatar',
            'remove_avatar',
            'team_type',
            'team_role',
            'team_order',
            'blocked_from_submissions_at',
            'marketing_opt_out',
        ]);

        if ($request->has('marketing_opt_out') === true) {
            $profileData['marketing_opt_out_at'] = $request->boolean('marketing_opt_out') === true
                ? Carbon::now()
                : null;
        }

        $this->profileService->update(user: $user, data: $profileData);

        // Admin-only fields handled separately from profile
        $this->updateAdminFields(request: $request, user: $user);
        $this->handleAvatar(request: $request, user: $user);
        $this->syncRoles(request: $request, user: $user);

        return Redirect::route('staff.users.edit', $user)
            ->with('flash', [
                'message' => ['message' => 'User updated successfully.', 'type' => 'success'],
            ]);
    }

    private function updateAdminFields(UserUpdateRequest $request, User $user): void
    {
        $adminFields = ['team_type', 'team_role', 'team_order', 'blocked_from_submissions_at'];
        $hasChanges = false;

        foreach ($adminFields as $field) {
            if ($request->has($field) === true) {
                $user->{$field} = $request->validated($field);
                $hasChanges = true;
            }
        }

        if ($hasChanges === true) {
            $user->save();
        }
    }

    private function handleAvatar(UserUpdateRequest $request, User $user): void
    {
        $avatarService = new UserAvatarService(user: $user);

        if ($request->boolean('remove_avatar') === true) {
            $avatarService->delete();

            return;
        }

        if ($request->hasFile('avatar') === true) {
            $avatarService->fromUploadedFile(file: $request->file('avatar'));
        }
    }

    private function syncRoles(UserUpdateRequest $request, User $user): void
    {
        if ($request->has('roles') === false) {
            return;
        }

        $user->syncRoles($request->validated('roles') ?? []);
    }
}
