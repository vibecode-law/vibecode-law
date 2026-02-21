<?php

namespace App\Http\Requests\Staff;

use App\Models\Course\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class LessonAllowPreviewRequest extends FormRequest
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
            'allow_preview' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->boolean('allow_preview') === false) {
                    return;
                }

                /** @var Lesson $lesson */
                $lesson = $this->route('lesson');

                $requiredFields = ['title', 'slug', 'tagline'];

                foreach ($requiredFields as $field) {
                    if (empty($lesson->$field)) {
                        $validator->errors()->add(
                            key: 'allow_preview',
                            message: 'The lesson '.str_replace('_', ' ', $field).' must be set before enabling preview.'
                        );
                    }
                }
            },
        ];
    }
}
