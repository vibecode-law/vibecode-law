import HeadingSmall from '@/components/heading/heading-small';
import { CreatePressCoverageModal } from '@/components/press-coverage/create-press-coverage-modal';
import { EditPressCoverageModal } from '@/components/press-coverage/edit-press-coverage-modal';
import { PressCoverageListItem } from '@/components/press-coverage/press-coverage-list-item';
import { SortableItem } from '@/components/ui/sortable-item';
import { SortableList } from '@/components/ui/sortable-list';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { reorder, store, update } from '@/routes/staff/press-coverage';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface PressCoverageIndexProps {
    pressCoverage: App.Http.Resources.PressCoverageResource[];
}

export default function PressCoverageIndex({
    pressCoverage,
}: PressCoverageIndexProps) {
    const [editingPressCoverage, setEditingPressCoverage] =
        useState<App.Http.Resources.PressCoverageResource | null>(null);
    const [localPressCoverage, setLocalPressCoverage] =
        useState(pressCoverage);

    // Sync local state when pressCoverage prop changes (after create/delete)
    useEffect(() => {
        setLocalPressCoverage(pressCoverage);
    }, [pressCoverage]);

    const handleReorder = (
        reorderedItems: App.Http.Resources.PressCoverageResource[],
    ) => {
        // Update local state immediately for instant feedback
        setLocalPressCoverage(reorderedItems);

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
            <Head title="Press Coverage" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Press Coverage"
                        description="Drag and drop to reorder press coverage"
                    />
                    <CreatePressCoverageModal storeUrl={store.url()} />
                </div>

                {localPressCoverage.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-neutral-300 py-12 text-center dark:border-neutral-700">
                        <p className="text-neutral-500 dark:text-neutral-400">
                            No press coverage yet
                        </p>
                    </div>
                ) : (
                    <div className="rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                        <SortableList
                            items={localPressCoverage}
                            onReorder={handleReorder}
                        >
                            {(article) => (
                                <SortableItem key={article.id} id={article.id}>
                                    <PressCoverageListItem
                                        pressCoverage={article}
                                        onEdit={() =>
                                            setEditingPressCoverage(article)
                                        }
                                    />
                                </SortableItem>
                            )}
                        </SortableList>
                    </div>
                )}
            </div>

            {editingPressCoverage !== null && (
                <EditPressCoverageModal
                    pressCoverage={editingPressCoverage}
                    updateUrl={update.url({
                        pressCoverage: editingPressCoverage.id,
                    })}
                    isOpen={editingPressCoverage !== null}
                    onOpenChange={(open) => {
                        if (open === false) {
                            setEditingPressCoverage(null);
                        }
                    }}
                />
            )}
        </StaffAreaLayout>
    );
}
