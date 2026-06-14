<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class PartnerLogoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('href') === '') {
            $this->merge(['href' => null]);
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'href' => ['nullable', 'url', 'max:2048'],
            'invert_in_dark' => ['required', 'boolean'],
        ];
    }
}
