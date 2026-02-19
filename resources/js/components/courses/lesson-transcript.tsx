import { useCallback } from 'react';

interface LessonTranscriptProps {
    lines: App.Http.Resources.Course.LessonTranscriptLineResource[];
    onSeek: (timeSeconds: number) => void;
}

function formatTimestamp(totalSeconds: number): string {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = Math.floor(totalSeconds % 60);

    if (hours > 0) {
        return `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    return `${minutes}:${String(seconds).padStart(2, '0')}`;
}

export function LessonTranscript({ lines, onSeek }: LessonTranscriptProps) {
    const handleTimestampClick = useCallback(
        (timeSeconds: number) => {
            onSeek(timeSeconds);
        },
        [onSeek],
    );

    if (lines.length === 0) {
        return (
            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                No transcript available for this lesson.
            </p>
        );
    }

    return (
        <div className="space-y-3">
            {lines.map((line) => (
                <div key={line.id} className="flex gap-3 md:gap-4">
                    <button
                        type="button"
                        onClick={() => handleTimestampClick(line.start_seconds)}
                        className="shrink-0 pt-0.5 font-mono text-xs text-blue-600 hover:text-blue-700 md:text-sm dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        {formatTimestamp(line.start_seconds)}
                    </button>
                    <p className="text-sm text-neutral-700 md:text-base dark:text-neutral-300">
                        {line.text}
                    </p>
                </div>
            ))}
        </div>
    );
}
