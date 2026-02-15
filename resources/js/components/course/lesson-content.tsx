import { RichTextContent } from '@/components/showcase/rich-text-content';
import { useState } from 'react';

interface LessonContentProps {
    html: string;
    truncateOnMobile?: boolean;
}

export function LessonContent({
    html,
    truncateOnMobile = false,
}: LessonContentProps) {
    const [isExpanded, setIsExpanded] = useState(false);

    if (!truncateOnMobile) {
        return (
            <div className="mb-8">
                <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                    Lesson
                </h2>
                <div className="prose dark:prose-invert max-w-none">
                    <RichTextContent
                        html={html}
                        className="rich-text-content"
                    />
                </div>
            </div>
        );
    }

    return (
        <div className="mb-8">
            <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                Lesson
            </h2>
            <div>
                <div
                    className={
                        isExpanded
                            ? 'prose dark:prose-invert max-w-none'
                            : 'prose dark:prose-invert line-clamp-4 max-w-none overflow-hidden md:line-clamp-none'
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
