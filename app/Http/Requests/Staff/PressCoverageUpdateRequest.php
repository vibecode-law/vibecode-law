<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class PressCoverageUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'publication_name' => ['required', 'string', 'max:255'],
            'publication_date' => ['required', 'date'],
            'url' => ['required', 'url', 'max:500'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048', 'dimensions:min_width=100,min_height=100'],
            'thumbnail_crop' => ['nullable', 'array'],
            'thumbnail_crop.x' => ['required_with:thumbnail_crop', 'integer'],
            'thumbnail_crop.y' => ['required_with:thumbnail_crop', 'integer'],
            'thumbnail_crop.width' => ['required_with:thumbnail_crop', 'integer'],
            'thumbnail_crop.height' => ['required_with:thumbnail_crop', 'integer'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the press coverage.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'publication_name.required' => 'Please provide the publication name.',
            'publication_name.max' => 'The publication name may not be greater than 255 characters.',
            'publication_date.required' => 'Please provide the publication date.',
            'publication_date.date' => 'The publication date must be a valid date.',
            'url.required' => 'Please provide a URL to the article.',
            'url.url' => 'The URL must be a valid URL.',
            'url.max' => 'The URL may not be greater than 500 characters.',
            'excerpt.max' => 'The excerpt may not be greater than 500 characters.',
            'thumbnail.image' => 'The thumbnail must be an image file.',
            'thumbnail.mimes' => 'The thumbnail must be a file of type: png, jpg, jpeg, gif, webp.',
            'thumbnail.max' => 'The thumbnail may not be greater than 2MB.',
            'thumbnail.dimensions' => 'The thumbnail must be at least 100x100 pixels.',
            'thumbnail_crop.array' => 'The thumbnail crop data must be an array.',
            'thumbnail_crop.x.required_with' => 'The x coordinate is required when crop data is provided.',
            'thumbnail_crop.x.integer' => 'The x coordinate must be an integer.',
            'thumbnail_crop.y.required_with' => 'The y coordinate is required when crop data is provided.',
            'thumbnail_crop.y.integer' => 'The y coordinate must be an integer.',
            'thumbnail_crop.width.required_with' => 'The width is required when crop data is provided.',
            'thumbnail_crop.width.integer' => 'The width must be an integer.',
            'thumbnail_crop.height.required_with' => 'The height is required when crop data is provided.',
            'thumbnail_crop.height.integer' => 'The height must be an integer.',
            'remove_thumbnail.boolean' => 'The remove thumbnail flag must be true or false.',
            'is_published.boolean' => 'The published status must be true or false.',
            'display_order.integer' => 'The display order must be an integer.',
            'display_order.min' => 'The display order must be at least 0.',
        ];
    }
}
