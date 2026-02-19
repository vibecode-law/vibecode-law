<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;
use App\Models\Tag;

describe('courses relationship', function () {
    test('tag can belong to many courses', function () {
        $tag = Tag::factory()->create();
        $courses = Course::factory()->count(3)->create();

        $tag->courses()->attach($courses);

        expect($tag->courses)->toHaveCount(3);
        expect($tag->courses->first())->toBeInstanceOf(Course::class);
    });

    test('tag with no courses returns empty collection', function () {
        $tag = Tag::factory()->create();

        expect($tag->courses)->toBeEmpty();
    });

    test('deleting tag removes pivot records', function () {
        $tag = Tag::factory()->create();
        $courses = Course::factory()->count(2)->create();

        $tag->courses()->attach($courses);

        $tag->delete();

        expect(
            Course::query()
                ->whereHas('tags', fn ($q) => $q->where('tag_id', $tag->id))
                ->count()
        )->toBe(0);
    });
});

describe('lessons relationship', function () {
    test('tag can belong to many lessons', function () {
        $tag = Tag::factory()->create();
        $course = Course::factory()->create();
        $lessons = Lesson::factory()->count(3)->for($course)->create();

        $tag->lessons()->attach($lessons);

        expect($tag->lessons)->toHaveCount(3);
        expect($tag->lessons->first())->toBeInstanceOf(Lesson::class);
    });

    test('tag with no lessons returns empty collection', function () {
        $tag = Tag::factory()->create();

        expect($tag->lessons)->toBeEmpty();
    });

    test('deleting tag removes lesson pivot records', function () {
        $tag = Tag::factory()->create();
        $course = Course::factory()->create();
        $lessons = Lesson::factory()->count(2)->for($course)->create();

        $tag->lessons()->attach($lessons);

        $tag->delete();

        expect(
            Lesson::query()
                ->whereHas('tags', fn ($q) => $q->where('tag_id', $tag->id))
                ->count()
        )->toBe(0);
    });
});
