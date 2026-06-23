import { type PaginatedData } from '@/types';
import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { Button } from './button';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    prev_page_url?: string | null;
    next_page_url?: string | null;
}

interface PaginationLinks {
    prev: string | null;
    next: string | null;
}

type PaginationProps = {
    meta: PaginationMeta;
    links?: PaginationLinks;
    preserveScroll?: boolean;
    className?: string;
} & (
    | { variant?: 'default' }
    | { variant: 'simple' }
);

function getPrevUrl(meta: PaginationMeta, links?: PaginationLinks): string | null {
    return links?.prev ?? meta.prev_page_url ?? null;
}

function getNextUrl(meta: PaginationMeta, links?: PaginationLinks): string | null {
    return links?.next ?? meta.next_page_url ?? null;
}

export function Pagination({
    meta,
    links,
    preserveScroll = false,
    variant = 'default',
    className,
}: PaginationProps) {
    if (meta.last_page <= 1) {
        return null;
    }

    const prevUrl = getPrevUrl(meta, links);
    const nextUrl = getNextUrl(meta, links);

    if (variant === 'simple') {
        return (
            <nav className={cn('flex items-center justify-center gap-2', className)}>
                {prevUrl !== null && (
                    <Link
                        href={prevUrl}
                        className="rounded bg-neutral-100 px-4 py-2 text-sm hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700"
                        preserveScroll={preserveScroll}
                    >
                        Previous
                    </Link>
                )}
                <span className="px-4 py-2 text-sm text-neutral-500">
                    Page {meta.current_page} of {meta.last_page}
                </span>
                {nextUrl !== null && (
                    <Link
                        href={nextUrl}
                        className="rounded bg-neutral-100 px-4 py-2 text-sm hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700"
                        preserveScroll={preserveScroll}
                    >
                        Next
                    </Link>
                )}
            </nav>
        );
    }

    return (
        <div className={cn('flex items-center justify-between', className)}>
            <p className="text-sm text-neutral-500 dark:text-neutral-300">
                Page {meta.current_page} of {meta.last_page}
            </p>
            <div className="flex gap-2">
                {prevUrl !== null && (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={prevUrl} preserveScroll={preserveScroll}>
                            Previous
                        </Link>
                    </Button>
                )}
                {nextUrl !== null && (
                    <Button variant="outline" size="sm" asChild>
                        <Link href={nextUrl} preserveScroll={preserveScroll}>
                            Next
                        </Link>
                    </Button>
                )}
            </div>
        </div>
    );
}

export function paginationFromData<T>(data: PaginatedData<T>): {
    meta: PaginationMeta;
    links: PaginationLinks;
} {
    return {
        meta: data.meta,
        links: data.links,
    };
}
