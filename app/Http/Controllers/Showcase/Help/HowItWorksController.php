<?php

namespace App\Http\Controllers\Showcase\Help;

use App\Http\Controllers\BaseController;
use Inertia\Inertia;
use Inertia\Response;

class HowItWorksController extends BaseController
{
    public function __invoke(): Response
    {
        return Inertia::render('showcase/help/how-it-works');
    }
}
