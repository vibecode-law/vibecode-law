<?php

namespace App\Http\Requests\Staff;

use App\Enums\ExperienceLevel;
use App\Rules\CropAspectRatio;
use App\Services\CropSanitizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('thumbnail_crops') && is_array($this->input('thumbnail_crops'))) {
            $this->merge([
                'thumbnail_crops' => CropSanitizationService::sanitizeNamedCrops(
                    crops: $this->input('thumbnail_crops'),
                    allowedShapes: ['square', 'landscape'],
                ),
            ]);
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('courses', 'slug'),
            ],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'learning_objectives' => ['nullable', 'string'],
            'experience_level' => ['nullable', 'integer', Rule::in(array_column(ExperienceLevel::cases(), 'value'))],
            'is_featured' => ['required', 'boolean'],
            'thumbnail' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'thumbnail_crops' => [
                'nullable',
                'array',
                new CropAspectRatio(expectedRatios: ['square' => 1.0, 'landscape' => 16 / 9]),
            ],
            'thumbnail_crops.*' => ['array'],
            'thumbnail_crops.*.x' => ['required', 'integer', 'min:0'],
            'thumbnail_crops.*.y' => ['required', 'integer', 'min:0'],
            'thumbnail_crops.*.width' => ['required', 'integer', 'min:1'],
            'thumbnail_crops.*.height' => ['required', 'integer', 'min:1'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title.',
            'title.max' => 'The title must be less than 255 characters.',
            'slug.required' => 'Please provide a slug.',
            'slug.regex' => 'Slug must be lowercase letters, numbers, and hyphens only.',
            'slug.unique' => 'This slug is already in use.',
            'tagline.required' => 'Please provide a tagline.',
            'description.required' => 'Please provide a description.',
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.mimes' => 'The thumbnail must be a PNG, JPG, GIF, or WebP file.',
            'thumbnail.max' => 'The thumbnail must be less than 2MB.',
        ];
    }
}
