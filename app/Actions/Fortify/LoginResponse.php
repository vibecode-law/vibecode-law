<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $intended = $request->session()->get('url.intended');

        if ($intended !== null && $this->shouldForceHardNavigation($intended) === true) {
            $request->session()->forget('url.intended');

            return Inertia::location($intended);
        }

        return Redirect::intended(config('fortify.home'));
    }

    private function shouldForceHardNavigation(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host !== null && $host !== request()->getHost()) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';

        return str_starts_with($path, '/oauth/');
    }
}
