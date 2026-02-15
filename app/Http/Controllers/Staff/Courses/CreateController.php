<?php

namespace App\Http\Controllers\Staff\Courses;

use App\Enums\ExperienceLevel;
use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use Inertia\Inertia;
use Inertia\Response;

class CreateController extends BaseController
{
    public function __invoke(): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('staff-area/courses/create', [
            'experienceLevels' => array_map(
                fn (ExperienceLevel $level) => $level->forFrontend(),
                ExperienceLevel::cases()
            ),
        ]);
    }
}
