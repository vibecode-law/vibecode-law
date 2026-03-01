<?php

namespace App\Http\Requests\Staff;

use App\Enums\InviteCodeScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeInviteCodeStoreRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'scope' => ['required', Rule::enum(InviteCodeScope::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required' => 'Please enter a label.',
            'scope.required' => 'Please select a scope.',
            'scope.in' => 'The selected scope is invalid.',
        ];
    }
}
