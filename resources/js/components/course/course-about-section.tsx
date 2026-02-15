import { RichTextContent } from '@/components/showcase/rich-text-content';
import { useState } from 'react';

interface CourseAboutSectionProps {
    html: string;
    truncateOnMobile?: boolean;
}

export function CourseAboutSection({
    html,
    truncateOnMobile = false,
}: CourseAboutSectionProps) {
    const [isExpanded, setIsExpanded] = useState(false);

    if (!truncateOnMobile) {
        return (
            <div className="mt-8">
                <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                    About This Course
                </h2>
                <div className="mt-4">
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
                About This Course
            </h2>
            <div className="mt-4">
                <div
                    className={
                        isExpanded
                            ? ''
                            : 'line-clamp-3 overflow-hidden md:line-clamp-none'
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
