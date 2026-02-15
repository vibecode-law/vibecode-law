import { useState } from 'react';

interface LessonTranscriptProps {
    transcript: string;
    truncateOnMobile?: boolean;
}

export function LessonTranscript({
    transcript,
    truncateOnMobile = false,
}: LessonTranscriptProps) {
    const [isExpanded, setIsExpanded] = useState(false);

    if (!truncateOnMobile) {
        return (
            <div className="mb-8">
                <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                    Transcript
                </h2>
                <div className="prose-sm prose dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400">
                    {transcript}
                </div>
            </div>
        );
    }

    return (
        <div className="mb-8">
            <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                Transcript
            </h2>
            <div>
                <div
                    className={
                        isExpanded
                            ? 'prose-sm prose dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400'
                            : 'prose-sm prose dark:prose-invert line-clamp-4 max-w-none text-neutral-600 md:line-clamp-none dark:text-neutral-400'
                    }
                >
                    {transcript}
                </div>
                {!isExpanded && (
                    <button
                        type="button"
                        onClick={() => setIsExpanded(true)}
                        className="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700 md:hidden dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Show more
                    </button>
                )}
            </div>
        </div>
    );
}
