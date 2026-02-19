<?php

use App\Services\Course\VttParserService;

test('parses standard VTT content into cue data', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        00:00:01.000 --> 00:00:04.500
        Hello and welcome to this lesson.

        00:00:05.000 --> 00:00:09.250
        Today we'll be covering contract law.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(2)
        ->and($result[0])->toBe([
            'start_seconds' => '1.000',
            'end_seconds' => '4.500',
            'text' => 'Hello and welcome to this lesson.',
        ])
        ->and($result[1])->toBe([
            'start_seconds' => '5.000',
            'end_seconds' => '9.250',
            'text' => 'Today we\'ll be covering contract law.',
        ]);
});

test('handles multi-line cue text', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        00:00:01.000 --> 00:00:04.000
        First line of text.
        Second line of text.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(1)
        ->and($result[0]['text'])->toBe("First line of text.\nSecond line of text.");
});

test('handles VTT with cue identifiers', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        1
        00:00:01.000 --> 00:00:03.000
        First cue.

        2
        00:00:04.000 --> 00:00:06.000
        Second cue.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(2)
        ->and($result[0]['text'])->toBe('First cue.')
        ->and($result[1]['text'])->toBe('Second cue.');
});

test('converts hour-based timestamps correctly', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        01:30:45.123 --> 02:15:30.456
        A cue well into the video.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(1)
        ->and($result[0]['start_seconds'])->toBe('5445.123')
        ->and($result[0]['end_seconds'])->toBe('8130.456');
});

test('handles MM:SS.mmm format without hours', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        05:30.500 --> 06:00.000
        Short format timestamp.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(1)
        ->and($result[0]['start_seconds'])->toBe('330.500')
        ->and($result[0]['end_seconds'])->toBe('360.000');
});

test('skips NOTE blocks', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        NOTE
        This is a comment.

        00:00:01.000 --> 00:00:03.000
        Actual content.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(1)
        ->and($result[0]['text'])->toBe('Actual content.');
});

test('returns empty array for empty VTT content', function () {
    $service = new VttParserService;

    $result = $service->parse(vttContent: 'WEBVTT');

    expect($result)->toBe([]);
});

test('returns empty array for VTT with no cues', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        NOTE
        Only comments here.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toBe([]);
});

test('handles Windows-style line endings', function () {
    $service = new VttParserService;

    $vtt = "WEBVTT\r\n\r\n00:00:01.000 --> 00:00:03.000\r\nWindows line endings.\r\n";

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(1)
        ->and($result[0]['text'])->toBe('Windows line endings.');
});

test('parses a real transcript fixture file', function () {
    $service = new VttParserService;

    $vtt = file_get_contents(__DIR__.'/../../../Fixtures/transcript.vtt');

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(916)
        ->and($result[0])->toBe([
            'start_seconds' => '0.000',
            'end_seconds' => '1.120',
            'text' => '[MUSIC PLAYING]',
        ])
        ->and($result[915])->toBe([
            'start_seconds' => '2397.520',
            'end_seconds' => '2401.120',
            'text' => 'of Developer Tools Companies and other industry leaders.',
        ])
        ->and(collect($result)->every(fn (array $cue): bool => isset($cue['start_seconds'], $cue['end_seconds'], $cue['text'])
        ))->toBeTrue();
});

test('skips cues with invalid timestamp format', function () {
    $service = new VttParserService;

    $vtt = <<<'VTT'
        WEBVTT

        bad --> timestamps
        This should be skipped.

        00:00:01.000 --> 00:00:03.000
        This should be kept.
        VTT;

    $result = $service->parse(vttContent: $vtt);

    expect($result)->toHaveCount(1)
        ->and($result[0]['text'])->toBe('This should be kept.');
});
