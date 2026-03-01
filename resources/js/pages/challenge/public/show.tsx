import ChallengeIndexController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeIndexController';
import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import { Participants } from '@/components/challenges/participants';
import { RichTextContent } from '@/components/showcase/rich-text-content';
import { ProjectItem } from '@/components/showcase/showcase-item';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { EmptyState } from '@/components/ui/empty-state';
import PublicLayout from '@/layouts/public-layout';
import {
    getChallengeStatus,
    getStatusLabel,
    getTimeInfo,
} from '@/lib/challenge-utils';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Trophy } from 'lucide-react';

interface ChallengeShowProps {
    challenge: App.Http.Resources.Challenge.ChallengeResource;
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
    participants: App.Http.Resources.User.UserResource[];
    canSubmit: boolean;
    requiresInviteToSubmit: boolean;
}

export default function ChallengeShow({
    challenge,
    showcases,
    participants,
    canSubmit,
    requiresInviteToSubmit,
}: ChallengeShowProps) {
    const { name, appUrl, transformImages } = usePage<SharedData>().props;
    const status = getChallengeStatus(challenge.starts_at, challenge.ends_at);
    const timeInfo = getTimeInfo(challenge.starts_at, challenge.ends_at);

    const squareRect =
        challenge.thumbnail_rect_strings?.square ??
        (challenge.thumbnail_rect_strings
            ? Object.values(challenge.thumbnail_rect_strings)[0]
            : null);

    const thumbnailSrc = challenge.thumbnail_url
        ? transformImages === true
            ? `${challenge.thumbnail_url}?w=256${squareRect ? `&${squareRect}` : ''}`
            : challenge.thumbnail_url
        : null;

    const orgSquareRect =
        challenge.organisation?.thumbnail_rect_strings?.square ??
        (challenge.organisation?.thumbnail_rect_strings
            ? Object.values(challenge.organisation.thumbnail_rect_strings)[0]
            : null);

    const orgThumbnailSrc =
        challenge.organisation?.thumbnail_url && transformImages === true
            ? `${challenge.organisation.thumbnail_url}?w=256${orgSquareRect ? `&${orgSquareRect}` : ''}`
            : challenge.organisation?.thumbnail_url;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                {
                    label: 'Inspiration',
                    href: ChallengeIndexController.url(),
                },
                { label: challenge.title },
            ]}
        >
            <Head title={challenge.title}>
                <meta
                    head-key="description"
                    name="description"
                    content={challenge.tagline}
                />
                <meta head-key="og-type" property="og:type" content="article" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`${challenge.title} | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={
                        thumbnailSrc ?? `${appUrl}/static/og-text-logo.png`
                    }
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={challenge.tagline}
                />
            </Head>

            <section className="bg-white dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl border-b border-neutral-200 px-4 py-8 dark:border-neutral-800">
                    <div className="flex flex-col gap-x-12 gap-y-4 lg:flex-row">
                        <div className="min-w-0 flex-1">
                            <div className="flex flex-col gap-4">
                                <div className="flex items-center gap-2">
                                    <Badge
                                        variant="secondary"
                                        size="sm"
                                        className={cn(
                                            status === 'in_progress' &&
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                            status === 'upcoming' &&
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            status === 'ended' &&
                                                'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                                        )}
                                    >
                                        {getStatusLabel(status)}
                                    </Badge>
                                    {timeInfo && (
                                        <span className="text-sm text-neutral-500 dark:text-neutral-400">
                                            {timeInfo}
                                        </span>
                                    )}
                                </div>

                                <h1 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                                    {challenge.title}
                                </h1>
                                <p className="text-lg text-neutral-600 dark:text-neutral-400">
                                    {challenge.tagline}
                                </p>
                            </div>

                            {challenge.description_html && (
                                <div className="mt-8">
                                    <RichTextContent
                                        html={challenge.description_html}
                                        className="rich-text-content"
                                    />
                                </div>
                            )}

                            <div className="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center">
                                {canSubmit === true && (
                                    <Button asChild>
                                        <Link
                                            href={
                                                ShowcaseCreateController.url() +
                                                '?challenge=' +
                                                challenge.slug
                                            }
                                        >
                                            Submit Your Project
                                        </Link>
                                    </Button>
                                )}
                                {canSubmit === false &&
                                    requiresInviteToSubmit === true && (
                                        <p className="text-sm text-neutral-500 italic dark:text-neutral-400">
                                            Submissions to this challenge are by
                                            invitation only.
                                        </p>
                                    )}
                                {(challenge.starts_at !== null ||
                                    challenge.ends_at !== null) && (
                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                        {challenge.starts_at !== null && (
                                            <span>
                                                Opens{' '}
                                                {new Date(
                                                    challenge.starts_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric',
                                                    },
                                                )}
                                            </span>
                                        )}
                                        {challenge.starts_at !== null &&
                                            challenge.ends_at !== null && (
                                                <span className="mx-1.5">
                                                    &middot;
                                                </span>
                                            )}
                                        {challenge.ends_at !== null && (
                                            <span>
                                                Closes{' '}
                                                {new Date(
                                                    challenge.ends_at,
                                                ).toLocaleDateString(
                                                    undefined,
                                                    {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric',
                                                    },
                                                )}
                                            </span>
                                        )}
                                    </p>
                                )}
                            </div>
                        </div>

                        {(challenge.organisation ||
                            thumbnailSrc ||
                            participants.length > 0) && (
                            <aside className="shrink-0 lg:w-64 xl:w-68 2xl:w-72">
                                {challenge.organisation ? (
                                    <div>
                                        <p className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                                            Presented by
                                        </p>
                                        <Avatar className="aspect-square h-auto w-full rounded-xl">
                                            {orgThumbnailSrc ? (
                                                <AvatarImage
                                                    src={orgThumbnailSrc}
                                                    alt={
                                                        challenge.organisation
                                                            .name
                                                    }
                                                    className="rounded-xl object-cover"
                                                />
                                            ) : null}
                                            <AvatarFallback className="rounded-xl bg-neutral-100 text-4xl font-bold text-neutral-400 dark:bg-neutral-800 dark:text-neutral-600">
                                                {challenge.organisation.name.charAt(
                                                    0,
                                                )}
                                            </AvatarFallback>
                                        </Avatar>
                                        <p className="mt-3 text-sm font-semibold text-neutral-900 dark:text-white">
                                            {challenge.organisation.name}
                                        </p>
                                        {challenge.organisation.tagline && (
                                            <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                {challenge.organisation.tagline}
                                            </p>
                                        )}
                                    </div>
                                ) : thumbnailSrc ? (
                                    <div className="hidden lg:block">
                                        <img
                                            src={thumbnailSrc}
                                            alt={challenge.title}
                                            className="aspect-square w-full rounded-xl object-cover"
                                        />
                                    </div>
                                ) : null}

                                {participants.length > 0 && (
                                    <Participants
                                        participants={participants}
                                        transformImages={
                                            transformImages === true
                                        }
                                    />
                                )}
                            </aside>
                        )}
                    </div>
                </div>
            </section>

            <section className="bg-white pb-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-4xl px-4">
                    <div className="pt-8">
                        <h2 className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                            <Trophy className="size-5" />
                            Leaderboard
                        </h2>

                        {showcases.length > 0 ? (
                            <div className="mt-4 divide-y divide-neutral-100 dark:divide-neutral-800">
                                {showcases.map((showcase, index) => (
                                    <ProjectItem
                                        key={showcase.id}
                                        showcase={showcase}
                                        rank={index + 1}
                                    />
                                ))}
                            </div>
                        ) : (
                            <EmptyState
                                icon={Trophy}
                                title="Vibing in Progress"
                                description="Entries will go live soon. Be one of the first!"
                                action={
                                    canSubmit === true
                                        ? {
                                              label: 'Submit',
                                              href:
                                                  ShowcaseCreateController.url() +
                                                  '?challenge=' +
                                                  challenge.slug,
                                          }
                                        : undefined
                                }
                                className="mt-4"
                            />
                        )}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
