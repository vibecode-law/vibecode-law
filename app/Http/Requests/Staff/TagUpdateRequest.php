<?php

namespace App\Http\Requests\Staff;

use App\Enums\TagType;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Tag::class, 'name')->ignore($this->route('tag'))],
            'type' => ['required', Rule::enum(TagType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A tag name is required.',
            'name.max' => 'The tag name may not be greater than 255 characters.',
            'name.unique' => 'A tag with this name already exists.',
            'type.required' => 'A tag type is required.',
            'type.enum' => 'The selected tag type is invalid.',
        ];
    }
}
