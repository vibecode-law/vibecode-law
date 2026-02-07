import HeadingSmall from '@/components/heading/heading-small';
import { CreateTestimonialModal } from '@/components/testimonials/create-testimonial-modal';
import { EditTestimonialModal } from '@/components/testimonials/edit-testimonial-modal';
import { TestimonialListItem } from '@/components/testimonials/testimonial-list-item';
import { SortableItem } from '@/components/ui/sortable-item';
import { SortableList } from '@/components/ui/sortable-list';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { reorder, store, update } from '@/routes/staff/testimonials';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface TestimonialsIndexProps {
    testimonials: App.Http.Resources.TestimonialResource[];
}

export default function TestimonialsIndex({
    testimonials,
}: TestimonialsIndexProps) {
    const [editingTestimonial, setEditingTestimonial] =
        useState<App.Http.Resources.TestimonialResource | null>(null);

    const [localTestimonials, setLocalTestimonials] = useState(testimonials);

    // Sync local state when testimonials prop changes (after create/delete)
    useEffect(() => {
        setLocalTestimonials(testimonials);
    }, [testimonials]);

    const handleReorder = (
        reorderedItems: App.Http.Resources.TestimonialResource[],
    ) => {
        // Update local state immediately for instant feedback
        setLocalTestimonials(reorderedItems);

        // Send update to backend
        router.post(
            reorder.url(),
            {
                items: reorderedItems.map((item) => ({
                    id: item.id,
                    display_order: item.display_order,
                })),
            },
            {
                preserveScroll: true,
                preserveState: true,
                only: [], // Don't reload any props
            },
        );
    };

    return (
        <StaffAreaLayout fullWidth>
            <Head title="Testimonials" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Testimonials"
                        description="Drag and drop to reorder testimonials"
                    />
                    <CreateTestimonialModal storeUrl={store.url()} />
                </div>

                {localTestimonials.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-neutral-300 py-12 text-center dark:border-neutral-700">
                        <p className="text-neutral-500 dark:text-neutral-400">
                            No testimonials yet
                        </p>
                    </div>
                ) : (
                    <div className="rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                        <SortableList
                            items={localTestimonials}
                            onReorder={handleReorder}
                        >
                            {(testimonial) => (
                                <SortableItem
                                    key={testimonial.id}
                                    id={testimonial.id}
                                >
                                    <TestimonialListItem
                                        testimonial={testimonial}
                                        onEdit={() =>
                                            setEditingTestimonial(testimonial)
                                        }
                                    />
                                </SortableItem>
                            )}
                        </SortableList>
                    </div>
                )}
            </div>

            {editingTestimonial !== null && (
                <EditTestimonialModal
                    testimonial={editingTestimonial}
                    updateUrl={update.url({
                        testimonial: editingTestimonial.id,
                    })}
                    isOpen={editingTestimonial !== null}
                    onOpenChange={(open) => {
                        if (open === false) {
                            setEditingTestimonial(null);
                        }
                    }}
                />
            )}
        </StaffAreaLayout>
    );
}
