import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import LessonShowController from '@/actions/App/Http/Controllers/Learn/LessonShowController';
import { CourseAboutSection } from '@/components/course/course-about-section';
import { CourseCurriculum } from '@/components/course/course-curriculum';
import { CourseInstructor } from '@/components/course/course-instructor';
import { CourseLearningObjectives } from '@/components/course/course-learning-objectives';
import { CourseProgressBar } from '@/components/course/course-progress-bar';
import { CourseSkillsTags } from '@/components/course/course-skills-tags';
import { CourseStats } from '@/components/course/course-stats';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { getExperienceLevelColor } from '@/lib/course-utils';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

interface CourseShowProps {
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
        tags?: App.Http.Resources.Course.CourseTagResource[];
    };
    nextLessonSlug: string | null;
    completedLessonIds: number[];
    totalLessons: number;
}

export default function CourseShow({
    course,
    nextLessonSlug,
    completedLessonIds,
    totalLessons,
}: CourseShowProps) {
    const completedLessonsCount = completedLessonIds.length;
    const { name, appUrl, transformImages } = usePage<SharedData>().props;

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

                                <h1 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                                    {course.title}
                                </h1>
                                <p className="text-lg text-neutral-600 dark:text-neutral-400">
                                    {course.tagline}
                                </p>

                                <CourseStats course={course} />
                            </div>

                            {/* Mobile: Progress Bar */}
                            {completedLessonsCount > 0 && (
                                <div className="mt-6 lg:hidden">
                                    <CourseProgressBar
                                        progressPercentage={Math.round(
                                            (completedLessonsCount /
                                                totalLessons) *
                                                100,
                                        )}
                                    />
                                </div>
                            )}

                            {/* Mobile: Instructor */}
                            {course.user && (
                                <div className="mt-6 lg:hidden">
                                    <CourseInstructor user={course.user} />
                                </div>
                            )}

                            {course.description_html && (
                                <CourseAboutSection
                                    html={course.description_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {course.learning_objectives_html && (
                                <CourseLearningObjectives
                                    html={course.learning_objectives_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {course.tags && course.tags.length > 0 && (
                                <CourseSkillsTags tags={course.tags} />
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

                        {/* Desktop Aside */}
                        <aside className="hidden shrink-0 lg:block lg:w-64 xl:w-68 2xl:w-72">
                            {completedLessonsCount > 0 && (
                                <div className="mb-8">
                                    <p className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                                        Progress
                                    </p>
                                    <CourseProgressBar
                                        progressPercentage={Math.round(
                                            (completedLessonsCount /
                                                totalLessons) *
                                                100,
                                        )}
                                    />
                                </div>
                            )}

                            {course.user && (
                                <div className="mb-8">
                                    <CourseInstructor user={course.user} />
                                </div>
                            )}

                            {thumbnailSrc && (
                                <div className="mb-8">
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
                    <CourseCurriculum
                        courseSlug={course.slug}
                        lessons={course.lessons ?? []}
                        completedLessonIds={completedLessonIds}
                    />
                </div>
            </section>
        </PublicLayout>
    );
}
