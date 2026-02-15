import { useState } from 'react';

interface CourseLearningObjectivesProps {
    objectives: string;
    truncateOnMobile?: boolean;
}

export function CourseLearningObjectives({
    objectives,
    truncateOnMobile = false,
}: CourseLearningObjectivesProps) {
    const [isExpanded, setIsExpanded] = useState(false);

    if (!truncateOnMobile) {
        return (
            <div className="mt-8">
                <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                    What You'll Learn
                </h2>
                <div className="mt-4 text-neutral-600 dark:text-neutral-400">
                    {objectives}
                </div>
            </div>
        );
    }

    return (
        <div className="mt-8">
            <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                What You'll Learn
            </h2>
            <div className="mt-4">
                <div
                    className={
                        isExpanded
                            ? 'text-neutral-600 dark:text-neutral-400'
                            : 'line-clamp-3 text-neutral-600 md:line-clamp-none dark:text-neutral-400'
                    }
                >
                    {objectives}
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
