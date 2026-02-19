<?php

namespace App\Http\Requests\Api\Learn;

use App\Enums\VideoPlayerEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonPlayerEventRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event' => ['required', Rule::enum(VideoPlayerEvent::class)],
            'current_time' => ['required_if:event,timeupdate', 'numeric', 'min:0'],
        ];
    }
}
