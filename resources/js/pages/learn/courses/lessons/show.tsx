import CourseShowController from '@/actions/App/Http/Controllers/Learn/CourseShowController';
import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import { LessonContent } from '@/components/course/lesson-content';
import { LessonNavigation } from '@/components/course/lesson-navigation';
import { LessonSidebarOutline } from '@/components/course/lesson-sidebar-outline';
import { LessonTranscript } from '@/components/course/lesson-transcript';
import { LessonVideoPlayer } from '@/components/course/lesson-video-player';
import { LessonWhatWeCover } from '@/components/course/lesson-what-we-cover';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { Head } from '@inertiajs/react';

interface LessonShowProps {
    lesson: App.Http.Resources.Course.LessonResource;
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
    };
    previousLesson: { slug: string; title: string } | null;
    nextLesson: { slug: string; title: string } | null;
    completedLessonIds: number[];
}

export default function LessonShow({
    lesson,
    course,
    previousLesson,
    nextLesson,
    completedLessonIds,
}: LessonShowProps) {
    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn', href: LearnIndexController.url() },
                {
                    label: course.title,
                    href: CourseShowController.url({
                        course: course.slug,
                    }),
                },
                { label: lesson.title },
            ]}
        >
            <Head title={`${lesson.title} - ${course.title}`} />

            <section className="bg-white dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4 py-8">
                    <div className="flex flex-col gap-8 lg:flex-row">
                        {/* Main Content */}
                        <div className="flex-1">
                            <div className="mb-8">
                                <h1 className="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
                                    {lesson.title}
                                </h1>
                                {lesson.tagline && (
                                    <p className="mt-2 text-lg text-neutral-600 dark:text-neutral-400">
                                        {lesson.tagline}
                                    </p>
                                )}
                            </div>

                            <LessonVideoPlayer />

                            {lesson.learning_objectives_html && (
                                <LessonWhatWeCover
                                    html={lesson.learning_objectives_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {lesson.copy_html && (
                                <LessonContent
                                    html={lesson.copy_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {lesson.transcript && (
                                <div className="lg:hidden">
                                    <LessonTranscript
                                        transcript={lesson.transcript}
                                        truncateOnMobile={true}
                                    />
                                </div>
                            )}

                            <LessonNavigation
                                courseSlug={course.slug}
                                previousLesson={previousLesson}
                                nextLesson={nextLesson}
                            />
                        </div>

                        {/* Desktop Sidebar */}
                        <aside className="hidden lg:block lg:w-80">
                            <div className="sticky top-4 space-y-6">
                                <LessonSidebarOutline
                                    courseSlug={course.slug}
                                    lessons={course.lessons ?? []}
                                    currentLessonId={lesson.id}
                                    completedLessonIds={completedLessonIds}
                                />

                                {lesson.transcript && (
                                    <div className="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                                        <h3 className="mb-4 font-semibold text-neutral-900 dark:text-white">
                                            Transcript
                                        </h3>
                                        <div className="max-h-96 overflow-y-auto">
                                            <div className="prose-sm prose dark:prose-invert text-neutral-600 dark:text-neutral-400">
                                                {lesson.transcript}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
