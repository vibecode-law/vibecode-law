<?php

namespace App\Http\Resources\Course;

use App\Models\Course\CourseTag;
use Spatie\LaravelData\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CourseTagResource extends Resource
{
    public int $id;

    public string $name;

    public string $slug;

    public static function fromModel(CourseTag $courseTag): self
    {
        return self::from([
            'id' => $courseTag->id,
            'name' => $courseTag->name,
            'slug' => $courseTag->slug,
        ]);
    }
}
