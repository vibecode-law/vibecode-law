import { CourseProgressBar } from '@/components/course/course-progress-bar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { formatDuration, getExperienceLevelColor } from '@/lib/course-utils';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Clock, Users } from 'lucide-react';

interface CourseCardProps {
    course: App.Http.Resources.Course.CourseResource;
    progress?: {
        progressPercentage: number;
    };
}

export function CourseCard({ course, progress }: CourseCardProps) {
    const { transformImages } = usePage<SharedData>().props;

    const thumbnailSrc = course.thumbnail_url
        ? transformImages === true
            ? `${course.thumbnail_url}?w=600`
            : course.thumbnail_url
        : null;

    const duration = formatDuration(course.duration_seconds);

    return (
        <Link
            href={`/learn/courses/${course.slug}`}
            className="group relative flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
            {/* Thumbnail */}
            <div className="relative aspect-video overflow-hidden">
                {thumbnailSrc ? (
                    <img
                        src={thumbnailSrc}
                        alt={course.title}
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-neutral-100 to-neutral-200 dark:from-neutral-800 dark:to-neutral-900">
                        <BookOpen className="size-16 text-neutral-400 dark:text-neutral-600" />
                    </div>
                )}
                <div className="absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent" />
            </div>

            {/* Content */}
            <div className="flex flex-1 flex-col p-6">
                <div className="mb-2 flex items-center justify-between text-sm text-neutral-500 dark:text-neutral-400">
                    <div className="flex items-center gap-3">
                        <div className="flex items-center gap-1">
                            <Users className="size-3.5" />
                            <span>
                                {course.started_count ?? 0} already enrolled
                            </span>
                        </div>
                        {duration && (
                            <div className="flex items-center gap-1">
                                <Clock className="size-3.5" />
                                <span>{duration}</span>
                            </div>
                        )}
                    </div>
                    {course.experience_level && (
                        <Badge
                            size="xs"
                            className={getExperienceLevelColor(
                                Number(course.experience_level.value),
                            )}
                        >
                            {course.experience_level.label}
                        </Badge>
                    )}
                </div>
                <h3 className="text-lg font-semibold text-neutral-900 dark:text-white">
                    {course.title}
                </h3>
                <p className="mt-2 line-clamp-2 flex-1 text-sm text-neutral-600 dark:text-neutral-400">
                    {course.tagline}
                </p>

                {/* Progress Bar */}
                {progress && progress.progressPercentage > 0 && (
                    <div className="mt-4 border-t border-neutral-100 pt-4 dark:border-neutral-800">
                        <CourseProgressBar
                            progressPercentage={progress.progressPercentage}
                        />
                    </div>
                )}

                {/* Author */}
                {course.user && (
                    <div
                        className={cn(
                            'mt-4 flex items-center gap-2 pt-4',
                            (!progress || progress.progressPercentage <= 0) &&
                                'border-t border-neutral-100 dark:border-neutral-800',
                        )}
                    >
                        <Avatar className="size-6">
                            {course.user.avatar ? (
                                <AvatarImage
                                    src={
                                        transformImages === true
                                            ? `${course.user.avatar}?w=48`
                                            : course.user.avatar
                                    }
                                    alt={course.user.first_name}
                                />
                            ) : null}
                            <AvatarFallback className="bg-neutral-100 text-xs font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                {course.user.first_name.charAt(0)}
                            </AvatarFallback>
                        </Avatar>
                        <span className="text-sm text-neutral-600 dark:text-neutral-400">
                            {course.user.first_name} {course.user.last_name}
                        </span>
                    </div>
                )}
            </div>
        </Link>
    );
}
