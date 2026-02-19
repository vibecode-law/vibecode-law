<?php

namespace App\Http\Controllers\Staff\Courses\Lessons;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Course\LessonResource;
use App\Http\Resources\TagResource;
use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class EditController extends BaseController
{
    public function __invoke(Course $course, Lesson $lesson): Response
    {
        $this->authorize('view', $lesson);

        $lesson->load('tags', 'instructors');

        return Inertia::render('staff-area/courses/lessons/edit', [
            'course' => CourseResource::fromModel($course)
                ->only('id', 'slug', 'title'),
            'lesson' => LessonResource::fromModel($lesson)
                ->include('description', 'learning_objectives', 'copy', 'asset_id', 'playback_id', 'duration_seconds', 'has_vtt_transcript', 'has_txt_transcript', 'has_transcript_lines', 'thumbnail_crops', 'tags', 'instructors', 'instructors.id')
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'description',
                    'learning_objectives',
                    'copy',
                    'gated',
                    'allow_preview',
                    'is_previewable',
                    'is_scheduled',
                    'publish_date',
                    'order',
                    'asset_id',
                    'playback_id',
                    'duration_seconds',
                    'has_vtt_transcript',
                    'has_txt_transcript',
                    'has_transcript_lines',
                    'thumbnail_url',
                    'thumbnail_rect_strings',
                    'thumbnail_crops',
                    'tags',
                    'instructors',
                ),
            'availableTags' => TagResource::collect(
                Tag::query()->orderBy('type')->orderBy('name')->get()
            ),
        ]);
    }
}
