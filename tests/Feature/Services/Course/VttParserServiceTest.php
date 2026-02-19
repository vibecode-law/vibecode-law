<?php

use App\Models\Course\Lesson;
use App\Models\Course\LessonTranscriptLine;
use App\Services\Course\VttParserService;

test('parseAndPersist creates transcript lines for a lesson', function () {
    $lesson = Lesson::factory()->create();

    $vtt = <<<'VTT'
        WEBVTT

        00:00:01.000 --> 00:00:04.500
        Hello and welcome.

        00:00:05.000 --> 00:00:09.250
        Let's get started.
        VTT;

    app(VttParserService::class)->parseAndPersist(vttContent: $vtt, lesson: $lesson);

    $lines = $lesson->transcriptLines()->orderBy('order')->get();

    expect($lines)->toHaveCount(2)
        ->and($lines[0]->start_seconds)->toBe('1.000')
        ->and($lines[0]->end_seconds)->toBe('4.500')
        ->and($lines[0]->text)->toBe('Hello and welcome.')
        ->and($lines[0]->order)->toBe(0)
        ->and($lines[1]->start_seconds)->toBe('5.000')
        ->and($lines[1]->end_seconds)->toBe('9.250')
        ->and($lines[1]->text)->toBe('Let\'s get started.')
        ->and($lines[1]->order)->toBe(1);
});

test('parseAndPersist replaces existing transcript lines', function () {
    $lesson = Lesson::factory()->create();
    LessonTranscriptLine::factory()->count(5)->for($lesson)->create();

    expect($lesson->transcriptLines)->toHaveCount(5);

    $vtt = <<<'VTT'
        WEBVTT

        00:00:01.000 --> 00:00:03.000
        Replacement content.
        VTT;

    app(VttParserService::class)->parseAndPersist(vttContent: $vtt, lesson: $lesson);

    $lines = $lesson->fresh()->transcriptLines;

    expect($lines)->toHaveCount(1)
        ->and($lines[0]->text)->toBe('Replacement content.');
});

test('parseAndPersist does not affect other lessons transcript lines', function () {
    $lessonA = Lesson::factory()->create();
    $lessonB = Lesson::factory()->create();

    LessonTranscriptLine::factory()->count(3)->for($lessonB)->create();

    $vtt = <<<'VTT'
        WEBVTT

        00:00:01.000 --> 00:00:03.000
        Only for lesson A.
        VTT;

    app(VttParserService::class)->parseAndPersist(vttContent: $vtt, lesson: $lessonA);

    expect($lessonA->fresh()->transcriptLines)->toHaveCount(1)
        ->and($lessonB->fresh()->transcriptLines)->toHaveCount(3);
});
