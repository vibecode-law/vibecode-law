<?php

namespace App\Services\Course;

use App\Models\Course\Lesson;
use App\Models\Course\LessonTranscriptLine;

class VttParserService
{
    /**
     * Parse VTT content and persist transcript lines for a lesson.
     */
    public function parseAndPersist(string $vttContent, Lesson $lesson): void
    {
        $lines = $this->parse(vttContent: $vttContent);

        $lesson->transcriptLines()->delete();

        $now = now();

        $records = array_map(fn (array $line, int $index): array => [
            'lesson_id' => $lesson->id,
            'start_seconds' => $line['start_seconds'],
            'end_seconds' => $line['end_seconds'],
            'text' => $line['text'],
            'order' => $index,
            'created_at' => $now,
            'updated_at' => $now,
        ], $lines, array_keys($lines));

        LessonTranscriptLine::query()->insert($records);
    }

    /**
     * Parse VTT content into an array of cue data.
     *
     * @return array<int, array{start_seconds: string, end_seconds: string, text: string}>
     */
    public function parse(string $vttContent): array
    {
        $lines = [];
        $vttContent = str_replace("\r\n", "\n", $vttContent);
        $blocks = preg_split('/\n\n+/', trim($vttContent));

        if ($blocks === false) {
            return [];
        }

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === 'WEBVTT' || str_starts_with($block, 'WEBVTT')) {
                continue;
            }

            if ($block === 'NOTE' || str_starts_with($block, 'NOTE')) {
                continue;
            }

            $blockLines = explode("\n", $block);
            $timestampLine = null;
            $textLines = [];

            foreach ($blockLines as $blockLine) {
                if ($timestampLine === null && str_contains($blockLine, '-->')) {
                    $timestampLine = $blockLine;
                } elseif ($timestampLine !== null) {
                    $textLines[] = $blockLine;
                }
            }

            if ($timestampLine === null || $textLines === []) {
                continue;
            }

            $timestamps = $this->parseTimestampLine(line: $timestampLine);

            if ($timestamps === null) {
                continue;
            }

            $lines[] = [
                'start_seconds' => $timestamps['start'],
                'end_seconds' => $timestamps['end'],
                'text' => implode("\n", $textLines),
            ];
        }

        return $lines;
    }

    /**
     * Parse a VTT timestamp line into start and end seconds.
     *
     * @return array{start: string, end: string}|null
     */
    private function parseTimestampLine(string $line): ?array
    {
        $parts = preg_split('/\s*-->\s*/', trim($line));

        if ($parts === false || count($parts) !== 2) {
            return null;
        }

        $start = $this->timestampToSeconds(timestamp: $parts[0]);
        $end = $this->timestampToSeconds(timestamp: $parts[1]);

        if ($start === null || $end === null) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Convert a VTT timestamp (HH:MM:SS.mmm or MM:SS.mmm) to decimal seconds.
     */
    private function timestampToSeconds(string $timestamp): ?string
    {
        $timestamp = trim($timestamp);

        if (preg_match('/^(?:(\d+):)?(\d{2}):(\d{2})\.(\d{3})$/', $timestamp, $matches) !== 1) {
            return null;
        }

        $hours = $matches[1] !== '' ? (int) $matches[1] : 0;
        $minutes = (int) $matches[2];
        $seconds = (int) $matches[3];
        $milliseconds = (int) $matches[4];

        $total = ($hours * 3600) + ($minutes * 60) + $seconds + ($milliseconds / 1000);

        return number_format($total, decimals: 3, decimal_separator: '.', thousands_separator: '');
    }
}
