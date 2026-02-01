<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('bio')) {
            $bio = strip_tags($this->input('bio'));

            $this->merge([
                'bio' => $bio === '' ? null : $bio,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_title' => ['nullable', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255', 'regex:/^https:\/\/[a-z]+\.linkedin\.com\/in\//i'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'marketing_opt_out' => ['nullable', 'boolean'],
            'intended' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
