import ChallengeShowController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeShowController';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Trophy } from 'lucide-react';

interface SidebarActiveChallengesProps {
    challenges: App.Http.Resources.Challenge.ChallengeResource[];
}

export function SidebarActiveChallenges({
    challenges,
}: SidebarActiveChallengesProps) {
    const { transformImages } = usePage<SharedData>().props;

    if (challenges.length === 0) {
        return null;
    }

    return (
        <div>
            <h3 className="mb-2 text-sm font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                Inspiration
            </h3>
            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                {challenges.map((challenge) => (
                    <Link
                        key={challenge.id}
                        href={ChallengeShowController.url({
                            challenge: challenge.slug,
                        })}
                        className="flex items-center gap-4 py-4 transition-transform duration-200 ease-out hover:scale-[1.01]"
                    >
                        <ChallengeThumbnail
                            challenge={challenge}
                            transformImages={transformImages}
                        />
                        <div className="min-w-0 flex-1">
                            <div className="truncate font-medium text-neutral-900 dark:text-white">
                                {challenge.title}
                            </div>
                            <div className="mt-0.5 flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                                <Trophy className="hidden size-3 lg:block" />
                                <span>Challenge</span>
                                {challenge.showcases_count !== null &&
                                    challenge.showcases_count !== undefined && (
                                        <span>
                                            &middot; {challenge.showcases_count}{' '}
                                            {challenge.showcases_count === 1
                                                ? 'entry'
                                                : 'entries'}
                                        </span>
                                    )}
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
    challenge: App.Http.Resources.Challenge.ChallengeResource;
    transformImages: boolean;
}) {
    if (
        challenge.thumbnail_url === null ||
        challenge.thumbnail_url === undefined
    ) {
        return (
            <div className="flex size-14 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900">
                <Trophy className="size-6 text-amber-600 dark:text-amber-400" />
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
            className="size-14 shrink-0 rounded-lg object-cover"
        />
    );
}
