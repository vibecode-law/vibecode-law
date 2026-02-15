import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import LessonShowController from '@/actions/App/Http/Controllers/Learn/LessonShowController';
import { CourseAboutSection } from '@/components/course/course-about-section';
import { CourseLearningObjectives } from '@/components/course/course-learning-objectives';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { BookOpen, Check, Clock, Users } from 'lucide-react';

interface CourseShowProps {
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
        tags?: App.Http.Resources.Course.CourseTagResource[];
    };
    nextLessonSlug: string | null;
    completedLessonIds: number[];
    totalLessons: number;
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

function getExperienceLevelColor(level: number): string {
    switch (level) {
        case 1:
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200';
        case 2:
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 3:
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200';
        case 4:
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
        default:
            return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-200';
    }
}

export default function CourseShow({
    course,
    nextLessonSlug,
    completedLessonIds,
    totalLessons,
}: CourseShowProps) {
    const completedLessonsCount = completedLessonIds.length;
    const { name, appUrl, transformImages } = usePage<SharedData>().props;

    const authorAvatarSrc =
        course.user?.avatar !== null && course.user?.avatar !== undefined
            ? transformImages === true
                ? `${course.user.avatar}?w=256`
                : course.user.avatar
            : undefined;

    const thumbnailSrc = course.thumbnail_url
        ? transformImages === true
            ? `${course.thumbnail_url}?w=600`
            : course.thumbnail_url
        : null;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn', href: LearnIndexController.url() },
                { label: course.title },
            ]}
        >
            <Head title={course.title}>
                <meta
                    head-key="description"
                    name="description"
                    content={course.tagline}
                />
                <meta head-key="og-type" property="og:type" content="article" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`${course.title} | ${name}`}
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
                    content={course.tagline}
                />
            </Head>

            {/* Header + Description + Aside */}
            <section className="bg-white dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl border-b border-neutral-200 px-4 py-8 dark:border-neutral-800">
                    <div className="flex flex-col gap-x-12 gap-y-8 lg:flex-row">
                        {/* Main content */}
                        <div className="min-w-0 flex-1">
                            <div className="flex flex-col gap-4">
                                {/* Experience level badge */}
                                {course.experience_level && (
                                    <div className="flex items-center gap-2">
                                        <Badge
                                            variant="secondary"
                                            size="sm"
                                            className={cn(
                                                getExperienceLevelColor(
                                                    Number(
                                                        course.experience_level
                                                            .value,
                                                    ),
                                                ),
                                            )}
                                        >
                                            {course.experience_level.label}
                                        </Badge>
                                    </div>
                                )}

                                {/* Title and tagline */}
                                <h1 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                                    {course.title}
                                </h1>
                                <p className="text-lg text-neutral-600 dark:text-neutral-400">
                                    {course.tagline}
                                </p>

                                {/* Stats */}
                                <div className="flex items-center gap-4 text-sm text-neutral-500 dark:text-neutral-400">
                                    <div className="flex items-center gap-1.5">
                                        <Users className="size-4" />
                                        <span>
                                            {course.started_count ?? 0} already
                                            enrolled
                                        </span>
                                    </div>
                                    {formatDuration(
                                        course.duration_seconds,
                                    ) && (
                                        <div className="flex items-center gap-1.5">
                                            <Clock className="size-4" />
                                            <span>
                                                {formatDuration(
                                                    course.duration_seconds,
                                                )}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Mobile: Progress Bar - shown after stats */}
                            {completedLessonsCount > 0 && (
                                <div className="mt-6 rounded-lg border border-neutral-200 bg-white p-4 lg:hidden dark:border-neutral-800 dark:bg-neutral-900">
                                    <div className="mb-2 flex items-center justify-between text-sm">
                                        <span className="font-medium text-neutral-900 dark:text-white">
                                            Your Progress
                                        </span>
                                        <span className="text-neutral-600 dark:text-neutral-400">
                                            {completedLessonsCount} of{' '}
                                            {totalLessons}
                                        </span>
                                    </div>
                                    <div className="h-2 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                                        <div
                                            className="h-full bg-linear-to-r from-blue-500 to-blue-600 transition-all duration-300"
                                            style={{
                                                width: `${(completedLessonsCount / totalLessons) * 100}%`,
                                            }}
                                        />
                                    </div>
                                    {completedLessonsCount === totalLessons && (
                                        <p className="mt-2 text-xs font-medium text-green-600 dark:text-green-400">
                                            ✨ Course completed!
                                        </p>
                                    )}
                                </div>
                            )}

                            {/* Mobile: Instructor - shown after progress */}
                            {course.user && (
                                <div className="mt-6 lg:hidden">
                                    <p className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                                        Instructor
                                    </p>
                                    <div className="flex items-start gap-3">
                                        <Avatar className="size-12">
                                            {authorAvatarSrc ? (
                                                <AvatarImage
                                                    src={authorAvatarSrc}
                                                    alt={`${course.user.first_name} ${course.user.last_name}`}
                                                />
                                            ) : null}
                                            <AvatarFallback className="bg-neutral-100 text-lg font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                                {course.user.first_name.charAt(
                                                    0,
                                                )}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-neutral-900 dark:text-white">
                                                {course.user.first_name}{' '}
                                                {course.user.last_name}
                                            </p>
                                            {course.user.job_title && (
                                                <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                                    {course.user.job_title}
                                                </p>
                                            )}
                                            {course.user.organisation && (
                                                <p className="text-sm text-neutral-500 dark:text-neutral-500">
                                                    {course.user.organisation}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Description */}
                            {course.description_html && (
                                <CourseAboutSection
                                    html={course.description_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {/* Learning Objectives */}
                            {course.learning_objectives_html && (
                                <CourseLearningObjectives
                                    html={course.learning_objectives_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {/* Skills Tags */}
                            {course.tags && course.tags.length > 0 && (
                                <div className="mt-8">
                                    <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                                        Skills You'll Gain
                                    </h2>
                                    <div className="mt-4 flex flex-wrap gap-2">
                                        {course.tags.map((tag) => (
                                            <Badge
                                                key={tag.id}
                                                variant="secondary"
                                                size="sm"
                                            >
                                                {tag.name}
                                            </Badge>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* CTA */}
                            <div className="mt-8">
                                <Button asChild size="lg">
                                    <Link
                                        href={
                                            nextLessonSlug
                                                ? LessonShowController.url({
                                                      course: course.slug,
                                                      lesson: nextLessonSlug,
                                                  })
                                                : '#'
                                        }
                                    >
                                        {completedLessonsCount > 0
                                            ? 'Resume Course'
                                            : 'Start Course'}
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        {/* Desktop Aside - hidden on mobile */}
                        <aside className="hidden shrink-0 lg:block lg:w-64 xl:w-68 2xl:w-72">
                            {/* Progress Bar */}
                            {completedLessonsCount > 0 && (
                                <div className="mb-8 rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                                    <div className="mb-2 flex items-center justify-between text-sm">
                                        <span className="font-medium text-neutral-900 dark:text-white">
                                            Your Progress
                                        </span>
                                        <span className="text-neutral-600 dark:text-neutral-400">
                                            {completedLessonsCount} of{' '}
                                            {totalLessons}
                                        </span>
                                    </div>
                                    <div className="h-2 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                                        <div
                                            className="h-full bg-linear-to-r from-blue-500 to-blue-600 transition-all duration-300"
                                            style={{
                                                width: `${(completedLessonsCount / totalLessons) * 100}%`,
                                            }}
                                        />
                                    </div>
                                    {completedLessonsCount === totalLessons && (
                                        <p className="mt-2 text-xs font-medium text-green-600 dark:text-green-400">
                                            ✨ Course completed!
                                        </p>
                                    )}
                                </div>
                            )}

                            {/* Course author */}
                            {course.user && (
                                <div>
                                    <p className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                                        Instructor
                                    </p>
                                    <div className="flex items-start gap-3">
                                        <Avatar className="size-12">
                                            {authorAvatarSrc ? (
                                                <AvatarImage
                                                    src={authorAvatarSrc}
                                                    alt={`${course.user.first_name} ${course.user.last_name}`}
                                                />
                                            ) : null}
                                            <AvatarFallback className="bg-neutral-100 text-lg font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                                {course.user.first_name.charAt(
                                                    0,
                                                )}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div className="min-w-0 flex-1">
                                            <p className="font-semibold text-neutral-900 dark:text-white">
                                                {course.user.first_name}{' '}
                                                {course.user.last_name}
                                            </p>
                                            {course.user.job_title && (
                                                <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                                    {course.user.job_title}
                                                </p>
                                            )}
                                            {course.user.organisation && (
                                                <p className="text-sm text-neutral-500 dark:text-neutral-500">
                                                    {course.user.organisation}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Course thumbnail - hidden on mobile */}
                            {thumbnailSrc && (
                                <div className="mt-8">
                                    <img
                                        src={thumbnailSrc}
                                        alt={course.title}
                                        className="aspect-video w-full rounded-lg object-cover"
                                    />
                                </div>
                            )}
                        </aside>
                    </div>
                </div>
            </section>

            {/* Course Curriculum Section */}
            <section className="bg-white pb-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-4xl px-4">
                    <div className="pt-8">
                        <h2 className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                            <BookOpen className="size-5" />
                            Course Curriculum
                        </h2>

                        {course.lessons && course.lessons.length > 0 ? (
                            <div className="mt-6 divide-y divide-neutral-100 dark:divide-neutral-800">
                                {course.lessons.map((lesson, index) => {
                                    const isComplete =
                                        completedLessonIds.includes(lesson.id);

                                    return (
                                        <Link
                                            key={lesson.id}
                                            href={LessonShowController.url({
                                                course: course.slug,
                                                lesson: lesson.slug,
                                            })}
                                            className="flex items-start gap-4 rounded-lg px-4 py-4 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900"
                                        >
                                            <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-neutral-100 text-sm font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                                {index + 1}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-start justify-between gap-4">
                                                    <div className="min-w-0 flex-1">
                                                        <h3 className="font-semibold text-neutral-900 dark:text-white">
                                                            {lesson.title}
                                                        </h3>
                                                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                            {lesson.tagline}
                                                        </p>
                                                    </div>
                                                    {isComplete && (
                                                        <Check className="size-5 shrink-0 text-green-600 dark:text-green-400" />
                                                    )}
                                                </div>
                                            </div>
                                        </Link>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="mt-6 rounded-lg border border-neutral-200 bg-neutral-50 p-8 text-center dark:border-neutral-800 dark:bg-neutral-900">
                                <BookOpen className="mx-auto size-12 text-neutral-400" />
                                <p className="mt-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    Lessons coming soon
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
