import { cn } from '@/lib/utils';

interface CourseProgressBarProps {
    progressPercentage: number;
}

export function CourseProgressBar({
    progressPercentage,
}: CourseProgressBarProps) {
    return (
        <div>
            <div className="mb-2 flex items-center justify-between text-xs">
                <span className="font-medium text-neutral-700 dark:text-neutral-300">
                    {progressPercentage >= 100 ? 'Completed' : 'In Progress'}
                </span>
                <span className="text-neutral-500 dark:text-neutral-400">
                    {progressPercentage}%
                </span>
            </div>
            <div className="h-1.5 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-800">
                <div
                    className={cn(
                        'h-full rounded-full transition-all',
                        progressPercentage >= 100
                            ? 'bg-green-600 dark:bg-green-500'
                            : 'bg-blue-600 dark:bg-blue-500',
                    )}
                    style={{
                        width: `${progressPercentage}%`,
                    }}
                />
            </div>
        </div>
    );
}
