<?php

namespace App\Mcp\Shapes;

use App\Models\PracticeArea;
use Spatie\LaravelData\Resource;

class PracticeAreaResource extends Resource
{
    public int $id;

    public string $name;

    public string $slug;

    public static function fromModel(PracticeArea $practiceArea): self
    {
        return self::from([
            'id' => $practiceArea->id,
            'name' => $practiceArea->name,
            'slug' => $practiceArea->slug,
        ]);
    }
}
