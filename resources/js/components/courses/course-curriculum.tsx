import LessonShowController from '@/actions/App/Http/Controllers/Learn/LessonShowController';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/react';
import { BookOpen, Check } from 'lucide-react';

interface CourseCurriculumProps {
    courseSlug: string;
    lessons: App.Http.Resources.Course.LessonResource[];
    completedLessonIds: number[];
    isComingSoon?: boolean;
}

export function CourseCurriculum({
    courseSlug,
    lessons,
    completedLessonIds,
    isComingSoon = false,
}: CourseCurriculumProps) {
    return (
        <div className="pt-8">
            <h2 className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                <BookOpen className="size-5" />
                Course Curriculum
            </h2>

            {lessons.length > 0 ? (
                <div className="mt-6 divide-y divide-neutral-100 dark:divide-neutral-800">
                    {lessons.map((lesson, index) => {
                        const isComplete = completedLessonIds.includes(
                            lesson.id,
                        );
                        const isLessonComingSoon =
                            isComingSoon || lesson.is_previewable === true;

                        const Wrapper = isLessonComingSoon ? 'div' : Link;
                        const wrapperProps = isLessonComingSoon
                            ? {}
                            : {
                                  href: LessonShowController.url({
                                      course: courseSlug,
                                      lesson: lesson.slug,
                                  }),
                              };

                        return (
                            <Wrapper
                                key={lesson.id}
                                {...wrapperProps}
                                className={`flex items-start gap-4 rounded-lg px-4 py-4 transition-colors ${isLessonComingSoon ? 'opacity-60' : 'hover:bg-neutral-50 dark:hover:bg-neutral-900'}`}
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
                                        {isLessonComingSoon ? (
                                            <Badge
                                                size="xs"
                                                className="shrink-0 border-amber-300 bg-amber-100 text-amber-800 dark:border-amber-700 dark:bg-amber-900 dark:text-amber-200"
                                            >
                                                Coming Soon
                                            </Badge>
                                        ) : (
                                            isComplete && (
                                                <Check className="size-5 shrink-0 text-green-600 dark:text-green-400" />
                                            )
                                        )}
                                    </div>
                                </div>
                            </Wrapper>
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
    );
}
