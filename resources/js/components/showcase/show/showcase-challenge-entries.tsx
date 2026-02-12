import ChallengeShowController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeShowController';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Trophy } from 'lucide-react';

interface ChallengeEntry {
    challenge: Pick<
        App.Http.Resources.Challenge.ChallengeResource,
        'id' | 'slug' | 'title' | 'thumbnail_url' | 'thumbnail_rect_strings'
    >;
    rank: number;
}

interface ShowcaseChallengeEntriesProps {
    challengeEntries: ChallengeEntry[];
    className?: string;
}

export function ShowcaseChallengeEntries({
    challengeEntries,
    className,
}: ShowcaseChallengeEntriesProps) {
    const { transformImages } = usePage<SharedData>().props;

    if (challengeEntries.length === 0) {
        return null;
    }

    return (
        <div className={cn('w-full', className)}>
            <h3 className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                Particpating In
            </h3>
            <div className="space-y-2">
                {challengeEntries.map((entry) => (
                    <Link
                        key={entry.challenge.id}
                        href={ChallengeShowController.url({
                            challenge: entry.challenge.slug,
                        })}
                        className="flex items-center gap-3 rounded-lg border border-neutral-200 bg-white p-3 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:hover:bg-neutral-800"
                    >
                        <ChallengeThumbnail
                            challenge={entry.challenge}
                            transformImages={transformImages}
                        />
                        <div className="min-w-0 flex-1">
                            <div className="truncate text-sm font-medium text-neutral-900 dark:text-white">
                                {entry.challenge.title}
                            </div>
                            <div className="mt-0.5 flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                                <Trophy className="hidden size-3 lg:block" />
                                <span>Challenge</span>
                            </div>
                        </div>
                        <div className="shrink-0 text-right">
                            <div className="text-xl font-bold text-neutral-900 dark:text-white">
                                #{entry.rank}
                            </div>
                            <div className="hidden text-xs text-neutral-500 lg:block dark:text-neutral-400">
                                Rank
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
}

function ChallengeThumbnail({
    challenge,
    transformImages,
}: {
    challenge: ChallengeEntry['challenge'];
    transformImages: boolean;
}) {
    if (
        challenge.thumbnail_url === null ||
        challenge.thumbnail_url === undefined
    ) {
        return (
            <div className="flex size-10 shrink-0 items-center justify-center rounded-md bg-amber-100 dark:bg-amber-900">
                <Trophy className="size-5 text-amber-600 dark:text-amber-400" />
            </div>
        );
    }

    const rectString = challenge.thumbnail_rect_strings?.square ?? null;

    return (
        <img
            src={
                transformImages === true
                    ? `${challenge.thumbnail_url}?w=80${rectString !== null ? `&${rectString}` : ''}`
                    : challenge.thumbnail_url
            }
            alt={challenge.title}
            className="size-10 shrink-0 rounded-md object-cover"
        />
    );
}
