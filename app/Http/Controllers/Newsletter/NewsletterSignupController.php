<?php

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Newsletter\NewsletterSignupRequest;
use App\Jobs\MarketingEmail\CreateGuestSubscriberJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class NewsletterSignupController extends BaseController
{
    public function __invoke(NewsletterSignupRequest $request): RedirectResponse
    {
        CreateGuestSubscriberJob::dispatch(
            email: $request->validated('email'),
        );

        return Redirect::back()->with('flash', [
            'newsletter_success' => 'Thanks for signing up! Please check your email to confirm your subscription.',
        ]);
    }
}
