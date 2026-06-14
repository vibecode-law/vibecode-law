<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class PartnerLogoStoreRequest extends FormRequest
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
            'logos' => ['required', 'array', 'min:1'],
            'logos.*' => ['image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logos.required' => 'Please choose at least one logo to upload.',
            'logos.*.image' => 'Each logo must be an image.',
            'logos.*.mimes' => 'Each logo must be a PNG, JPG, GIF, or WebP file.',
            'logos.*.max' => 'Each logo must be less than 2MB.',
        ];
    }
}
