<?php

namespace App\Http\Requests\Showcase;

use App\Enums\SourceStatus;
use App\Models\PracticeArea;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\Showcase\ShowcaseDraftImage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowcaseDraftWriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ShowcaseDraft $draft */
        $draft = $this->route('draft');

        return $this->user()->can('update', $draft);
    }

    protected function prepareForValidation(): void
    {
        $sourceStatus = SourceStatus::tryFrom((int) $this->input('source_status'));

        if ($sourceStatus === null || $sourceStatus->hasSourceUrl() === false) {
            $this->merge(['source_url' => null]);
        }
    }

    public function rules(): array
    {
        /** @var ShowcaseDraft $draft */
        $draft = $this->route('draft');

        $practiceAreaIds = PracticeArea::pluck('id');

        return [
            'practice_area_ids' => ['required', 'array', 'min:1'],
            'practice_area_ids.*' => [
                'required',
                'integer',
                Rule::in($practiceAreaIds),
            ],
            'title' => ['required', 'string', 'max:60'],
            'tagline' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'key_features' => ['nullable', 'string', 'max:5000'],
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
            'launch_date' => ['nullable', 'date', 'before_or_equal:today'],
            'thumbnail' => ['nullable', 'image', 'dimensions:min_width=400,min_height=400', 'max:1024'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'thumbnail_crop' => ['nullable', 'required_with:thumbnail', 'array'],
            'thumbnail_crop.x' => ['required_with:thumbnail_crop', 'integer', 'min:0'],
            'thumbnail_crop.y' => ['required_with:thumbnail_crop', 'integer', 'min:0'],
            'thumbnail_crop.width' => ['required_with:thumbnail_crop', 'integer', 'min:1'],
            'thumbnail_crop.height' => ['required_with:thumbnail_crop', 'integer', 'min:1'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'dimensions:min_width=1000,min_height=562', 'max:2048'],
            'removed_images' => ['nullable', 'array'],
            'removed_images.*' => [
                'integer',
                Rule::exists('showcase_draft_images', 'original_image_id')
                    ->where('showcase_draft_id', $draft->id)
                    ->where('action', ShowcaseDraftImage::ACTION_KEEP),
            ],
            'deleted_new_images' => ['nullable', 'array'],
            'deleted_new_images.*' => [
                'integer',
                Rule::exists('showcase_draft_images', 'id')
                    ->where('showcase_draft_id', $draft->id)
                    ->where('action', ShowcaseDraftImage::ACTION_ADD),
            ],
            'submit' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var ShowcaseDraft $draft */
            $draft = $this->route('draft');

            // Count kept images (existing images minus removed ones)
            $keptImagesCount = $draft->images()
                ->where('action', ShowcaseDraftImage::ACTION_KEEP)
                ->whereNotIn('original_image_id', $this->input('removed_images', []))
                ->count();

            // Count added images (existing new images minus deleted ones, plus new uploads)
            $existingAddedImagesCount = $draft->images()
                ->where('action', ShowcaseDraftImage::ACTION_ADD)
                ->whereNotIn('id', $this->input('deleted_new_images', []))
                ->count();

            $newUploadsCount = count($this->file('images', []));

            $totalImages = $keptImagesCount + $existingAddedImagesCount + $newUploadsCount;

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
            'tagline.required' => 'Please provide a short tagline for your showcase.',
            'description.required' => 'Please provide a description.',
            'url.url' => 'Please provide a valid URL.',
            'video_url.url' => 'Please provide a valid video URL.',
            'source_status.required' => 'Please specify the source availability for this project.',
            'source_status.enum' => 'Please select a valid source availability option.',
            'source_url.required' => 'Please provide a source code URL.',
            'source_url.url' => 'Please provide a valid source code URL.',
            'thumbnail.image' => 'The thumbnail must be an image.',
            'thumbnail.dimensions' => 'The thumbnail must be at least 400x400 pixels.',
            'thumbnail.max' => 'The thumbnail must not exceed 1MB.',
            'thumbnail_crop.required_with' => 'Please crop the thumbnail before saving.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.dimensions' => 'Images must be at least 1000x562 pixels.',
            'images.*.max' => 'Images must not exceed 2MB.',
        ];
    }
}
