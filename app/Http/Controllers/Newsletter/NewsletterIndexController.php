<?php

namespace App\Http\Controllers\Newsletter;

use App\Http\Controllers\BaseController;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterIndexController extends BaseController
{
    public function __invoke(): Response
    {
        return Inertia::render(component: 'newsletter/index');
    }
}
