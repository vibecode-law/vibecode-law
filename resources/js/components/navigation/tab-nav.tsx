import { useActiveUrl } from '@/hooks/use-active-url';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

export interface TabNavItem {
    title: string;
    href?: string;
    onClick?: () => void;
    isActive?: boolean;
}

interface TabNavProps {
    items: TabNavItem[];
    ariaLabel: string;
}

export function TabNav({ items, ariaLabel }: TabNavProps) {
    const { urlIsActive } = useActiveUrl();

    return (
        <nav
            className="flex gap-1 overflow-x-auto border-b border-neutral-200 dark:border-neutral-800"
            aria-label={ariaLabel}
        >
            {items.map((item, index) => {
                const isActive = item.href
                    ? urlIsActive(item.href)
                    : (item.isActive ?? false);

                const className = cn(
                    'shrink-0 border-b-2 px-4 py-3 text-sm font-medium transition-colors',
                    isActive
                        ? 'border-neutral-900 text-neutral-900 dark:border-white dark:text-white'
                        : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:text-neutral-300',
                );

                if (item.href) {
                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            prefetch
                            className={className}
                        >
                            {item.title}
                        </Link>
                    );
                }

                return (
                    <button
                        key={index}
                        type="button"
                        onClick={item.onClick}
                        className={className}
                    >
                        {item.title}
                    </button>
                );
            })}
        </nav>
    );
}
