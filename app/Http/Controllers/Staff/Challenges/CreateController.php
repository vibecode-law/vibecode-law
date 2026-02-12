<?php

namespace App\Http\Controllers\Staff\Challenges;

use App\Http\Controllers\BaseController;
use App\Models\Challenge\Challenge;
use Inertia\Inertia;
use Inertia\Response;

class CreateController extends BaseController
{
    public function __invoke(): Response
    {
        $this->authorize('create', Challenge::class);

        return Inertia::render('staff-area/challenges/create');
    }
}
