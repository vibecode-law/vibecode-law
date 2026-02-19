import LessonShowController from '@/actions/App/Http/Controllers/Learn/LessonShowController';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { Check } from 'lucide-react';

interface LessonSidebarOutlineProps {
    courseSlug: string;
    lessons: App.Http.Resources.Course.LessonResource[];
    currentLessonId: number;
    completedLessonIds: number[];
}

export function LessonSidebarOutline({
    courseSlug,
    lessons,
    currentLessonId,
    completedLessonIds,
}: LessonSidebarOutlineProps) {
    return (
        <div>
            <p className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                Course Outline
            </p>
            <nav className="space-y-1">
                {lessons.map((navLesson, index) => {
                    const isCurrent = navLesson.id === currentLessonId;
                    const isLocked = navLesson.gated;
                    const isComplete = completedLessonIds.includes(
                        navLesson.id,
                    );
                    const isPreviewable = navLesson.is_previewable === true;

                    if (isPreviewable) {
                        return (
                            <span
                                key={navLesson.id}
                                className="block rounded-md px-3 py-2 text-sm text-neutral-400 dark:text-neutral-600"
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span>{index + 1}.</span>
                                        <span>{navLesson.title}</span>
                                    </div>
                                    <Badge
                                        size="xs"
                                        className="border-amber-300 bg-amber-100 text-amber-800 dark:border-amber-700 dark:bg-amber-900 dark:text-amber-200"
                                    >
                                        Soon
                                    </Badge>
                                </div>
                            </span>
                        );
                    }

                    return (
                        <Link
                            key={navLesson.id}
                            href={LessonShowController.url({
                                course: courseSlug,
                                lesson: navLesson.slug,
                            })}
                            className={cn(
                                'block rounded-md px-3 py-2 text-sm transition-colors',
                                isCurrent
                                    ? 'bg-neutral-900 font-semibold text-white dark:bg-white dark:text-neutral-900'
                                    : isLocked
                                      ? 'text-neutral-400 dark:text-neutral-600'
                                      : 'text-neutral-600 hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-800',
                            )}
                        >
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <span className="text-neutral-500 dark:text-neutral-400">
                                        {index + 1}.
                                    </span>
                                    <span>{navLesson.title}</span>
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
    );
}
