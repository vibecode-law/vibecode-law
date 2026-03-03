<?php

namespace App\Http\Requests\Staff;

use App\Enums\ChallengeVisibility;
use App\Rules\CropAspectRatio;
use App\Services\CropSanitizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeStoreRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:80'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('challenges', 'slug'),
            ],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'visibility' => ['nullable', Rule::enum(ChallengeVisibility::class)],
            'organisation_id' => ['nullable', Rule::exists('organisations', 'id')],
            'thumbnail' => [Rule::prohibitedIf($this->filled('organisation_id')), 'nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'thumbnail_crops' => [
                Rule::prohibitedIf($this->filled('organisation_id')),
                'nullable',
                'array',
                new CropAspectRatio(expectedRatios: ['square' => 1.0, 'landscape' => 16 / 9]),
            ],
            'thumbnail_crops.*' => ['array'],
            'thumbnail_crops.*.x' => ['required', 'integer', 'min:0'],
            'thumbnail_crops.*.y' => ['required', 'integer', 'min:0'],
            'thumbnail_crops.*.width' => ['required', 'integer', 'min:1'],
            'thumbnail_crops.*.height' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title.',
            'title.max' => 'The title must be less than 80 characters.',
            'slug.required' => 'Please provide a slug.',
            'slug.regex' => 'Slug must be lowercase letters, numbers, and hyphens only.',
            'slug.unique' => 'This slug is already in use.',
            'tagline.required' => 'Please provide a tagline.',
            'description.required' => 'Please provide a description.',
            'ends_at.after' => 'The end date must be after the start date.',
            'organisation_id.exists' => 'The selected organisation does not exist.',
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.mimes' => 'The thumbnail must be a PNG, JPG, GIF, or WebP file.',
            'thumbnail.max' => 'The thumbnail must be less than 2MB.',
            'thumbnail.prohibited' => 'A thumbnail cannot be uploaded when an organisation is selected.',
        ];
    }
}
