import { RichTextContent } from '@/components/showcase/rich-text-content';
import { useState } from 'react';

interface CourseLearningObjectivesProps {
    html: string;
    truncateOnMobile?: boolean;
}

export function CourseLearningObjectives({
    html,
    truncateOnMobile = false,
}: CourseLearningObjectivesProps) {
    const [isExpanded, setIsExpanded] = useState(false);

    if (!truncateOnMobile) {
        return (
            <div className="mt-8">
                <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                    What You'll Learn
                </h2>
                <div className="prose dark:prose-invert mt-4 max-w-none">
                    <RichTextContent
                        html={html}
                        className="rich-text-content"
                    />
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
                            ? 'prose dark:prose-invert max-w-none'
                            : 'prose dark:prose-invert line-clamp-3 max-w-none overflow-hidden md:line-clamp-none'
                    }
                >
                    <RichTextContent
                        html={html}
                        className="rich-text-content"
                    />
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
