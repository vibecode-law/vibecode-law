<?php

namespace App\Http\Controllers\Staff\Settings;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\UpdateAnnouncementRequest;
use App\Models\SiteSetting;
use App\Services\Markdown\MarkdownService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UpdateAnnouncementController extends BaseController
{
    public function __invoke(UpdateAnnouncementRequest $request, MarkdownService $markdownService): RedirectResponse
    {
        $this->authorize('update', SiteSetting::class);

        SiteSetting::setValue(
            key: SiteSetting::ANNOUNCEMENT,
            value: $request->validated('announcement'),
        );

        $markdownService->clearCacheByKey(cacheKey: 'site-announcement');

        return Redirect::back();
    }
}
