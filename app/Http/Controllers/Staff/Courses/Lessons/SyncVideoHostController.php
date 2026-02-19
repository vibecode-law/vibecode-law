<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Actions\Course\SyncLessonWithVideoHostAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Staff\LessonSyncVideoHostRequest;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Services\VideoHost\Exceptions\VideoHostException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class SyncVideoHostController extends BaseController
{
    public function __invoke(LessonSyncVideoHostRequest $request, Course $course, Lesson $lesson, SyncLessonWithVideoHostAction $action): RedirectResponse
    {
        $this->authorize('update', $lesson);

        try {
            $action->handle(
                lesson: $lesson,
                assetId: $request->validated('asset_id'),
            );
        } catch (VideoHostException|\RuntimeException $e) {
            return Redirect::back()->withErrors([
                'asset_id' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Video host sync failed', ['exception' => $e]);

            return Redirect::back()->withErrors([
                'asset_id' => 'An unexpected error occurred while syncing. Please try again.',
            ]);
        }

        return Redirect::route('staff.academy.courses.lessons.edit', [$course, $lesson])
            ->with('flash', [
                'message' => ['message' => 'Synced with video host successfully.', 'type' => 'success'],
            ]);
    }
}
