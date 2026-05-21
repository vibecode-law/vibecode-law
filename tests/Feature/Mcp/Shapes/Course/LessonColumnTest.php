<?php

use App\Mcp\Shapes\Course\LessonColumn;
use App\Mcp\Shapes\Course\LessonDetailResource;
use App\Mcp\Tools\Staff\Course\ListLessonsTool;
use App\Models\Course\Lesson;

it('covers every detail field exactly once across summary and columns', function (): void {
    $lesson = Lesson::factory()->create();

    $fields = array_keys(LessonDetailResource::from($lesson)->toArray());

    expect([...ListLessonsTool::SUMMARY_FIELDS, ...LessonColumn::values()])
        ->toEqualCanonicalizing($fields);
});
