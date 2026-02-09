<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'avatar_crop' => ['nullable', 'array'],
            'avatar_crop.x' => ['required_with:avatar_crop', 'integer'],
            'avatar_crop.y' => ['required_with:avatar_crop', 'integer'],
            'avatar_crop.width' => ['required_with:avatar_crop', 'integer'],
            'avatar_crop.height' => ['required_with:avatar_crop', 'integer'],
            'is_published' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'name.required_without' => 'A name is required when the testimonial is not linked to a user.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'job_title.max' => 'The job title may not be greater than 255 characters.',
            'organisation.max' => 'The organisation may not be greater than 255 characters.',
            'content.required' => 'Please provide testimonial content.',
            'content.max' => 'The testimonial content may not be greater than 1000 characters.',
            'avatar.image' => 'The avatar must be an image file.',
            'avatar.mimes' => 'The avatar must be a file of type: png, jpg, jpeg, gif, webp.',
            'avatar.max' => 'The avatar may not be greater than 2MB.',
            'avatar_crop.array' => 'The avatar crop data must be an array.',
            'avatar_crop.x.required_with' => 'The x coordinate is required when crop data is provided.',
            'avatar_crop.x.integer' => 'The x coordinate must be an integer.',
            'avatar_crop.y.required_with' => 'The y coordinate is required when crop data is provided.',
            'avatar_crop.y.integer' => 'The y coordinate must be an integer.',
            'avatar_crop.width.required_with' => 'The width is required when crop data is provided.',
            'avatar_crop.width.integer' => 'The width must be an integer.',
            'avatar_crop.height.required_with' => 'The height is required when crop data is provided.',
            'avatar_crop.height.integer' => 'The height must be an integer.',
            'is_published.boolean' => 'The published status must be true or false.',
            'display_order.integer' => 'The display order must be an integer.',
            'display_order.min' => 'The display order must be at least 0.',
        ];
    }
}
