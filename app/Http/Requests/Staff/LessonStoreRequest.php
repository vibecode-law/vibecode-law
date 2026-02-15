<?php

namespace App\Http\Requests\Staff;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                Rule::unique('lessons', 'slug')->where('course_id', $this->route('course')->id),
            ],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'learning_objectives' => ['required', 'string'],
            'copy' => ['nullable', 'string'],
            'gated' => ['required', 'boolean'],
            'visible' => ['required', 'boolean'],
            'publish_date' => ['nullable', 'date'],
            'asset_id' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'thumbnail_crops' => [
                'nullable',
                'array',
                $this->validateCropKeysAndAspectRatios(),
            ],
            'thumbnail_crops.*' => ['array'],
            'thumbnail_crops.*.x' => ['required', 'integer', 'min:0'],
            'thumbnail_crops.*.y' => ['required', 'integer', 'min:0'],
            'thumbnail_crops.*.width' => ['required', 'integer', 'min:1'],
            'thumbnail_crops.*.height' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return Closure(string, mixed, Closure): void
     */
    private function validateCropKeysAndAspectRatios(): Closure
    {
        $expectedRatios = [
            'square' => 1.0,
            'landscape' => 16 / 9,
        ];

        return function (string $attribute, mixed $value, Closure $fail) use ($expectedRatios): void {
            if (! is_array($value)) {
                return;
            }

            $invalidKeys = array_diff(array_keys($value), array_keys($expectedRatios));

            if (count($invalidKeys) > 0) {
                $fail('Only square and landscape crops are accepted.');

                return;
            }

            foreach ($value as $key => $crop) {
                if (! is_array($crop) || ! isset($crop['width'], $crop['height']) || (int) $crop['height'] === 0) {
                    continue;
                }

                $ratio = (int) $crop['width'] / (int) $crop['height'];
                $expectedRatio = $expectedRatios[$key];

                if (abs($ratio - $expectedRatio) > 0.02) {
                    $fail("The {$key} crop does not have the correct aspect ratio.");
                }
            }
        };
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
