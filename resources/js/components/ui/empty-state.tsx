import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { type LucideIcon } from 'lucide-react';

interface EmptyStateAction {
    label: string;
    href: string;
}

interface EmptyStateProps {
    icon: LucideIcon;
    title: string;
    description: string;
    action?: EmptyStateAction;
    className?: string;
}

export function EmptyState({
    icon: Icon,
    title,
    description,
    action,
    className,
}: EmptyStateProps) {
    return (
        <div
            className={cn(
                'flex items-center gap-4 rounded-lg border border-neutral-200 bg-neutral-50 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-900',
                className,
            )}
        >
            <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-neutral-200 dark:bg-neutral-800">
                <Icon className="size-5 text-neutral-500 dark:text-neutral-400" />
            </div>
            <div className="min-w-0 flex-1">
                <p className="font-semibold text-neutral-900 dark:text-white">
                    {title}
                </p>
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    {description}
                </p>
            </div>
            {action && (
                <Button asChild variant="outline" size="sm" className="shrink-0">
                    <Link href={action.href}>{action.label}</Link>
                </Button>
            )}
        </div>
    );
}
