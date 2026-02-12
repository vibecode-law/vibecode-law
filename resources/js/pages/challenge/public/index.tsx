import ChallengeIndexController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeIndexController';
import ChallengeShowController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeShowController';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
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
import { ArrowUp, Lightbulb, Users } from 'lucide-react';

interface ChallengeIndexProps {
    featuredChallenges: App.Http.Resources.Challenge.ChallengeResource[];
    activeChallenges: App.Http.Resources.Challenge.ChallengeResource[];
}

interface FeaturedChallengeCardProps {
    challenge: App.Http.Resources.Challenge.ChallengeResource;
}

function FeaturedChallengeCard({ challenge }: FeaturedChallengeCardProps) {
    const { transformImages } = usePage<SharedData>().props;
    const status = getChallengeStatus(challenge.starts_at, challenge.ends_at);

    const orgThumbnail = challenge.organisation?.thumbnail_url ?? null;

    const orgLandscapeRect =
        challenge.organisation?.thumbnail_rect_strings?.landscape ??
        (challenge.organisation?.thumbnail_rect_strings
            ? Object.values(challenge.organisation.thumbnail_rect_strings)[0]
            : null);

    const thumbnailRect =
        challenge.thumbnail_rect_strings?.landscape ??
        (challenge.thumbnail_rect_strings
            ? Object.values(challenge.thumbnail_rect_strings)[0]
            : null);

    const mainImageSrc = orgThumbnail
        ? transformImages === true
            ? `${orgThumbnail}?w=600${orgLandscapeRect ? `&${orgLandscapeRect}` : ''}`
            : orgThumbnail
        : challenge.thumbnail_url
          ? transformImages === true
              ? `${challenge.thumbnail_url}?w=600${thumbnailRect ? `&${thumbnailRect}` : ''}`
              : challenge.thumbnail_url
          : null;

    return (
        <Link
            href={ChallengeShowController.url({
                challenge: challenge.slug,
            })}
            className="group relative flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
            prefetch
        >
            {/* Thumbnail */}
            <div className="relative aspect-video overflow-hidden">
                {mainImageSrc ? (
                    <img
                        src={mainImageSrc}
                        alt={challenge.title}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-neutral-100 to-neutral-200 dark:from-neutral-800 dark:to-neutral-900">
                        <span className="text-4xl font-bold text-neutral-400 dark:text-neutral-600">
                            {challenge.title.charAt(0)}
                        </span>
                    </div>
                )}
                {/* Gradient overlay */}
                <div className="absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent" />
            </div>

            {/* Content */}
            <div className="flex flex-1 flex-col p-6">
                {/* Stats and status above title */}
                <div className="mb-2 flex items-center justify-between text-sm text-neutral-500 dark:text-neutral-400">
                    <div className="flex items-center gap-3">
                        <div className="flex items-center gap-1">
                            <Users className="size-3.5" />
                            <span>
                                {challenge.showcases_count ?? 0}{' '}
                                {challenge.showcases_count === 1
                                    ? 'entry'
                                    : 'entries'}
                            </span>
                        </div>
                        <div className="flex items-center gap-1">
                            <ArrowUp className="size-3.5" />
                            <span>
                                {challenge.total_upvotes_count ?? 0}{' '}
                                {challenge.total_upvotes_count === 1
                                    ? 'upvote'
                                    : 'upvotes'}
                            </span>
                        </div>
                    </div>
                    <Badge
                        variant="secondary"
                        size="xs"
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
                </div>
                <h3 className="text-lg font-semibold text-neutral-900 dark:text-white">
                    {challenge.title}
                </h3>
                <p className="mt-2 line-clamp-2 flex-1 text-sm text-neutral-600 dark:text-neutral-400">
                    {challenge.tagline}
                </p>
            </div>
        </Link>
    );
}

interface ActiveChallengeItemProps {
    challenge: App.Http.Resources.Challenge.ChallengeResource;
}

function ActiveChallengeItem({ challenge }: ActiveChallengeItemProps) {
    const { transformImages } = usePage<SharedData>().props;
    const status = getChallengeStatus(challenge.starts_at, challenge.ends_at);
    const timeInfo = getTimeInfo(challenge.starts_at, challenge.ends_at);

    const orgThumbnail = challenge.organisation?.thumbnail_url ?? null;

    const orgSquareRect =
        challenge.organisation?.thumbnail_rect_strings?.square ??
        (challenge.organisation?.thumbnail_rect_strings
            ? Object.values(challenge.organisation.thumbnail_rect_strings)[0]
            : null);

    const squareRect =
        challenge.thumbnail_rect_strings?.square ??
        (challenge.thumbnail_rect_strings
            ? Object.values(challenge.thumbnail_rect_strings)[0]
            : null);

    const avatarSrc = orgThumbnail
        ? transformImages === true
            ? `${orgThumbnail}?w=80${orgSquareRect ? `&${orgSquareRect}` : ''}`
            : orgThumbnail
        : challenge.thumbnail_url
          ? transformImages === true
              ? `${challenge.thumbnail_url}?w=80${squareRect ? `&${squareRect}` : ''}`
              : challenge.thumbnail_url
          : null;

    return (
        <Link
            href={ChallengeShowController.url({ challenge: challenge.slug })}
            className="flex items-start gap-4 rounded-lg px-2 py-4 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900"
            prefetch
        >
            {/* Organisation Avatar */}
            <Avatar className="size-12 shrink-0">
                {avatarSrc ? (
                    <AvatarImage
                        src={avatarSrc}
                        alt={challenge.organisation?.name ?? challenge.title}
                    />
                ) : null}
                <AvatarFallback className="bg-neutral-100 text-lg font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                    {(challenge.organisation?.name ?? challenge.title).charAt(
                        0,
                    )}
                </AvatarFallback>
            </Avatar>

            {/* Title, Organisation, and Status */}
            <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-4">
                    <div className="min-w-0 flex-1">
                        <h3 className="font-semibold text-neutral-900 dark:text-white">
                            {challenge.title}
                        </h3>
                        <p className="truncate text-sm text-neutral-500 dark:text-neutral-400">
                            {challenge.tagline}
                        </p>
                    </div>

                    {/* Stats */}
                    <div className="flex shrink-0 items-center gap-3 text-sm text-neutral-500 dark:text-neutral-400">
                        <div className="flex items-center gap-1">
                            <Users className="size-4" />
                            <span>{challenge.showcases_count ?? 0}</span>
                        </div>
                        <div className="flex items-center gap-1">
                            <ArrowUp className="size-4" />
                            <span>{challenge.total_upvotes_count ?? 0}</span>
                        </div>
                    </div>
                </div>

                {/* Organisation, Status and Time Info */}
                <div className="mt-2 flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {challenge.organisation && (
                        <>
                            <span>{challenge.organisation.name}</span>
                            <span>·</span>
                        </>
                    )}
                    <Badge
                        variant="secondary"
                        size="xs"
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
                    {timeInfo && <span>· {timeInfo}</span>}
                </div>
            </div>
        </Link>
    );
}

export default function ChallengeIndex({
    featuredChallenges,
    activeChallenges,
}: ChallengeIndexProps) {
    const { name, appUrl } = usePage<SharedData>().props;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Inspiration' },
            ]}
        >
            <Head title="Inspiration">
                <meta
                    head-key="description"
                    name="description"
                    content="Participate in community-driven legaltech challenges, sharpen your skills, and get discovered by top innovative firms."
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`Inspiration | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${ChallengeIndexController.url()}`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Participate in community-driven legaltech challenges, sharpen your skills, and get discovered by top innovative firms."
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-white py-10 lg:py-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4 text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        Inspiration.
                    </h1>
                    <p className="mx-auto mt-4 max-w-3xl text-lg text-neutral-600 dark:text-neutral-400">
                        Staring at a blank prompt window? We've got you! Here
                        are some ideas and community projects to help get you
                        moving.
                    </p>
                    <p className="mt-6 flex flex-col items-center justify-center gap-2 text-sm text-neutral-600 md:flex-row md:text-base dark:text-neutral-400">
                        <span className="flex items-center justify-start gap-2">
                            <Lightbulb className="size-5" />
                            Have an idea for a challenge?
                        </span>
                        <a
                            href="mailto:hello@vibecode.law"
                            className="font-bold underline underline-offset-4 transition-colors hover:text-neutral-900 dark:hover:text-white"
                        >
                            Email hello@vibecode.law!
                        </a>
                    </p>
                </div>
            </section>

            <div className="pb-8">
                {/* Featured Challenges */}
                {featuredChallenges.length > 0 && (
                    <section className="bg-white pb-8 dark:bg-neutral-950">
                        <div className="mx-auto max-w-6xl px-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                {featuredChallenges.map((challenge) => (
                                    <FeaturedChallengeCard
                                        key={challenge.id}
                                        challenge={challenge}
                                    />
                                ))}
                            </div>
                        </div>
                    </section>
                )}
                {/* Active Challenges */}
                {activeChallenges.length > 0 && (
                    <section className="bg-white pb-8 dark:bg-neutral-950">
                        <div className="mx-auto max-w-6xl px-4">
                            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {activeChallenges.map((challenge) => (
                                    <ActiveChallengeItem
                                        key={challenge.id}
                                        challenge={challenge}
                                    />
                                ))}
                            </div>
                        </div>
                    </section>
                )}
            </div>
        </PublicLayout>
    );
}
