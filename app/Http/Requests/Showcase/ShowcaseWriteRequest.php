<?php

namespace App\Http\Requests\Showcase;

use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShowcaseWriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ?Showcase $showcase */
        $showcase = $this->route('showcase');

        if ($showcase === null) {
            return $this->user()->can('create', Showcase::class);
        }

        return $this->user()->can('update', $showcase);
    }

    protected function prepareForValidation(): void
    {
        $sourceStatus = SourceStatus::tryFrom((int) $this->input('source_status'));

        if ($sourceStatus === null || $sourceStatus->hasSourceUrl() === false) {
            $this->merge(['source_url' => null]);
        }

        $this->prepareSlug();
    }

    protected function prepareSlug(): void
    {
        if ($this->has('slug') === false) {
            return;
        }

        $slug = Str::lower($this->input('slug'));

        if (preg_match(pattern: '/-\d{6}$/', subject: $slug) !== 1) {
            $randomSuffix = random_int(min: 100000, max: 999999);
            $slug = $slug.'-'.$randomSuffix;
        }

        $this->merge(['slug' => $slug]);
    }

    protected function isModOrAdmin(): bool
    {
        return $this->user()->is_admin === true || $this->user()->can('showcase.approve-reject');
    }

    protected function getSlugRequirementRule(?Showcase $showcase): string
    {
        // Slug is prohibited on creation - auto-generated in controller
        if ($showcase === null) {
            return 'prohibited';
        }

        // Approved showcases cannot have their slug changed
        if ($showcase->isApproved() === true) {
            return 'prohibited';
        }

        // Normal users cannot change the slug
        if ($this->isModOrAdmin() === false) {
            return 'prohibited';
        }

        // Mods and admins must provide a slug when updating
        return 'required';
    }

    public function rules(): array
    {
        /** @var ?Showcase $showcase */
        $showcase = $this->route('showcase');

        $practiceAreaIds = PracticeArea::pluck('id');

        return [
            'practice_area_ids' => ['required', 'array', 'min:1'],
            'practice_area_ids.*' => [
                'required',
                'integer',
                Rule::in($practiceAreaIds),
            ],
            'title' => ['required', 'string', 'max:60'],
            'slug' => [
                $this->getSlugRequirementRule(showcase: $showcase),
                'string',
                'min:2',
                'max:67', // 60 chars user input + 7 chars for "-XXXXXX" suffix
                'regex:/^[a-z][a-z-]*[a-z]-\d{6}$/',
                Rule::unique('showcases', 'slug')->ignore($showcase?->id),
            ],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'key_features' => ['required', 'string', 'max:5000'],
            'help_needed' => ['nullable', 'string', 'max:5000'],
            'url' => ['nullable', 'url', 'max:500'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'source_status' => ['required', Rule::enum(SourceStatus::class)],
            'source_url' => [
                'nullable',
                'url',
                'max:500',
                Rule::requiredIf(fn () => in_array(
                    (int) $this->input('source_status'),
                    [SourceStatus::SourceAvailable->value, SourceStatus::OpenSource->value],
                    strict: true,
                )),
            ],
            'thumbnail' => ['nullable', 'image', 'dimensions:min_width=100,min_height=100', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'thumbnail_crop' => ['nullable', 'required_with:thumbnail', 'array'],
            'thumbnail_crop.x' => ['required_with:thumbnail_crop', 'integer', 'min:0'],
            'thumbnail_crop.y' => ['required_with:thumbnail_crop', 'integer', 'min:0'],
            'thumbnail_crop.width' => ['required_with:thumbnail_crop', 'integer', 'min:1'],
            'thumbnail_crop.height' => ['required_with:thumbnail_crop', 'integer', 'min:1'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'dimensions:min_width=400,min_height=225', 'max:4096'],
            'removed_images' => ['nullable', 'array'],
            'removed_images.*' => ['integer', Rule::exists('showcase_images', 'id')->where('showcase_id', $showcase?->id)],
            'submit' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var ?Showcase $showcase */
            $showcase = $this->route('showcase');

            $existingImagesCount = $showcase?->images()->count() ?? 0;
            $removedImagesCount = count($this->input('removed_images', []));
            $newImagesCount = count($this->file('images', []));

            $totalImages = $existingImagesCount - $removedImagesCount + $newImagesCount;

            if ($totalImages < 1) {
                $validator->errors()->add('images', 'There should be at least one image.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'practice_area_ids.required' => 'Please select at least one practice area.',
            'practice_area_ids.min' => 'Please select at least one practice area.',
            'practice_area_ids.*.exists' => 'One or more selected practice areas are invalid.',
            'title.required' => 'Please provide a title for your showcase.',
            'title.max' => 'The title must not exceed 60 characters.',
            'slug.required' => 'Please provide a slug for the showcase.',
            'slug.min' => 'The slug must be at least 2 characters.',
            'slug.max' => 'The slug must not exceed 60 characters.',
            'slug.regex' => 'The slug must contain only lowercase letters and hyphens.',
            'slug.unique' => 'This slug is already taken.',
            'slug.prohibited' => 'You cannot set or change the slug.',
            'tagline.required' => 'Please provide a short tagline for your showcase.',
            'description.required' => 'Please provide a description.',
            'key_features.required' => 'Please provide the key features of your project.',
            'url.url' => 'Please provide a valid URL.',
            'video_url.url' => 'Please provide a valid video URL.',
            'source_status.required' => 'Please specify the source availability for this project.',
            'source_status.enum' => 'Please select a valid source availability option.',
            'source_url.required' => 'Please provide a source code URL.',
            'source_url.url' => 'Please provide a valid source code URL.',
            'thumbnail.required' => 'Please upload a project thumbnail.',
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.dimensions' => 'The thumbnail must be at least 100x100 pixels.',
            'thumbnail.max' => 'The thumbnail must not exceed 2MB.',
            'thumbnail_crop.required_with' => 'Please crop the thumbnail before saving.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.dimensions' => 'Images must be at least 400x225 pixels.',
            'images.*.max' => 'Images must not exceed 4MB.',
        ];
    }
}
