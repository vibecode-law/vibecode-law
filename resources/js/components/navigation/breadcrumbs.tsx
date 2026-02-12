import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

interface BreadcrumbsProps {
    items: BreadcrumbItem[];
}

export function Breadcrumbs({ items }: BreadcrumbsProps) {
    return (
        <nav aria-label="Breadcrumb" className="min-w-0">
            <ol className="flex flex-nowrap items-center gap-1 text-sm">
                {items.map((item, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <li
                            key={index}
                            className={`flex shrink-0 items-center gap-1 ${isLast ? 'min-w-0 shrink' : ''}`}
                        >
                            {index > 0 && (
                                <ChevronRight className="size-4 shrink-0 text-neutral-400 dark:text-neutral-500" />
                            )}
                            {isLast || !item.href ? (
                                <span
                                    className={`text-neutral-600 dark:text-neutral-400 ${isLast ? 'truncate' : ''}`}
                                    aria-current={isLast ? 'page' : undefined}
                                >
                                    {item.label}
                                </span>
                            ) : (
                                <Link
                                    href={item.href}
                                    className="whitespace-nowrap text-neutral-500 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-200"
                                >
                                    {item.label}
                                </Link>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
