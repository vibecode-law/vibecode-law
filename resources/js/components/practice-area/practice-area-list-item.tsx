import { Button } from '@/components/ui/button';
import { Pencil } from 'lucide-react';
import { type ReactNode } from 'react';

interface PracticeAreaListItemProps {
    practiceArea: App.Http.Resources.PracticeAreaResource;
    onEdit?: () => void;
    actions?: ReactNode;
}

export function PracticeAreaListItem({
    practiceArea,
    onEdit,
    actions,
}: PracticeAreaListItemProps) {
    return (
        <div className="flex items-center gap-4 py-4">
            <div className="min-w-0 flex-1">
                <div className="flex items-center gap-3">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        {practiceArea.name}
                    </h3>
                    <code className="rounded bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                        {practiceArea.slug}
                    </code>
                </div>
                {practiceArea.showcases_count !== undefined && (
                    <p className="mt-0.5 text-sm text-neutral-500 dark:text-neutral-300">
                        {practiceArea.showcases_count}{' '}
                        {practiceArea.showcases_count === 1
                            ? 'showcase'
                            : 'showcases'}
                    </p>
                )}
            </div>

            <div className="flex shrink-0 items-center gap-3">
                {actions}

                {onEdit !== undefined && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={onEdit}
                        className="gap-1.5"
                    >
                        <Pencil className="size-4" />
                        Edit
                    </Button>
                )}
            </div>
        </div>
    );
}
