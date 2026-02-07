import { AvatarFallback } from '@/components/ui/avatar-fallback';
import { Button } from '@/components/ui/button';
import { destroy } from '@/routes/staff/press-coverage';
import { router } from '@inertiajs/react';
import { ExternalLink, EyeOff, Pencil, Trash2 } from 'lucide-react';
import { type ReactNode } from 'react';

interface PressCoverageListItemProps {
    pressCoverage: App.Http.Resources.PressCoverageResource;
    onEdit?: () => void;
    actions?: ReactNode;
}

export function PressCoverageListItem({
    pressCoverage,
    onEdit,
    actions,
}: PressCoverageListItemProps) {
    const handleDelete = () => {
        if (
            confirm(
                'Are you sure you want to delete this press coverage? This action cannot be undone.',
            )
        ) {
            router.delete(destroy.url({ pressCoverage: pressCoverage.id }));
        }
    };

    return (
        <div className="flex gap-4 px-4 py-4">
            <div className="shrink-0">
                <AvatarFallback
                    name={pressCoverage.publication_name}
                    imageUrl={pressCoverage.thumbnail_url}
                    size="lg"
                    shape="square"
                />
            </div>

            <div className="min-w-0 flex-1">
                <div className="flex items-start gap-3">
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-2">
                            <h3 className="font-semibold text-neutral-900 dark:text-white">
                                {pressCoverage.title}
                            </h3>
                            {pressCoverage.is_published === false && (
                                <span className="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                                    <EyeOff className="size-3" />
                                    Unpublished
                                </span>
                            )}
                        </div>
                        <div className="mt-1 flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                            <span className="font-medium">
                                {pressCoverage.publication_name}
                            </span>
                            <span>•</span>
                            <span>{pressCoverage.publication_date}</span>
                        </div>
                        {pressCoverage.excerpt && (
                            <p className="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                                {pressCoverage.excerpt}
                            </p>
                        )}
                        <div className="mt-2">
                            <a
                                href={pressCoverage.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1 text-xs text-neutral-500 hover:text-amber-600 dark:text-neutral-400 dark:hover:text-amber-500"
                            >
                                <ExternalLink className="size-3" />
                                View article
                            </a>
                        </div>
                    </div>
                </div>
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

                <Button
                    variant="destructive"
                    size="sm"
                    onClick={handleDelete}
                    className="gap-1.5"
                >
                    <Trash2 className="size-4" />
                    Delete
                </Button>
            </div>
        </div>
    );
}
