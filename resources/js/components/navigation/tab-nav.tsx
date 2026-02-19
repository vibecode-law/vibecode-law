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
    variant?: 'default' | 'secondary';
}

export function TabNav({ items, ariaLabel, variant = 'default' }: TabNavProps) {
    const { urlIsActive } = useActiveUrl();

    return (
        <nav
            className={cn(
                'flex overflow-x-auto',
                variant === 'default' ? 'gap-1' : 'gap-2',
                variant === 'default' &&
                    'border-b border-neutral-200 dark:border-neutral-800',
            )}
            aria-label={ariaLabel}
        >
            {items.map((item, index) => {
                const isActive =
                    item.isActive ??
                    (item.href ? urlIsActive(item.href) : false);

                const className = cn(
                    'shrink-0 font-medium transition-colors',
                    variant === 'default' && [
                        'border-b-2 px-4 py-3 text-sm',
                        isActive
                            ? 'border-neutral-900 text-neutral-900 dark:border-white dark:text-white'
                            : 'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:text-neutral-300',
                    ],
                    variant === 'secondary' && [
                        'rounded-lg border px-4 py-2 text-sm',
                        isActive
                            ? 'border-neutral-900 bg-neutral-900 text-white dark:border-white dark:bg-white dark:text-neutral-900'
                            : 'border-neutral-200 bg-neutral-50 text-neutral-600 hover:border-neutral-300 hover:bg-neutral-100 hover:text-neutral-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400 dark:hover:border-neutral-600 dark:hover:bg-neutral-700 dark:hover:text-neutral-200',
                    ],
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
