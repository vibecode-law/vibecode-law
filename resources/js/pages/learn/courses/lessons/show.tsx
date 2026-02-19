import LessonPlayerEventController from '@/actions/App/Http/Controllers/Api/Learn/LessonPlayerEventController';
import CourseShowController from '@/actions/App/Http/Controllers/Learn/CourseShowController';
import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import { InstructorList } from '@/components/courses/instructor-list';
import { LessonContent } from '@/components/courses/lesson-content';
import { LessonGatedPlaceholder } from '@/components/courses/lesson-gated-placeholder';
import { LessonNavigation } from '@/components/courses/lesson-navigation';
import { LessonSidebarOutline } from '@/components/courses/lesson-sidebar-outline';
import { LessonTranscript } from '@/components/courses/lesson-transcript';
import { LessonVideoPlayer } from '@/components/courses/lesson-video-player';
import { LessonWhatWeCover } from '@/components/courses/lesson-what-we-cover';
import { TabNav } from '@/components/navigation/tab-nav';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useRef, useState } from 'react';

interface LessonProgress {
    started: boolean;
    completed: boolean;
    playback_time_seconds: number | null;
}

interface LessonShowProps {
    lesson: App.Http.Resources.Course.LessonResource & {
        tags?: App.Http.Resources.TagResource[];
        instructors?: App.Http.Resources.User.UserResource[];
    };
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
    };
    previousLesson: { slug: string; title: string } | null;
    nextLesson: { slug: string; title: string } | null;
    completedLessonIds: number[];
    lessonProgress: LessonProgress;
    isGatedForUser: boolean;
}

export default function LessonShow({
    lesson,
    course,
    previousLesson,
    nextLesson,
    completedLessonIds,
    lessonProgress,
    isGatedForUser,
}: LessonShowProps) {
    const progressRef = useRef<LessonProgress>(lessonProgress);
    const lastTimeUpdateRef = useRef(0);
    const playerContainerRef = useRef<HTMLDivElement>(null);
    const throttleInterval = 5000;

    const sendPlayerEvent = useCallback(
        (event: string, currentTime?: number) => {
            const { url, method } = LessonPlayerEventController({
                course: course.slug,
                lesson: lesson.slug,
            });

            const data: Record<string, unknown> = { event };
            if (currentTime !== undefined) {
                data.current_time = currentTime;
            }

            axios({ url, method, data });
        },
        [course.slug, lesson.slug],
    );

    const handlePlaying = useCallback(() => {
        if (progressRef.current.started === true) {
            return;
        }

        progressRef.current.started = true;
        sendPlayerEvent('playing');
    }, [sendPlayerEvent]);

    const handleTimeUpdate = useCallback(
        (currentTime: number) => {
            const roundedTime = Math.floor(currentTime);

            if (
                progressRef.current.playback_time_seconds !== null &&
                roundedTime <= progressRef.current.playback_time_seconds
            ) {
                return;
            }

            const now = Date.now();

            if (now - lastTimeUpdateRef.current >= throttleInterval) {
                lastTimeUpdateRef.current = now;
                progressRef.current.playback_time_seconds = roundedTime;
                sendPlayerEvent('timeupdate', currentTime);
            }
        },
        [sendPlayerEvent],
    );

    const handleEnded = useCallback(() => {
        if (progressRef.current.completed === true) {
            return;
        }

        progressRef.current.completed = true;
        sendPlayerEvent('ended');
    }, [sendPlayerEvent]);

    const [activeTab, setActiveTab] = useState<'lesson' | 'transcript'>(
        'lesson',
    );

    const handleSeek = useCallback((timeSeconds: number) => {
        const muxPlayer =
            playerContainerRef.current?.querySelector('mux-player');

        if (muxPlayer) {
            (muxPlayer as HTMLMediaElement).currentTime = timeSeconds;
        }

        playerContainerRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, []);

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

                            {/* Mobile: Instructors */}
                            {lesson.instructors &&
                                lesson.instructors.length > 0 && (
                                    <div className="mb-8 lg:hidden">
                                        <InstructorList
                                            instructors={lesson.instructors}
                                        />
                                    </div>
                                )}

                            <div ref={playerContainerRef}>
                                {isGatedForUser ? (
                                    <LessonGatedPlaceholder
                                        thumbnailUrl={lesson.thumbnail_url}
                                    />
                                ) : (
                                    <LessonVideoPlayer
                                        playbackId={lesson.playback_id}
                                        host={lesson.host}
                                        playbackTokens={lesson.playback_tokens}
                                        title={lesson.title}
                                        startTime={
                                            lessonProgress.completed ===
                                                false &&
                                            lessonProgress.playback_time_seconds !==
                                                null
                                                ? lessonProgress.playback_time_seconds
                                                : undefined
                                        }
                                        onPlaying={handlePlaying}
                                        onTimeUpdate={handleTimeUpdate}
                                        onEnded={handleEnded}
                                    />
                                )}
                            </div>

                            {lesson.learning_objectives_html && (
                                <LessonWhatWeCover
                                    html={lesson.learning_objectives_html}
                                    tags={lesson.tags}
                                    truncateOnMobile={true}
                                />
                            )}

                            {isGatedForUser ? (
                                <div className="mb-8">
                                    <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                                        Lesson
                                    </h2>
                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                        Log in to access the full lesson
                                        content.
                                    </p>
                                </div>
                            ) : (
                                <div className="mb-8">
                                    <TabNav
                                        items={[
                                            {
                                                title: 'Lesson',
                                                onClick: () =>
                                                    setActiveTab('lesson'),
                                                isActive:
                                                    activeTab === 'lesson',
                                            },
                                            {
                                                title: 'Transcript',
                                                onClick: () =>
                                                    setActiveTab('transcript'),
                                                isActive:
                                                    activeTab === 'transcript',
                                            },
                                        ]}
                                        ariaLabel="Lesson content"
                                    />

                                    <div className="mt-6">
                                        {activeTab === 'lesson' &&
                                            lesson.copy_html && (
                                                <LessonContent
                                                    html={lesson.copy_html}
                                                    truncateOnMobile={true}
                                                />
                                            )}

                                        {activeTab === 'transcript' && (
                                            <LessonTranscript
                                                lines={
                                                    lesson.transcript_lines ??
                                                    []
                                                }
                                                onSeek={handleSeek}
                                            />
                                        )}
                                    </div>
                                </div>
                            )}

                            <LessonNavigation
                                courseSlug={course.slug}
                                previousLesson={previousLesson}
                                nextLesson={nextLesson}
                            />
                        </div>

                        {/* Desktop Sidebar */}
                        <aside className="hidden shrink-0 lg:block lg:w-64 xl:w-68 2xl:w-72">
                            <div className="sticky top-4">
                                {lesson.instructors &&
                                    lesson.instructors.length > 0 && (
                                        <div className="mb-8">
                                            <InstructorList
                                                instructors={lesson.instructors}
                                            />
                                        </div>
                                    )}

                                <div className="mb-8">
                                    <LessonSidebarOutline
                                        courseSlug={course.slug}
                                        lessons={course.lessons ?? []}
                                        currentLessonId={lesson.id}
                                        completedLessonIds={completedLessonIds}
                                    />
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
