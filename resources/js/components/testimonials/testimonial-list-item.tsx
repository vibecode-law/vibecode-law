import { AvatarFallback } from '@/components/ui/avatar-fallback';
import { Button } from '@/components/ui/button';
import { destroy } from '@/routes/staff/testimonials';
import { router } from '@inertiajs/react';
import { EyeOff, Pencil, Trash2 } from 'lucide-react';
import { type ReactNode } from 'react';

interface TestimonialListItemProps {
    testimonial: App.Http.Resources.TestimonialResource;
    onEdit?: () => void;
    actions?: ReactNode;
}

export function TestimonialListItem({
    testimonial,
    onEdit,
    actions,
}: TestimonialListItemProps) {
    const handleDelete = () => {
        if (
            confirm(
                'Are you sure you want to delete this testimonial? This action cannot be undone.',
            )
        ) {
            router.delete(destroy.url({ testimonial: testimonial.id }));
        }
    };

    return (
        <div className="flex gap-4 px-4 py-4">
            <div className="shrink-0">
                <AvatarFallback
                    name={testimonial.display_name ?? 'Anonymous'}
                    imageUrl={testimonial.avatar}
                    size="sm"
                    shape="circle"
                />
            </div>

            <div className="min-w-0 flex-1">
                <div className="flex items-start gap-3">
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center gap-2">
                            {testimonial.display_name && (
                                <h3 className="font-semibold text-neutral-900 dark:text-white">
                                    {testimonial.display_name}
                                </h3>
                            )}
                            {testimonial.is_published === false && (
                                <span className="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400">
                                    <EyeOff className="size-3" />
                                    Unpublished
                                </span>
                            )}
                            {testimonial.user_id !== null && (
                                <span className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    Linked to User
                                </span>
                            )}
                        </div>
                        {(testimonial.display_job_title ||
                            testimonial.display_organisation) && (
                            <p className="mt-0.5 text-sm text-neutral-600 dark:text-neutral-400">
                                {testimonial.display_job_title}
                                {testimonial.display_job_title &&
                                    testimonial.display_organisation &&
                                    ' at '}
                                {testimonial.display_organisation}
                            </p>
                        )}
                        <p className="mt-2 text-sm text-neutral-700 dark:text-neutral-300">
                            "{testimonial.content}"
                        </p>
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
