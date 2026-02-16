import CourseShowController from '@/actions/App/Http/Controllers/Learn/CourseShowController';
import LessonShowController from '@/actions/App/Http/Controllers/Learn/LessonShowController';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

interface LessonNavigationProps {
    courseSlug: string;
    previousLesson: { slug: string; title: string } | null;
    nextLesson: { slug: string; title: string } | null;
}

export function LessonNavigation({
    courseSlug,
    previousLesson,
    nextLesson,
}: LessonNavigationProps) {
    return (
        <div className="flex items-center justify-between gap-4 border-t border-neutral-200 pt-8 dark:border-neutral-800">
            {previousLesson ? (
                <Button asChild variant="outline" className="group">
                    <Link
                        href={LessonShowController.url({
                            course: courseSlug,
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
                            course: courseSlug,
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
                <Button asChild variant="outline" className="ml-auto">
                    <Link
                        href={CourseShowController.url({
                            course: courseSlug,
                        })}
                    >
                        Back to Course
                    </Link>
                </Button>
            )}
        </div>
    );
}
