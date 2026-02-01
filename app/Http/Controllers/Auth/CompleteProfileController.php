<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\CompleteProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class CompleteProfileController extends BaseController
{
    public function show(Request $request): Response
    {
        return Inertia::render('auth/complete-profile', [
            'intended' => $request->query('intended'),
        ]);
    }

    public function store(CompleteProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->except(['marketing_opt_out', 'intended']));

        if ($request->boolean('marketing_opt_out') === true) {
            $user->marketing_opt_out_at = now();
        }

        $user->save();

        return Redirect::to(
            path: $request->input('intended') ?? route('home')
        );
    }

    public function skip(Request $request): RedirectResponse
    {
        return Redirect::to(
            path: $request->input('intended') ?? route('home')
        );
    }
}
