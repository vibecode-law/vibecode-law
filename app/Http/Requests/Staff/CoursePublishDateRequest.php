<?php

namespace App\Http\Requests\Staff;

use App\Models\Course\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class CoursePublishDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course $course */
        $course = $this->route('course');

        return Gate::allows('update', $course);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'publish_date' => ['nullable', 'date'],
            'allow_preview' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Course $course */
                $course = $this->route('course');

                $settingPublishDate = $this->input('publish_date') !== null;
                $enablingPreview = $this->boolean('allow_preview') === true;

                if ($settingPublishDate === false && $enablingPreview === false) {
                    return;
                }

                $requiredFields = ['title', 'slug', 'tagline', 'description', 'learning_objectives', 'experience_level'];

                foreach ($requiredFields as $field) {
                    if (empty($course->$field)) {
                        $errorKey = $settingPublishDate ? 'publish_date' : 'allow_preview';

                        $validator->errors()->add(
                            key: $errorKey,
                            message: 'The course '.str_replace('_', ' ', $field).' must be set before publishing.'
                        );
                    }
                }

                if ($settingPublishDate === true) {
                    $hasMatchingLesson = $course->lessons()
                        ->whereDate('publish_date', $this->input('publish_date'))
                        ->exists();

                    if ($hasMatchingLesson === false) {
                        $validator->errors()->add(
                            key: 'publish_date',
                            message: 'At least one lesson must have a publish date matching the course publish date.'
                        );
                    }
                }
            },
        ];
    }
}
