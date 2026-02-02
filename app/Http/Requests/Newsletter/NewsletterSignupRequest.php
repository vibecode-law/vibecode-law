<?php

namespace App\Http\Requests\Newsletter;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class NewsletterSignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (Auth::check() === true) {
                $validator->errors()->add(
                    key: 'email',
                    message: 'You are already logged in. Please manage your newsletter preferences in your profile settings.',
                );

                return;
            }

            $email = $this->input('email');

            if ($email !== null && User::query()->where('email', $email)->exists() === true) {
                $validator->errors()->add(
                    key: 'email',
                    message: 'This email is already linked to an account. Please login to manage your newsletter preferences in your profile settings.',
                );
            }
        });
    }
}
