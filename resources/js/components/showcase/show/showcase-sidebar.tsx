import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { Share2 } from 'lucide-react';
import { UpvoteButton } from '../upvote-button';
import { ShowcaseChallengeEntries } from './showcase-challenge-entries';

interface ChallengeEntry {
    challenge: Pick<
        App.Http.Resources.Challenge.ChallengeResource,
        'id' | 'slug' | 'title' | 'thumbnail_url' | 'thumbnail_rect_strings'
    >;
    rank: number;
}

interface ShowcaseSidebarProps {
    monthlyRank: number | null;
    lifetimeRank: number | null;
    hasUpvoted: boolean;
    upvotesCount: number;
    showcaseSlug: string;
    linkedinShareUrl: string;
    challengeEntries?: ChallengeEntry[];
}

export function ShowcaseSidebar({
    monthlyRank,
    lifetimeRank,
    hasUpvoted,
    upvotesCount,
    showcaseSlug,
    linkedinShareUrl,
    challengeEntries,
}: ShowcaseSidebarProps) {
    return (
        <div className="w-full lg:w-72 xl:w-78 2xl:w-84">
            <div className="lg:sticky lg:top-4">
                <Card className="py-4 lg:py-6">
                    <CardContent className="flex flex-row items-center justify-between gap-4 lg:flex-col">
                        <div className="grid w-full gap-4 lg:grid-cols-2 lg:py-2">
                            <RankDisplay
                                rank={monthlyRank}
                                label="Monthly Rank"
                                className={
                                    monthlyRank !== null ? 'flex-1' : 'w-full'
                                }
                            />
                            <RankDisplay
                                rank={lifetimeRank}
                                label="Lifetime Rank"
                                className={
                                    lifetimeRank !== null ? 'flex-1' : 'w-full'
                                }
                            />
                        </div>

                        <div className="flex w-full flex-col gap-4">
                            <UpvoteButton
                                showcaseSlug={showcaseSlug}
                                upvotesCount={upvotesCount}
                                hasUpvoted={hasUpvoted}
                                variant="full"
                            />
                            <Button
                                variant="outline"
                                className="w-full"
                                asChild
                            >
                                <a href={linkedinShareUrl} target="_blank">
                                    <Share2 className="size-4" />
                                    Share
                                </a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {challengeEntries !== undefined &&
                    challengeEntries.length > 0 && (
                        <ShowcaseChallengeEntries
                            className="mt-8 hidden lg:block"
                            challengeEntries={challengeEntries}
                        />
                    )}
            </div>
        </div>
    );
}

function RankDisplay({
    rank,
    label,
    className,
}: {
    rank: number | null;
    label: string;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex items-center gap-4 text-center lg:flex-col lg:gap-0',
                className,
            )}
        >
            <div className="text-xl font-bold text-neutral-900 lg:text-3xl dark:text-white">
                #{rank}
            </div>
            <div className="text-xs text-neutral-500 dark:text-neutral-400">
                {label}
            </div>
        </div>
    );
}
