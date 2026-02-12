import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    ListCard,
    ListCardContent,
    ListCardEmpty,
    ListCardFooter,
    ListCardHeader,
    ListCardTitle,
} from '@/components/ui/list-card';
import { Pagination } from '@/components/ui/pagination';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { create, edit } from '@/routes/staff/challenges';
import { type PaginatedData } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';

interface ChallengesIndexProps {
    challenges: PaginatedData<App.Http.Resources.Challenge.ChallengeResource>;
}

export default function ChallengesIndex({ challenges }: ChallengesIndexProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title="Challenges" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Challenges"
                        description="Manage challenges and competitions"
                    />
                    <Button asChild>
                        <Link href={create.url()}>
                            <Plus className="mr-1.5 size-4" />
                            Create Challenge
                        </Link>
                    </Button>
                </div>

                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Challenges</ListCardTitle>
                        <Badge variant="secondary">
                            {challenges.meta.total}{' '}
                            {challenges.meta.total === 1
                                ? 'challenge'
                                : 'challenges'}
                        </Badge>
                    </ListCardHeader>

                    {challenges.data.length > 0 ? (
                        <ListCardContent>
                            {challenges.data.map((challenge) => (
                                <div
                                    key={challenge.id}
                                    className="flex items-center gap-4 py-4"
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-2">
                                            <h3 className="font-semibold text-neutral-900 dark:text-white">
                                                {challenge.title}
                                            </h3>
                                            {challenge.is_active === true && (
                                                <Badge className="bg-green-500 text-white hover:bg-green-500">
                                                    Active
                                                </Badge>
                                            )}
                                            {challenge.is_featured === true && (
                                                <Badge className="bg-amber-500 text-white hover:bg-amber-500">
                                                    Featured
                                                </Badge>
                                            )}
                                        </div>
                                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                            {challenge.tagline}
                                            {challenge.organisation !==
                                                undefined &&
                                                challenge.organisation !==
                                                    null && (
                                                    <span className="ml-2 text-neutral-400 dark:text-neutral-500">
                                                        &middot;{' '}
                                                        {
                                                            challenge
                                                                .organisation
                                                                .name
                                                        }
                                                    </span>
                                                )}
                                        </p>
                                        <p className="mt-0.5 text-xs text-neutral-400 dark:text-neutral-500">
                                            {challenge.showcases_count !==
                                                undefined && (
                                                <span>
                                                    {challenge.showcases_count}{' '}
                                                    {challenge.showcases_count ===
                                                    1
                                                        ? 'showcase'
                                                        : 'showcases'}
                                                </span>
                                            )}
                                            {challenge.starts_at !== null && (
                                                <>
                                                    <span className="mx-1.5">
                                                        &middot;
                                                    </span>
                                                    <span>
                                                        Starts{' '}
                                                        {new Date(
                                                            challenge.starts_at,
                                                        ).toLocaleDateString(
                                                            undefined,
                                                            {
                                                                year: 'numeric',
                                                                month: 'short',
                                                                day: 'numeric',
                                                            },
                                                        )}
                                                    </span>
                                                </>
                                            )}
                                            {challenge.ends_at !== null && (
                                                <>
                                                    <span className="mx-1.5">
                                                        &middot;
                                                    </span>
                                                    <span>
                                                        Ends{' '}
                                                        {new Date(
                                                            challenge.ends_at,
                                                        ).toLocaleDateString(
                                                            undefined,
                                                            {
                                                                year: 'numeric',
                                                                month: 'short',
                                                                day: 'numeric',
                                                            },
                                                        )}
                                                    </span>
                                                </>
                                            )}
                                        </p>
                                    </div>

                                    <div className="flex shrink-0 items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="gap-1.5"
                                            asChild
                                        >
                                            <Link
                                                href={edit.url({
                                                    challenge: challenge.slug,
                                                })}
                                            >
                                                <Pencil className="size-4" />
                                                Edit
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </ListCardContent>
                    ) : (
                        <ListCardEmpty>No challenges found</ListCardEmpty>
                    )}

                    <ListCardFooter>
                        <Pagination
                            meta={challenges.meta}
                            links={challenges.links}
                        />
                    </ListCardFooter>
                </ListCard>
            </div>
        </StaffAreaLayout>
    );
}
