import { formatDuration } from '@/lib/course-utils';
import { Clock, Users } from 'lucide-react';

interface CourseStatsProps {
    course: App.Http.Resources.Course.CourseResource;
}

export function CourseStats({ course }: CourseStatsProps) {
    const duration = formatDuration(course.duration_seconds);

    return (
        <div className="flex items-center gap-4 text-sm text-neutral-500 dark:text-neutral-400">
            <div className="flex items-center gap-1.5">
                <Users className="size-4" />
                <span>{course.started_count ?? 0} already enrolled</span>
            </div>
            {duration && (
                <div className="flex items-center gap-1.5">
                    <Clock className="size-4" />
                    <span>{duration}</span>
                </div>
            )}
        </div>
    );
}
