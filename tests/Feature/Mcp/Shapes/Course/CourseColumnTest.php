<?php

use App\Mcp\Shapes\Course\CourseColumn;
use App\Mcp\Shapes\Course\CourseDetailResource;
use App\Mcp\Tools\Staff\Course\ListCoursesTool;
use App\Models\Course\Course;

it('covers every detail field exactly once across summary and columns', function (): void {
    $course = Course::factory()->create();

    $fields = array_keys(CourseDetailResource::from($course)->toArray());

    expect([...ListCoursesTool::SUMMARY_FIELDS, ...CourseColumn::values()])
        ->toEqualCanonicalizing($fields);
});
