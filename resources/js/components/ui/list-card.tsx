import * as React from 'react';

import { cn } from '@/lib/utils';

function ListCard({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="list-card"
            className={cn(
                'rounded-lg border bg-white dark:border-neutral-800 dark:bg-neutral-900',
                className,
            )}
            {...props}
        />
    );
}

function ListCardHeader({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="list-card-header"
            className={cn(
                'flex items-center justify-between border-b px-4 py-3 dark:border-neutral-800',
                className,
            )}
            {...props}
        />
    );
}

function ListCardTitle({ className, ...props }: React.ComponentProps<'h3'>) {
    return (
        <h3
            data-slot="list-card-title"
            className={cn(
                'font-semibold text-neutral-900 dark:text-white',
                className,
            )}
            {...props}
        />
    );
}

function ListCardContent({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="list-card-content"
            className={cn(
                'divide-y divide-neutral-100 px-4 dark:divide-neutral-800',
                className,
            )}
            {...props}
        />
    );
}

function ListCardFooter({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="list-card-footer"
            className={cn(
                'border-t px-4 py-3 dark:border-neutral-800',
                className,
            )}
            {...props}
        />
    );
}

function ListCardEmpty({ className, ...props }: React.ComponentProps<'p'>) {
    return (
        <p
            data-slot="list-card-empty"
            className={cn(
                'py-12 text-center text-sm text-neutral-500 dark:text-neutral-300',
                className,
            )}
            {...props}
        />
    );
}

export {
    ListCard,
    ListCardHeader,
    ListCardTitle,
    ListCardContent,
    ListCardFooter,
    ListCardEmpty,
};
