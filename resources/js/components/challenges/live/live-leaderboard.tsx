import { ThumbnailImage } from '@/components/ui/thumbnail-image';
import { useFlipAnimation } from '@/hooks/use-flip-animation';
import { cn } from '@/lib/utils';
import { ChevronUp, Sparkles } from 'lucide-react';

interface LiveLeaderboardProps {
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
    title?: string | null;
    subtitle?: string | null;
    limit?: number;
}

// Shared card surface for the empty state and each ranked row. The white ring is
// inset so it reads as a top highlight rather than leaving a gap between the
// border and the drop shadow in light mode.
const cardSurface =
    'rounded-2xl border border-neutral-200 bg-linear-to-b from-white to-neutral-50 shadow-md shadow-neutral-400/20 ring-1 ring-inset ring-white/60 dark:border-neutral-700/60 dark:from-neutral-800 dark:to-neutral-900 dark:shadow-xl dark:shadow-black/80 dark:ring-white/5';

function rankAccent(rank: number): string {
    if (rank === 1) {
        return 'text-amber-500';
    }

    if (rank === 2) {
        return 'text-neutral-400';
    }

    if (rank === 3) {
        return 'text-orange-600 dark:text-orange-500';
    }

    return 'text-neutral-400 dark:text-neutral-600';
}

export function LiveLeaderboard({
    showcases,
    title,
    subtitle,
    limit,
}: LiveLeaderboardProps) {
    const visibleShowcases =
        limit !== undefined ? showcases.slice(0, limit) : showcases;

    // Standalone (single board, no title) centres the empty state on the screen
    // with no surrounding card; per-category boards keep the bordered box.
    const hasTitle = title !== null && title !== undefined;

    // Re-run the FLIP transition whenever the ranked order changes.
    const orderKey = visibleShowcases.map((showcase) => showcase.id).join(',');
    const register = useFlipAnimation(orderKey);

    return (
        <div className="flex h-full flex-1 flex-col gap-4">
            {hasTitle === true && (
                <div className="space-y-1">
                    <h2 className="text-xl font-semibold tracking-tight text-neutral-900 lg:text-2xl dark:text-white">
                        {title}
                    </h2>
                    {subtitle !== null && subtitle !== undefined && (
                        <p className="text-base text-neutral-600 lg:text-lg dark:text-neutral-400">
                            {subtitle}
                        </p>
                    )}
                </div>
            )}

            {visibleShowcases.length === 0 ? (
                <div
                    className={cn(
                        'relative flex flex-1 flex-col items-center justify-center gap-4 overflow-hidden text-center',
                        hasTitle === true && cn(cardSurface, 'px-6 py-8'),
                    )}
                >
                    <div className="holographic-surface flex size-16 items-center justify-center rounded-2xl text-white shadow-lg lg:size-20">
                        <Sparkles
                            className="size-8 lg:size-10"
                            strokeWidth={2.5}
                        />
                    </div>
                    <h3 className="holographic-text text-3xl font-bold tracking-tight lg:text-4xl">
                        Vibing in progress
                    </h3>
                </div>
            ) : (
                <ul className="space-y-5 lg:space-y-6">
                    {visibleShowcases.map((showcase, index) => {
                        const rank = index + 1;

                        return (
                            <li
                                key={showcase.id}
                                ref={register(showcase.id)}
                                className={cn(
                                    'flex items-center gap-4 px-5 py-4 lg:gap-6 lg:px-6 lg:py-5',
                                    cardSurface,
                                )}
                            >
                                <span
                                    className={cn(
                                        'w-10 shrink-0 text-center text-3xl font-bold tabular-nums lg:w-16 lg:text-5xl',
                                        rankAccent(rank),
                                    )}
                                >
                                    {rank}
                                </span>

                                <ThumbnailImage
                                    url={showcase.thumbnail_url}
                                    rectString={showcase.thumbnail_rect_string}
                                    alt={showcase.title}
                                    fallbackText={showcase.title}
                                    className="size-14 lg:size-20"
                                />

                                <div className="min-w-0 flex-1">
                                    <h3 className="truncate text-xl font-semibold text-neutral-900 lg:text-3xl dark:text-white">
                                        {showcase.title}
                                    </h3>
                                    <p className="truncate text-base text-neutral-600 lg:text-xl dark:text-neutral-400">
                                        {showcase.tagline}
                                    </p>
                                    {showcase.user !== null &&
                                        showcase.user !== undefined && (
                                            <p className="truncate text-sm text-neutral-500 lg:text-base dark:text-neutral-500">
                                                {showcase.user.first_name}{' '}
                                                {showcase.user.last_name}
                                            </p>
                                        )}
                                </div>

                                <div className="flex shrink-0 items-center gap-1.5 text-emerald-600 lg:gap-2 dark:text-emerald-400">
                                    <ChevronUp
                                        className="size-6 lg:size-8"
                                        strokeWidth={3}
                                    />
                                    <span className="text-2xl font-bold tabular-nums lg:text-4xl">
                                        {(
                                            showcase.upvotes_count ?? 0
                                        ).toLocaleString()}
                                    </span>
                                </div>
                            </li>
                        );
                    })}
                </ul>
            )}
        </div>
    );
}
