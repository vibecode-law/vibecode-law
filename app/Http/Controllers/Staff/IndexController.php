<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\BaseController;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends BaseController
{
    public function __invoke(): Response
    {
        return Inertia::render('staff-area/index');
    }
}
