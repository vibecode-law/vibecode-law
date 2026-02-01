<?php

namespace App\Http\Controllers\User;

use App\Actions\User\DeleteUserAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\User\ProfileService;
use App\Services\User\UserAvatarService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EditProfileController extends BaseController
{
    public function __construct(
        private ProfileService $profileService,
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('user-area/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'remove_avatar', 'marketing_opt_out']);

        // Convert boolean marketing_opt_out to timestamp
        $data['marketing_opt_out_at'] = $this->resolveMarketingOptOutAt(request: $request);

        // Check if email will change to reset verification
        $emailWillChange = isset($data['email']) && $data['email'] !== $user->email;

        if ($emailWillChange === true) {
            $user->email_verified_at = null;
            $user->save();
        }

        $this->profileService->update(user: $user, data: $data);

        $this->handleAvatar(request: $request);

        return to_route('user-area.profile.edit');
    }

    private function resolveMarketingOptOutAt(ProfileUpdateRequest $request): ?\Illuminate\Support\Carbon
    {
        $user = $request->user();
        $wantsOptOut = $request->boolean('marketing_opt_out');
        $isCurrentlyOptedOut = $user->marketing_opt_out_at !== null;

        if ($wantsOptOut === true && $isCurrentlyOptedOut === false) {
            return now();
        }

        if ($wantsOptOut === false && $isCurrentlyOptedOut === true) {
            return null;
        }

        return $user->marketing_opt_out_at;
    }

    private function handleAvatar(ProfileUpdateRequest $request): void
    {
        $avatarService = new UserAvatarService(user: $request->user());

        if ($request->boolean('remove_avatar') === true) {
            $avatarService->delete();

            return;
        }

        if ($request->hasFile('avatar') === true) {
            $avatarService->fromUploadedFile(file: $request->file('avatar'));
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(ProfileDeleteRequest $request, DeleteUserAction $action): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $action->delete(user: $user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
