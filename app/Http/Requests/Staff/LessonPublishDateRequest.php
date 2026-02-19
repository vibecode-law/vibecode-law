<?php

namespace App\Http\Requests\Staff;

use App\Models\Course\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class LessonPublishDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Lesson $lesson */
        $lesson = $this->route('lesson');

        return Gate::allows('update', $lesson);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'publish_date' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('publish_date') === null) {
                    return;
                }

                /** @var Lesson $lesson */
                $lesson = $this->route('lesson');

                $requiredFields = ['title', 'slug', 'tagline', 'description', 'learning_objectives'];

                foreach ($requiredFields as $field) {
                    if (empty($lesson->$field)) {
                        $validator->errors()->add(
                            key: 'publish_date',
                            message: 'The lesson '.str_replace('_', ' ', $field).' must be set before publishing.'
                        );
                    }
                }

                $videoHostFields = ['asset_id', 'playback_id', 'duration_seconds'];

                foreach ($videoHostFields as $field) {
                    if (empty($lesson->$field)) {
                        $validator->errors()->add(
                            key: 'publish_date',
                            message: 'The lesson must be synced with the video host before publishing.'
                        );

                        return;
                    }
                }

                $transcriptFiles = ['transcript.vtt', 'transcript.txt'];

                foreach ($transcriptFiles as $file) {
                    if (Storage::exists("lessons/{$lesson->id}/{$file}") === false) {
                        $validator->errors()->add(
                            key: 'publish_date',
                            message: 'The lesson must be synced with the video host before publishing.'
                        );

                        return;
                    }
                }

                if ($lesson->transcriptLines()->exists() === false) {
                    $validator->errors()->add(
                        key: 'publish_date',
                        message: 'The lesson must have parsed transcript lines before publishing.'
                    );
                }
            },
        ];
    }
}
