<?php

namespace App\Http\Requests\Staff;

use App\Models\Challenge\Challenge;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeUpdateRequest extends FormRequest
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
        /** @var Challenge $challenge */
        $challenge = $this->route('challenge');

        $slugRules = $challenge->is_active === true
            ? ['exclude']
            : [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('challenges', 'slug')->ignore($challenge),
            ];

        $isActiveRules = $challenge->is_active === true
            ? ['required', 'accepted']
            : ['nullable', 'boolean'];

        return [
            'title' => ['required', 'string', 'max:80'],
            'slug' => $slugRules,
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => $isActiveRules,
            'is_featured' => ['nullable', 'boolean'],
            'organisation_id' => ['nullable', Rule::exists('organisations', 'id')],
            'thumbnail' => [Rule::prohibitedIf($this->filled('organisation_id')), 'nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'thumbnail_crops' => [
                Rule::prohibitedIf($this->filled('organisation_id')),
                'nullable',
                'array',
                $this->validateCropKeysAndAspectRatios(),
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
            'title.max' => 'The title must be less than 80 characters.',
            'slug.required' => 'Please provide a slug.',
            'slug.regex' => 'Slug must be lowercase letters, numbers, and hyphens only.',
            'slug.unique' => 'This slug is already in use.',
            'slug.in' => 'The slug cannot be changed once the challenge is active.',
            'is_active.accepted' => 'An active challenge cannot be deactivated.',
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
