<?php

namespace App\Http\Resources\Course;

use App\Models\Course\LessonTranscriptLine;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class LessonTranscriptLineResource extends Resource
{
    public int $id;

    public float $start_seconds;

    public string $text;

    public static function fromModel(LessonTranscriptLine $line): self
    {
        return self::from([
            'id' => $line->id,
            'start_seconds' => (float) $line->start_seconds,
            'text' => $line->text,
        ]);
    }
}
