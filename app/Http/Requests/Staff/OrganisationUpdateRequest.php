<?php

namespace App\Http\Requests\Staff;

use App\Rules\CropAspectRatio;
use App\Services\CropSanitizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganisationUpdateRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'max:255', Rule::unique('organisations', 'name')->ignore($this->route('organisation'))],
            'tagline' => ['required', 'string', 'max:255'],
            'about' => ['required', 'string'],
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
            'remove_thumbnail' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide the organisation name.',
            'name.unique' => 'An organisation with this name already exists.',
            'tagline.required' => 'Please provide a tagline.',
            'about.required' => 'Please provide an about description.',
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.mimes' => 'The thumbnail must be a PNG, JPG, GIF, or WebP file.',
            'thumbnail.max' => 'The thumbnail must be less than 2MB.',
        ];
    }
}
