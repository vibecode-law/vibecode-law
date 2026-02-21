import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import LessonShowController from '@/actions/App/Http/Controllers/Learn/LessonShowController';
import { CourseAboutSection } from '@/components/courses/course-about-section';
import { CourseCurriculum } from '@/components/courses/course-curriculum';
import { CourseLearningObjectives } from '@/components/courses/course-learning-objectives';
import { CourseProgressBar } from '@/components/courses/course-progress-bar';
import { CourseStats } from '@/components/courses/course-stats';
import { InstructorList } from '@/components/courses/instructor-list';
import { GroupedTagList } from '@/components/grouped-tag-list';
import { TagList } from '@/components/tag-list';
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
        tags?: App.Http.Resources.TagResource[];
    };
    nextLessonSlug: string | null;
    completedLessonIds: number[];
}

export default function CourseShow({
    course,
    nextLessonSlug,
    completedLessonIds,
}: CourseShowProps) {
    const totalLessons = (course.lessons ?? []).length;
    const completedLessonsCount = completedLessonIds.length;
    const { name, appUrl, transformImages } = usePage<SharedData>().props;

    const thumbnailSrc = course.thumbnail_url
        ? transformImages === true
            ? `${course.thumbnail_url}?w=600`
            : course.thumbnail_url
        : null;

    const squareRectString = course.thumbnail_rect_strings?.square ?? null;
    const sidebarThumbnailSrc = course.thumbnail_url
        ? transformImages === true
            ? `${course.thumbnail_url}?w=300${squareRectString !== null ? `&${squareRectString}` : ''}`
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
                    content={course.tagline ?? undefined}
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
                    content={course.tagline ?? undefined}
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

                            {/* Mobile: Instructors */}
                            {course.instructors &&
                                course.instructors.length > 0 && (
                                    <div className="mt-6 lg:hidden">
                                        <InstructorList
                                            instructors={course.instructors}
                                        />
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

                            {/* Mobile: Tags */}
                            {course.tags && course.tags.length > 0 && (
                                <div className="mt-8 lg:hidden">
                                    <TagList tags={course.tags} />
                                </div>
                            )}

                            {/* CTA */}
                            <div className="mt-8">
                                {nextLessonSlug &&
                                course.is_previewable === false ? (
                                    <Button asChild size="lg">
                                        <Link
                                            href={LessonShowController.url({
                                                course: course.slug,
                                                lesson: nextLessonSlug,
                                            })}
                                        >
                                            {completedLessonsCount >=
                                                totalLessons && totalLessons > 0
                                                ? 'Restart Course'
                                                : completedLessonsCount > 0
                                                  ? 'Resume Course'
                                                  : 'Start Course'}
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button size="lg" disabled>
                                        Coming Soon
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Desktop Aside */}
                        <aside className="hidden shrink-0 lg:block lg:w-64 xl:w-68 2xl:w-72">
                            {sidebarThumbnailSrc && (
                                <div className="mb-8">
                                    <img
                                        src={sidebarThumbnailSrc}
                                        alt={course.title}
                                        className="aspect-square w-full rounded-lg object-cover"
                                    />
                                </div>
                            )}

                            {course.instructors &&
                                course.instructors.length > 0 && (
                                    <div className="mb-8">
                                        <InstructorList
                                            instructors={course.instructors}
                                        />
                                    </div>
                                )}

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

                            {course.tags && course.tags.length > 0 && (
                                <GroupedTagList tags={course.tags} />
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
                        isComingSoon={course.is_previewable}
                    />
                </div>
            </section>
        </PublicLayout>
    );
}
