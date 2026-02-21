import { cn } from '@/lib/utils';
import {
    AlertTriangle,
    CheckCircle2,
    Info,
    XCircle,
} from 'lucide-react';

export interface ChecklistItem {
    label: string;
    completed: boolean;
}

interface ReadinessChecklistProps {
    title: string;
    items: ChecklistItem[];
    variant: 'required' | 'optional' | 'info';
}

export function ReadinessChecklist({
    title,
    items,
    variant,
}: ReadinessChecklistProps) {
    const completedCount = items.filter((item) => item.completed === true).length;
    const allComplete = completedCount === items.length;

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between">
                <h4 className="text-sm font-medium text-neutral-900 dark:text-white">
                    {title}
                </h4>
                <span
                    className={cn(
                        'text-xs',
                        allComplete
                            ? 'text-green-600 dark:text-green-400'
                            : variant === 'required'
                              ? 'text-red-600 dark:text-red-400'
                              : variant === 'optional'
                                ? 'text-amber-600 dark:text-amber-400'
                                : 'text-neutral-500 dark:text-neutral-400',
                    )}
                >
                    {completedCount} of {items.length} complete
                </span>
            </div>
            <ul className="space-y-1">
                {items.map((item) => (
                    <li
                        key={item.label}
                        className="flex items-center gap-2 text-sm"
                    >
                        {item.completed === true ? (
                            <CheckCircle2 className="size-4 shrink-0 text-green-600 dark:text-green-400" />
                        ) : (
                            <IncompleteIcon variant={variant} />
                        )}
                        <span
                            className={cn(
                                item.completed === true
                                    ? 'text-neutral-700 dark:text-neutral-300'
                                    : 'text-neutral-500 dark:text-neutral-400',
                            )}
                        >
                            {item.label}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

function IncompleteIcon({ variant }: { variant: 'required' | 'optional' | 'info' }) {
    if (variant === 'required') {
        return (
            <XCircle className="size-4 shrink-0 text-red-500 dark:text-red-400" />
        );
    }

    if (variant === 'optional') {
        return (
            <AlertTriangle className="size-4 shrink-0 text-amber-500 dark:text-amber-400" />
        );
    }

    return (
        <Info className="size-4 shrink-0 text-neutral-400 dark:text-neutral-500" />
    );
}
