<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Services\Course\LessonCopywriterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class GenerateCopywriterController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        if ($lesson->transcript_txt === null || trim($lesson->transcript_txt) === '') {
            throw ValidationException::withMessages([
                'transcript' => 'This lesson does not have a text transcript to generate content from.',
            ]);
        }

        try {
            $result = (new LessonCopywriterService(lesson: $lesson))->generate();
        } catch (\Throwable $e) {
            Log::error('Copywriter generation failed', ['lesson' => $lesson->id, 'exception' => $e]);

            return Redirect::back()->withErrors([
                'copywriter' => 'Content generation failed. Please try again.',
            ]);
        }

        $lesson->update([
            'tagline' => $result->tagline,
            'description' => $result->description,
            'learning_objectives' => $result->learningObjectives,
            'copy' => $result->copy,
        ]);

        $lesson->tags()->sync($result->suggestedTagIds);

        return Redirect::route('staff.academy.courses.lessons.edit', [$course, $lesson])
            ->with('flash', [
                'message' => ['message' => 'Lesson content generated successfully.', 'type' => 'success'],
            ]);
    }
}
