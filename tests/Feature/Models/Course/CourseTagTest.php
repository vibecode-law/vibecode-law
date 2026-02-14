<?php

use App\Models\Course\Course;
use App\Models\Course\CourseTag;

describe('courses relationship', function () {
    test('tag can belong to many courses', function () {
        $tag = CourseTag::factory()->create();
        $courses = Course::factory()->count(3)->create();

        $tag->courses()->attach($courses);

        expect($tag->courses)->toHaveCount(3);
        expect($tag->courses->first())->toBeInstanceOf(Course::class);
    });

    test('tag with no courses returns empty collection', function () {
        $tag = CourseTag::factory()->create();

        expect($tag->courses)->toBeEmpty();
    });

    test('deleting tag removes pivot records', function () {
        $tag = CourseTag::factory()->create();
        $courses = Course::factory()->count(2)->create();

        $tag->courses()->attach($courses);

        $tag->delete();

        expect(
            Course::query()
                ->whereHas('tags', fn ($q) => $q->where('course_tag_id', $tag->id))
                ->count()
        )->toBe(0);
    });
});
