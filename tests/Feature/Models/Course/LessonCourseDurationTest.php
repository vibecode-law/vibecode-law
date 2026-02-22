<?php

use App\Models\Course\Course;
use App\Models\Course\Lesson;

describe('course duration recalculation', function () {
    test('creating a published lesson recalculates course duration', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);

        Lesson::factory()->published()->for($course)->create(['duration_seconds' => 300]);

        expect($course->fresh()->duration_seconds)->toBe(300);
    });

    test('creating a previewable lesson recalculates course duration', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);

        Lesson::factory()->previewable()->for($course)->create(['duration_seconds' => 200]);

        expect($course->fresh()->duration_seconds)->toBe(200);
    });

    test('creating a draft lesson sets course duration to zero', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);

        Lesson::factory()->draft()->for($course)->create(['duration_seconds' => 500]);

        expect($course->fresh()->duration_seconds)->toBe(0);
    });

    test('updating a lesson duration recalculates course duration', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);
        $lesson = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 300]);

        $lesson->update(['duration_seconds' => 600]);

        expect($course->fresh()->duration_seconds)->toBe(600);
    });

    test('deleting a lesson recalculates course duration', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);
        $lessonA = Lesson::factory()->published()->for($course)->create(['duration_seconds' => 300]);
        Lesson::factory()->published()->for($course)->create(['duration_seconds' => 200]);

        $lessonA->delete();

        expect($course->fresh()->duration_seconds)->toBe(200);
    });

    test('sums duration from all visible lessons', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);

        Lesson::factory()->published()->for($course)->create(['duration_seconds' => 300]);
        Lesson::factory()->previewable()->for($course)->create(['duration_seconds' => 200]);
        Lesson::factory()->draft()->for($course)->create(['duration_seconds' => 500]);

        expect($course->fresh()->duration_seconds)->toBe(500);
    });

    test('making a draft lesson visible recalculates course duration', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);
        $lesson = Lesson::factory()->draft()->for($course)->create(['duration_seconds' => 400]);

        expect($course->fresh()->duration_seconds)->toBe(0);

        $lesson->update(['allow_preview' => true]);

        expect($course->fresh()->duration_seconds)->toBe(400);
    });

    test('handles lessons with null duration', function () {
        $course = Course::factory()->create(['duration_seconds' => 0]);

        Lesson::factory()->published()->for($course)->create(['duration_seconds' => 300]);
        Lesson::factory()->published()->for($course)->create(['duration_seconds' => null]);

        expect($course->fresh()->duration_seconds)->toBe(300);
    });
});
