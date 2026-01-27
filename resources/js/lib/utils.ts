import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export const CONFETTI_COLORS = [
    '#fbbf24',
    '#f59e0b',
    '#f97316',
    '#ef4444',
    '#8b5cf6',
    '#3b82f6',
    '#10b981',
    '#ec4899',
];
