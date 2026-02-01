<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Services\Auth\Linkedin\FindOrCreateLinkedinUserResult;
use App\Services\Auth\Linkedin\FindOrCreateLinkedinUserService;
use App\Services\Auth\Linkedin\SyncUserLinkedinService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as LinkedinUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LinkedinAuthCallbackController extends BaseController
{
    public function __invoke(): RedirectResponse
    {
        try {
            $linkedinUser = $this->getLinkedinUser();
        } catch (InvalidStateException) {
            return $this->redirectWithError(
                message: "There was an issue with Linkedin's response. Please try again."
            );
        }

        $result = $this->findOrCreateUser(linkedinUser: $linkedinUser);

        if ($result->failed() === true) {
            return $this->redirectWithError(message: $result->errorMessage);
        }

        Auth::login($result->user);

        $this->syncUserData(linkedinUser: $linkedinUser, localUser: $result->user);

        if ($result->wasRecentlyCreated === true) {
            return Redirect::route('auth.complete-profile', [
                'intended' => session()->pull('url.intended'),
            ]);
        }

        return Redirect::intended();
    }

    private function getLinkedinUser(): LinkedinUser
    {
        /** @var LinkedinUser */
        return Socialite::driver('linkedin-openid')->user();
    }

    private function findOrCreateUser(LinkedinUser $linkedinUser): FindOrCreateLinkedinUserResult
    {
        return app()->makeWith(
            abstract: FindOrCreateLinkedinUserService::class,
            parameters: ['linkedinUser' => $linkedinUser],
        )->handle();
    }

    private function redirectWithError(string $message): RedirectResponse
    {
        return Redirect::route('login')->with('flash', [
            'message' => ['message' => $message, 'type' => 'error'],
        ]);
    }

    private function syncUserData(LinkedinUser $linkedinUser, User $localUser): void
    {
        new SyncUserLinkedinService(
            linkedinUser: $linkedinUser,
            localUser: $localUser
        )->handle();
    }
}
