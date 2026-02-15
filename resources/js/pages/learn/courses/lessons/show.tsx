import CourseIndexController from '@/actions/App/Http/Controllers/Course/Public/CourseIndexController';
import CourseShowController from '@/actions/App/Http/Controllers/Course/Public/CourseShowController';
import LessonCompleteController from '@/actions/App/Http/Controllers/Course/Public/LessonCompleteController';
import LessonShowController from '@/actions/App/Http/Controllers/Course/Public/LessonShowController';
import { RichTextContent } from '@/components/showcase/rich-text-content';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Check, Play } from 'lucide-react';
import { useState } from 'react';

interface LessonShowProps {
    lesson: App.Http.Resources.Course.LessonResource;
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
    };
    previousLesson: { slug: string; title: string } | null;
    nextLesson: { slug: string; title: string } | null;
    isEnrolled: boolean;
    completedLessonIds: number[];
    isLessonComplete: boolean;
}

export default function LessonShow({
    lesson,
    course,
    previousLesson,
    nextLesson,
    isEnrolled,
    completedLessonIds,
    isLessonComplete,
}: LessonShowProps) {
    const [isMarking, setIsMarking] = useState(false);
    const [localComplete, setLocalComplete] = useState(isLessonComplete);

    const handleMarkComplete = async () => {
        if (!isEnrolled || localComplete) return;

        setIsMarking(true);
        setLocalComplete(true); // Optimistic update

        try {
            await fetch(
                LessonCompleteController.url({
                    course: course.slug,
                    lesson: lesson.slug,
                }),
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                },
            );

            // Reload the page to get updated progress
            router.reload({ only: ['completedLessonIds', 'isLessonComplete'] });
        } catch (error) {
            setLocalComplete(false); // Revert on error
            console.error('Failed to mark lesson complete:', error);
        } finally {
            setIsMarking(false);
        }
    };

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
                    {/* Back to Course Link */}
                    <Link
                        href={CourseShowController.url({
                            course: course.slug,
                        })}
                        className="mb-6 inline-flex items-center gap-2 text-sm text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                    >
                        <ArrowLeft className="size-4" />
                        Back to {course.title}
                    </Link>

                    <div className="flex flex-col gap-8 lg:flex-row">
                        {/* Main Content */}
                        <div className="flex-1">
                            {/* Large Video Placeholder */}
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

                            {/* Lesson Title and Description */}
                            <div className="mb-8">
                                <div className="flex items-start justify-between gap-4">
                                    <div className="flex-1">
                                        <h1 className="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
                                            {lesson.title}
                                        </h1>
                                        {lesson.tagline && (
                                            <p className="mt-2 text-lg text-neutral-600 dark:text-neutral-400">
                                                {lesson.tagline}
                                            </p>
                                        )}
                                    </div>
                                    {isEnrolled && !localComplete && (
                                        <Button
                                            onClick={handleMarkComplete}
                                            disabled={isMarking}
                                            variant="outline"
                                            className="shrink-0"
                                        >
                                            {isMarking ? (
                                                <>Marking...</>
                                            ) : (
                                                <>
                                                    <Check className="mr-2 size-4" />
                                                    Mark Complete
                                                </>
                                            )}
                                        </Button>
                                    )}
                                    {localComplete && (
                                        <div className="flex shrink-0 items-center gap-2 rounded-md bg-green-50 px-4 py-2 text-sm font-medium text-green-700 dark:bg-green-950 dark:text-green-400">
                                            <Check className="size-4" />
                                            Completed
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* What You'll Learn */}
                            {lesson.learning_objectives && (
                                <div className="mb-8">
                                    <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                                        What You'll Learn
                                    </h2>
                                    <div className="prose dark:prose-invert max-w-none">
                                        <RichTextContent
                                            html={lesson.learning_objectives}
                                            className="rich-text-content"
                                        />
                                    </div>
                                </div>
                            )}

                            {/* Tabs for Lesson Content and Transcript */}
                            {(lesson.copy_html || lesson.transcript) && (
                                <div className="mb-8">
                                    <Tabs
                                        defaultValue={
                                            lesson.copy_html
                                                ? 'lesson'
                                                : 'transcript'
                                        }
                                        className="w-full"
                                    >
                                        <TabsList>
                                            {lesson.copy_html && (
                                                <TabsTrigger value="lesson">
                                                    Lesson
                                                </TabsTrigger>
                                            )}
                                            {lesson.transcript && (
                                                <TabsTrigger value="transcript">
                                                    Transcript
                                                </TabsTrigger>
                                            )}
                                        </TabsList>
                                        {lesson.copy_html && (
                                            <TabsContent value="lesson">
                                                <div className="rounded-lg border border-neutral-200 p-6 dark:border-neutral-800">
                                                    <RichTextContent
                                                        html={lesson.copy_html}
                                                        className="rich-text-content"
                                                    />
                                                </div>
                                            </TabsContent>
                                        )}
                                        {lesson.transcript && (
                                            <TabsContent value="transcript">
                                                <div className="rounded-lg border border-neutral-200 p-6 dark:border-neutral-800">
                                                    <div className="prose dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400">
                                                        {lesson.transcript}
                                                    </div>
                                                </div>
                                            </TabsContent>
                                        )}
                                    </Tabs>
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
                                            className="flex items-center gap-3 px-4 py-3"
                                        >
                                            <ArrowLeft className="size-4 shrink-0 transition-transform group-hover:-translate-x-1" />
                                            <span className="line-clamp-2 text-left font-medium">
                                                {previousLesson.title}
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
                                            className="flex items-center gap-3 px-4 py-3"
                                        >
                                            <span className="line-clamp-2 text-right font-medium">
                                                {nextLesson.title}
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
                                            className="px-4 py-3"
                                        >
                                            Back to Course
                                        </Link>
                                    </Button>
                                )}
                            </div>
                        </div>

                        {/* Sidebar - Course Outline */}
                        <aside className="lg:w-80">
                            <div className="sticky top-4 rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                                <h3 className="mb-4 font-semibold text-neutral-900 dark:text-white">
                                    Course Outline
                                </h3>
                                <nav className="space-y-1">
                                    {course.lessons?.map((navLesson, index) => {
                                        const isCurrent =
                                            navLesson.id === lesson.id;
                                        const isLocked =
                                            navLesson.gated && !isEnrolled;
                                        const isComplete =
                                            completedLessonIds.includes(
                                                navLesson.id,
                                            ) ||
                                            (isCurrent && localComplete);

                                        return (
                                            <Link
                                                key={navLesson.id}
                                                href={LessonShowController.url({
                                                    course: course.slug,
                                                    lesson: navLesson.slug,
                                                })}
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
                                                            {navLesson.title}
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
                                    })}
                                </nav>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
