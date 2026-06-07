<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class SubChallengeUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:60'],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a name.',
            'name.max' => 'The name must not exceed 60 characters.',
            'tagline.required' => 'Please provide a tagline.',
            'tagline.max' => 'The tagline must be less than 255 characters.',
        ];
    }
}
