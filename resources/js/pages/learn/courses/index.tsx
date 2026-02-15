import { TabNav } from '@/components/navigation/tab-nav';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowRight,
    BookOpen,
    Clock,
    Lightbulb,
    Play,
    Users,
    type LucideIcon,
} from 'lucide-react';
import { useState } from 'react';

interface ResourceChild {
    name: string;
    slug: string;
    summary: string;
    icon: string;
    route: string;
}

interface CourseIndexProps {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    courses?: any[];
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    featuredCourses?: any[];
    courseProgress?: Record<
        number,
        {
            isEnrolled: boolean;
            progressPercentage: number;
            isComplete: boolean;
        }
    >;
    guides?: ResourceChild[];
    totalEnrolledUsers?: number;
}

const iconMap: Record<string, LucideIcon> = {
    lightbulb: Lightbulb,
    play: Play,
    'alert-triangle': AlertTriangle,
};

const colorMap: Record<
    string,
    { bg: string; icon: string; hover: string; border: string }
> = {
    lightbulb: {
        bg: 'bg-linear-to-br from-yellow-50 to-amber-50 dark:from-yellow-950/30 dark:to-amber-950/30',
        icon: 'text-yellow-600 dark:text-yellow-400',
        hover: 'group-hover:from-yellow-100 group-hover:to-amber-100 dark:group-hover:from-yellow-950/50 dark:group-hover:to-amber-950/50',
        border: 'border-yellow-200 dark:border-yellow-800/50',
    },
    play: {
        bg: 'bg-linear-to-br from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/30',
        icon: 'text-emerald-600 dark:text-emerald-400',
        hover: 'group-hover:from-emerald-100 group-hover:to-green-100 dark:group-hover:from-emerald-950/50 dark:group-hover:to-green-950/50',
        border: 'border-emerald-200 dark:border-emerald-800/50',
    },
    'alert-triangle': {
        bg: 'bg-linear-to-br from-red-50 to-orange-50 dark:from-red-950/30 dark:to-orange-950/30',
        icon: 'text-red-600 dark:text-red-400',
        hover: 'group-hover:from-red-100 group-hover:to-orange-100 dark:group-hover:from-red-950/50 dark:group-hover:to-orange-950/50',
        border: 'border-red-200 dark:border-red-800/50',
    },
    scale: {
        bg: 'bg-linear-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/30',
        icon: 'text-violet-600 dark:text-violet-400',
        hover: 'group-hover:from-violet-100 group-hover:to-purple-100 dark:group-hover:from-violet-950/50 dark:group-hover:to-purple-950/50',
        border: 'border-violet-200 dark:border-violet-800/50',
    },
};

// Mock data for development - will be replaced with real backend data
// Using 'any' type because backend types are still being developed
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const mockCourses: any[] = [
    {
        id: 1,
        slug: 'foundations',
        title: 'VibeCoding Foundations',
        tagline: 'Master the basics of AI-assisted legal tech development',
        description:
            'Learn the fundamentals of building legal tech with AI coding assistants.',
        experience_level: { value: 1, label: 'Beginner' },
        started_count: 1247,
        duration_seconds: 2700, // 45 minutes
        thumbnail_url: null,
        visible: true,
        is_featured: true,
        order: 1,
        user: {
            first_name: 'Sarah',
            avatar: null,
            handle: 'sarah',
        },
    },
    {
        id: 2,
        slug: 'intermediate-patterns',
        title: 'Intermediate VibeCoding Patterns',
        tagline: 'Advanced techniques for building complex legal applications',
        description:
            'Take your skills to the next level with advanced patterns and best practices.',
        experience_level: { value: 2, label: 'Intermediate' },
        started_count: 634,
        duration_seconds: 5400, // 90 minutes
        thumbnail_url: null,
        visible: true,
        is_featured: true,
        order: 2,
        user: {
            first_name: 'Marcus',
            avatar: null,
            handle: 'marcus',
        },
    },
    {
        id: 3,
        slug: 'advanced-architecture',
        title: 'Advanced Architecture & Design',
        tagline: 'Design scalable, maintainable legal tech systems',
        description:
            'Master architectural patterns for enterprise-grade legal applications.',
        experience_level: { value: 3, label: 'Advanced' },
        started_count: 289,
        duration_seconds: 7200, // 2 hours
        thumbnail_url: null,
        visible: true,
        is_featured: false,
        order: 3,
        user: {
            first_name: 'Elena',
            avatar: null,
            handle: 'elena',
        },
    },
    {
        id: 4,
        slug: 'professional-practices',
        title: 'Professional VibeCoding Practices',
        tagline: 'Production-ready workflows for legal tech professionals',
        description:
            'Learn industry best practices for deploying and maintaining legal tech at scale.',
        experience_level: { value: 4, label: 'Professional' },
        started_count: 156,
        duration_seconds: 10800, // 3 hours
        thumbnail_url: null,
        visible: true,
        is_featured: false,
        order: 4,
        user: {
            first_name: 'David',
            avatar: null,
            handle: 'david',
        },
    },
];

// Colors match the confetti palette from utils.ts
// Defined as a static object so Tailwind's JIT compiler picks them up
const experienceLevelColors: Record<number, string> = {
    1: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
    2: 'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300',
    3: 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
    4: 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300',
};

function getExperienceLevelColor(level: number): string {
    return (
        experienceLevelColors[level] ??
        'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-200'
    );
}

function formatDuration(seconds: number | null | undefined): string | null {
    if (!seconds || seconds <= 0) {
        return null;
    }

    const HOUR_IN_SECONDS = 3600;

    if (seconds < HOUR_IN_SECONDS) {
        // Convert to minutes and round up to nearest 5
        const minutes = Math.ceil(seconds / 60);
        const roundedMinutes = Math.ceil(minutes / 5) * 5;
        return `${roundedMinutes} min`;
    } else {
        // Convert to hours and round up
        const hours = Math.ceil(seconds / HOUR_IN_SECONDS);
        return `${hours} ${hours === 1 ? 'hr' : 'hrs'}`;
    }
}

interface FeaturedCourseCardProps {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    course: any;
    progress?: {
        isEnrolled: boolean;
        progressPercentage: number;
        isComplete: boolean;
    };
}

function FeaturedCourseCard({ course, progress }: FeaturedCourseCardProps) {
    const { transformImages } = usePage<SharedData>().props;

    const thumbnailSrc = course.thumbnail_url
        ? transformImages === true
            ? `${course.thumbnail_url}?w=600`
            : course.thumbnail_url
        : null;

    return (
        <Link
            href={`/learn/courses/${course.slug}`}
            className="group relative flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
            {/* Thumbnail */}
            <div className="relative aspect-video overflow-hidden">
                {thumbnailSrc ? (
                    <img
                        src={thumbnailSrc}
                        alt={course.title}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-neutral-100 to-neutral-200 dark:from-neutral-800 dark:to-neutral-900">
                        <BookOpen className="size-16 text-neutral-400 dark:text-neutral-600" />
                    </div>
                )}
                {/* Gradient overlay */}
                <div className="absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent" />
            </div>

            {/* Content */}
            <div className="flex flex-1 flex-col p-6">
                {/* Stats and experience level above title */}
                <div className="mb-2 flex items-center justify-between text-sm text-neutral-500 dark:text-neutral-400">
                    <div className="flex items-center gap-3">
                        <div className="flex items-center gap-1">
                            <Users className="size-3.5" />
                            <span>
                                {course.started_count ?? 0} already enrolled
                            </span>
                        </div>
                        {formatDuration(course.duration_seconds) && (
                            <div className="flex items-center gap-1">
                                <Clock className="size-3.5" />
                                <span>
                                    {formatDuration(course.duration_seconds)}
                                </span>
                            </div>
                        )}
                    </div>
                    {course.experience_level && (
                        <Badge
                            size="xs"
                            className={getExperienceLevelColor(
                                course.experience_level.value,
                            )}
                        >
                            {course.experience_level.label}
                        </Badge>
                    )}
                </div>
                <h3 className="text-lg font-semibold text-neutral-900 dark:text-white">
                    {course.title}
                </h3>
                <p className="mt-2 line-clamp-2 flex-1 text-sm text-neutral-600 dark:text-neutral-400">
                    {course.tagline}
                </p>
                {/* Progress Bar (if enrolled) */}
                {progress?.isEnrolled && (
                    <div className="mt-4 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                        <div className="mb-2 flex items-center justify-between text-xs">
                            <span className="font-medium text-neutral-700 dark:text-neutral-300">
                                {progress.isComplete
                                    ? 'Completed'
                                    : 'In Progress'}
                            </span>
                            <span className="text-neutral-500 dark:text-neutral-400">
                                {progress.progressPercentage}%
                            </span>
                        </div>
                        <div className="h-1.5 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-800">
                            <div
                                className={cn(
                                    'h-full rounded-full transition-all',
                                    progress.isComplete
                                        ? 'bg-green-600 dark:bg-green-500'
                                        : 'bg-blue-600 dark:bg-blue-500',
                                )}
                                style={{
                                    width: `${progress.progressPercentage}%`,
                                }}
                            />
                        </div>
                    </div>
                )}

                {/* Author */}
                {course.user && (
                    <div
                        className={cn(
                            'mt-4 flex items-center gap-2 pt-4',
                            !progress?.isEnrolled &&
                                'border-t border-neutral-100 dark:border-neutral-800',
                        )}
                    >
                        <Avatar className="size-6">
                            {course.user.avatar ? (
                                <AvatarImage
                                    src={
                                        transformImages === true
                                            ? `${course.user.avatar}?w=48`
                                            : course.user.avatar
                                    }
                                    alt={course.user.first_name}
                                />
                            ) : null}
                            <AvatarFallback className="bg-neutral-100 text-xs font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                {course.user.first_name.charAt(0)}
                            </AvatarFallback>
                        </Avatar>
                        <span className="text-sm text-neutral-600 dark:text-neutral-400">
                            {course.user.first_name}
                        </span>
                    </div>
                )}
            </div>
        </Link>
    );
}

export default function CourseIndex({
    featuredCourses: propFeaturedCourses,
    courses: propCourses,
    courseProgress = {},
    guides = [],
    totalEnrolledUsers = 0,
}: CourseIndexProps) {
    const { name, appUrl } = usePage<SharedData>().props;
    const [activeTab, setActiveTab] = useState<'courses' | 'guides'>('courses');

    // Use mock data if no props provided (for development)
    const allCourses = propCourses ?? mockCourses;
    const featuredCourses =
        propFeaturedCourses ?? allCourses.filter((c) => c.is_featured === true);

    // Show all non-featured courses
    const displayedCourses = allCourses.filter((c) => c.is_featured !== true);

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn' },
            ]}
        >
            <Head title="Learn">
                <meta
                    head-key="description"
                    name="description"
                    content="Master vibecoding with structured courses from beginner to professional. Build legal tech faster with AI-assisted development."
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`Learn | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}/learn`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Master vibecoding with structured courses from beginner to professional. Build legal tech faster with AI-assisted development."
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-white py-10 lg:py-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4 text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        VibeAcademy
                    </h1>
                    <p className="mx-auto mt-6 max-w-3xl text-lg text-neutral-600 dark:text-neutral-400">
                        Master the art of building with AI coding assistants.
                        Start with the foundations and progress to master skills
                        through structured, hands-on courses.
                    </p>
                    <p className="mt-8 flex flex-col items-center justify-center gap-2 text-sm text-neutral-600 md:flex-row md:text-base dark:text-neutral-400">
                        <span className="flex items-center justify-start gap-2">
                            <Users className="size-5" />
                            Join {totalEnrolledUsers.toLocaleString()} others
                            who are learning to build.
                        </span>
                    </p>
                </div>
            </section>

            {/* Tab Navigation */}
            <section className="border-b border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <TabNav
                        items={[
                            {
                                title: 'Courses',
                                onClick: () => setActiveTab('courses'),
                                isActive: activeTab === 'courses',
                            },
                            {
                                title: 'Guides',
                                onClick: () => setActiveTab('guides'),
                                isActive: activeTab === 'guides',
                            },
                        ]}
                        ariaLabel="Learn navigation"
                    />
                </div>
            </section>

            {/* Courses Content */}
            {activeTab === 'courses' && (
                <section className="bg-white pt-8 pb-8 dark:bg-neutral-950">
                    <div className="mx-auto max-w-6xl px-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            {featuredCourses.map((course) => (
                                <FeaturedCourseCard
                                    key={course.id}
                                    course={course}
                                    progress={courseProgress[course.id]}
                                />
                            ))}
                            {displayedCourses.map((course) => (
                                <FeaturedCourseCard
                                    key={course.id}
                                    course={course}
                                    progress={courseProgress[course.id]}
                                />
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Guides Content */}
            {activeTab === 'guides' && (
                <section className="bg-white pt-8 pb-8 dark:bg-neutral-950">
                    <div className="mx-auto max-w-6xl px-4">
                        {guides.length > 0 ? (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {guides.map((child) => {
                                    const Icon =
                                        iconMap[child.icon] || Lightbulb;
                                    const colors =
                                        colorMap[child.icon] ||
                                        colorMap.lightbulb;

                                    return (
                                        <Link
                                            key={child.slug}
                                            href={child.route}
                                            className={`group relative flex items-start gap-4 rounded-xl border p-6 transition-all duration-200 ${colors.border} bg-white hover:shadow-md dark:bg-neutral-900 dark:hover:bg-neutral-800/50`}
                                        >
                                            <div
                                                className={`flex size-12 shrink-0 items-center justify-center rounded-lg transition-all duration-200 ${colors.bg} ${colors.hover}`}
                                            >
                                                <Icon
                                                    className={`size-6 ${colors.icon}`}
                                                />
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <h3 className="flex items-center gap-2 font-semibold text-neutral-900 dark:text-neutral-100">
                                                    {child.name}
                                                    <ArrowRight className="size-4 opacity-0 transition-all duration-200 group-hover:translate-x-1 group-hover:opacity-100" />
                                                </h3>
                                                <p className="mt-1 text-sm leading-relaxed text-neutral-600 dark:text-neutral-400">
                                                    {child.summary}
                                                </p>
                                            </div>
                                        </Link>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="rounded-lg border border-neutral-200 bg-neutral-50 p-8 text-center dark:border-neutral-800 dark:bg-neutral-900">
                                <Lightbulb className="mx-auto size-12 text-neutral-400" />
                                <p className="mt-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    Guides coming soon
                                </p>
                            </div>
                        )}
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
