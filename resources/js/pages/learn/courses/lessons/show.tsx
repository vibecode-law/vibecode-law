import CourseIndexController from '@/actions/App/Http/Controllers/Course/Public/CourseIndexController';
import CourseShowController from '@/actions/App/Http/Controllers/Course/Public/CourseShowController';
import LessonShowController from '@/actions/App/Http/Controllers/Course/Public/LessonShowController';
import { LessonContent } from '@/components/course/lesson-content';
import { LessonTranscript } from '@/components/course/lesson-transcript';
import { LessonWhatWeCover } from '@/components/course/lesson-what-we-cover';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Check, Play } from 'lucide-react';

interface LessonShowProps {
    lesson: App.Http.Resources.Course.LessonResource;
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
    };
    previousLesson: { slug: string; title: string } | null;
    nextLesson: { slug: string; title: string } | null;
    isEnrolled: boolean;
    completedLessonIds: number[];
}

export default function LessonShow({
    lesson,
    course,
    previousLesson,
    nextLesson,
    isEnrolled,
    completedLessonIds,
}: LessonShowProps) {
    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn', href: CourseIndexController.url() },
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
                            {/* Lesson Title and Description */}
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

                            {/* Video Player */}
                            <div className="relative mb-8 overflow-hidden rounded-xl bg-neutral-900">
                                <div className="aspect-video w-full">
                                    <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-neutral-800 to-neutral-900">
                                        <div className="text-center">
                                            <div className="mx-auto mb-4 flex size-20 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                                                <Play className="size-10 text-white" />
                                            </div>
                                            <p className="text-lg font-semibold text-white">
                                                Video Player
                                            </p>
                                            <p className="mt-2 text-sm text-neutral-400">
                                                Mux video will be embedded here
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* What We Cover */}
                            {lesson.learning_objectives && (
                                <LessonWhatWeCover
                                    html={lesson.learning_objectives}
                                    truncateOnMobile={true}
                                />
                            )}

                            {/* Lesson Content */}
                            {lesson.copy_html && (
                                <LessonContent
                                    html={lesson.copy_html}
                                    truncateOnMobile={true}
                                />
                            )}

                            {/* Transcript - Mobile Only */}
                            {lesson.transcript && (
                                <div className="lg:hidden">
                                    <LessonTranscript
                                        transcript={lesson.transcript}
                                        truncateOnMobile={true}
                                    />
                                </div>
                            )}

                            {/* Navigation Buttons */}
                            <div className="flex items-center justify-between gap-4 border-t border-neutral-200 pt-8 dark:border-neutral-800">
                                {previousLesson ? (
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="group"
                                    >
                                        <Link
                                            href={LessonShowController.url({
                                                course: course.slug,
                                                lesson: previousLesson.slug,
                                            })}
                                        >
                                            <span className="line-clamp-2 text-left font-medium">
                                                Previous
                                            </span>
                                        </Link>
                                    </Button>
                                ) : (
                                    <div />
                                )}

                                {nextLesson ? (
                                    <Button asChild className="group ml-auto">
                                        <Link
                                            href={LessonShowController.url({
                                                course: course.slug,
                                                lesson: nextLesson.slug,
                                            })}
                                            className="flex items-center gap-3"
                                        >
                                            <span className="line-clamp-2 text-right font-medium">
                                                Next
                                            </span>
                                            <ArrowRight className="size-4 shrink-0 transition-transform group-hover:translate-x-1" />
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="ml-auto"
                                    >
                                        <Link
                                            href={CourseShowController.url({
                                                course: course.slug,
                                            })}
                                        >
                                            Back to Course
                                        </Link>
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Desktop Sidebar - Course Outline & Transcript */}
                        <aside className="hidden lg:block lg:w-80">
                            <div className="sticky top-4 space-y-6">
                                {/* Course Outline */}
                                <div className="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                                    <h3 className="mb-4 font-semibold text-neutral-900 dark:text-white">
                                        Course Outline
                                    </h3>
                                    <nav className="space-y-1">
                                        {course.lessons?.map(
                                            (navLesson, index) => {
                                                const isCurrent =
                                                    navLesson.id === lesson.id;
                                                const isLocked =
                                                    navLesson.gated &&
                                                    !isEnrolled;
                                                const isComplete =
                                                    completedLessonIds.includes(
                                                        navLesson.id,
                                                    );

                                                return (
                                                    <Link
                                                        key={navLesson.id}
                                                        href={LessonShowController.url(
                                                            {
                                                                course: course.slug,
                                                                lesson: navLesson.slug,
                                                            },
                                                        )}
                                                        className={cn(
                                                            'block rounded-md px-3 py-2 text-sm transition-colors',
                                                            isCurrent
                                                                ? 'bg-neutral-900 font-semibold text-white dark:bg-white dark:text-neutral-900'
                                                                : isLocked
                                                                  ? 'cursor-not-allowed text-neutral-400 dark:text-neutral-600'
                                                                  : 'text-neutral-600 hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-800',
                                                        )}
                                                    >
                                                        <div className="flex items-center justify-between">
                                                            <div className="flex items-center gap-2">
                                                                <span className="text-neutral-500 dark:text-neutral-400">
                                                                    {index + 1}.
                                                                </span>
                                                                <span>
                                                                    {
                                                                        navLesson.title
                                                                    }
                                                                </span>
                                                            </div>
                                                            {isComplete && (
                                                                <Check
                                                                    className={cn(
                                                                        'size-4 shrink-0',
                                                                        isCurrent
                                                                            ? 'text-white dark:text-neutral-900'
                                                                            : 'text-green-600 dark:text-green-400',
                                                                    )}
                                                                />
                                                            )}
                                                        </div>
                                                    </Link>
                                                );
                                            },
                                        )}
                                    </nav>
                                </div>

                                {/* Transcript - Desktop Only */}
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
