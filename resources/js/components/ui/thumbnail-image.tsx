import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

const colorPalette = [
    'bg-rose-500',
    'bg-amber-500',
    'bg-emerald-500',
    'bg-sky-500',
    'bg-violet-500',
    'bg-pink-500',
    'bg-teal-500',
    'bg-orange-500',
];

function getColorIndex(text: string): number {
    let hash = 0;
    for (let i = 0; i < text.length; i++) {
        hash = text.charCodeAt(i) + ((hash << 5) - hash);
    }
    return Math.abs(hash) % colorPalette.length;
}

interface ThumbnailImageProps {
    url?: string | null;
    fallbackText: string;
    alt: string;
    rectString?: string | null;
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}

const sizeClasses = {
    sm: 'size-10',
    md: 'size-14',
    lg: 'size-20',
};

const fontSizeClasses = {
    sm: 'text-xl',
    md: 'text-2xl',
    lg: 'text-3xl',
};

export function ThumbnailImage({
    url,
    fallbackText,
    alt,
    rectString,
    size = 'md',
    className,
}: ThumbnailImageProps) {
    const { transformImages } = usePage<SharedData>().props;

    if (url) {
        const src =
            transformImages === true
                ? `${url}?w=100${rectString ? `&${rectString}` : ''}`
                : url;

        return (
            <img
                src={src}
                alt={alt}
                className={cn(
                    sizeClasses[size],
                    'shrink-0 rounded-lg object-cover',
                    className,
                )}
            />
        );
    }

    const colorClass = colorPalette[getColorIndex(fallbackText)];

    return (
        <div
            className={cn(
                sizeClasses[size],
                'flex shrink-0 items-center justify-center rounded-lg',
                colorClass,
                className,
            )}
        >
            <span
                className={cn(fontSizeClasses[size], 'font-bold text-white')}
            >
                {fallbackText.charAt(0)}
            </span>
        </div>
    );
}
