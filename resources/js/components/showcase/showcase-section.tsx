import { ChevronDown, ChevronRight } from 'lucide-react';
import { useState } from 'react';

interface ShowcaseSectionProps<T> {
    title: string;
    items: T[];
    emptyMessage: string;
    children: (item: T) => React.ReactNode;
    defaultOpen?: boolean;
}

export function ShowcaseSection<T>({
    title,
    items,
    emptyMessage,
    children,
    defaultOpen = true,
}: ShowcaseSectionProps<T>) {
    const [isOpen, setIsOpen] = useState(defaultOpen);
    const count = items.length;

    return (
        <div className="rounded-lg border bg-white dark:border-neutral-800 dark:bg-neutral-900">
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className="flex w-full items-center justify-between px-4 py-3 text-left"
            >
                <div className="flex items-center gap-2">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        {title}
                    </h3>
                    <span className="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                        {count}
                    </span>
                </div>
                {isOpen ? (
                    <ChevronDown className="size-5 text-neutral-400" />
                ) : (
                    <ChevronRight className="size-5 text-neutral-400" />
                )}
            </button>

            {isOpen === true && (
                <div className="border-t px-4 dark:border-neutral-800">
                    {count > 0 ? (
                        <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                            {items.map((item, index) => (
                                <div key={index}>{children(item)}</div>
                            ))}
                        </div>
                    ) : (
                        <p className="py-8 text-center text-sm text-neutral-500 dark:text-neutral-300">
                            {emptyMessage}
                        </p>
                    )}
                </div>
            )}
        </div>
    );
}
