<?php

namespace App\Http\Controllers\Staff\Settings;

use App\Http\Controllers\BaseController;
use App\Models\SiteSetting;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends BaseController
{
    public function __invoke(): Response
    {
        $this->authorize('viewAny', SiteSetting::class);

        return Inertia::render('staff-area/settings/index', [
            'announcementMarkdown' => SiteSetting::getValue(key: SiteSetting::ANNOUNCEMENT),
        ]);
    }
}
