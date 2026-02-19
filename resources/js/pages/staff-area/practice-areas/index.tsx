import HeadingSmall from '@/components/heading/heading-small';
import { CreatePracticeAreaModal } from '@/components/practice-area/create-practice-area-modal';
import { EditPracticeAreaModal } from '@/components/practice-area/edit-practice-area-modal';
import { PracticeAreaListItem } from '@/components/practice-area/practice-area-list-item';
import { ShowcaseSection } from '@/components/showcase/showcase-section';
import { MetadataSubNav } from '@/components/staff/metadata-sub-nav';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { store, update } from '@/routes/staff/metadata/practice-areas';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

interface PracticeAreasIndexProps {
    practiceAreas: App.Http.Resources.PracticeAreaResource[];
}

export default function PracticeAreasIndex({
    practiceAreas,
}: PracticeAreasIndexProps) {
    const [editingPracticeArea, setEditingPracticeArea] =
        useState<App.Http.Resources.PracticeAreaResource | null>(null);

    return (
        <StaffAreaLayout fullWidth>
            <Head title="Practice Areas" />

            <MetadataSubNav />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Practice Areas"
                        description="Manage practice areas for showcases"
                    />
                    <CreatePracticeAreaModal storeUrl={store.url()} />
                </div>

                <ShowcaseSection
                    title="All Practice Areas"
                    items={practiceAreas}
                    emptyMessage="No practice areas yet"
                >
                    {(practiceArea) => (
                        <PracticeAreaListItem
                            key={practiceArea.id}
                            practiceArea={practiceArea}
                            onEdit={() => setEditingPracticeArea(practiceArea)}
                        />
                    )}
                </ShowcaseSection>
            </div>

            {editingPracticeArea !== null && (
                <EditPracticeAreaModal
                    practiceArea={editingPracticeArea}
                    updateUrl={update.url({
                        practiceArea: editingPracticeArea.id,
                    })}
                    isOpen={editingPracticeArea !== null}
                    onOpenChange={(open) => {
                        if (open === false) {
                            setEditingPracticeArea(null);
                        }
                    }}
                />
            )}
        </StaffAreaLayout>
    );
}
