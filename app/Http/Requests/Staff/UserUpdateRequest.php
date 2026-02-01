<?php

namespace App\Http\Requests\Staff;

use App\Enums\TeamType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'handle' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('users', 'handle')->ignore($this->route('user')),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'organisation' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:10000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'team_type' => ['nullable', 'integer', Rule::enum(TeamType::class)],
            'team_role' => ['nullable', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'marketing_opt_out' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please provide a first name.',
            'last_name.required' => 'Please provide a last name.',
            'handle.required' => 'Please provide a handle.',
            'handle.regex' => 'Handle must be lowercase letters, numbers, and hyphens only.',
            'handle.unique' => 'This handle is already in use.',
            'email.required' => 'Please provide an email address.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'linkedin_url.url' => 'Please provide a valid LinkedIn URL.',
            'roles.*.exists' => 'One or more selected roles are invalid.',
            'avatar.image' => 'The avatar must be an image.',
            'avatar.mimes' => 'The avatar must be a PNG, JPG, GIF, or WebP file.',
            'avatar.max' => 'The avatar must be less than 2MB.',
        ];
    }
}
