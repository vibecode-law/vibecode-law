import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import ShowcaseEditController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseEditController';
import ShowcaseDraftEditController from '@/actions/App/Http/Controllers/Showcase/ManageShowcaseDraft/ShowcaseDraftEditController';
import ShowcaseDraftStoreController from '@/actions/App/Http/Controllers/Showcase/ManageShowcaseDraft/ShowcaseDraftStoreController';
import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import HeadingSmall from '@/components/heading/heading-small';
import {
    ShowcaseListItem,
    ShowcaseStats,
} from '@/components/showcase/showcase-list-item';
import { ShowcaseSection } from '@/components/showcase/showcase-section';
import { Button } from '@/components/ui/button';
import UserAreaLayout from '@/layouts/user-area/layout';
import { Head, Link, router } from '@inertiajs/react';
import { FilePenLine, Plus } from 'lucide-react';

interface MyShowcasesProps {
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
}

type ShowcaseStatusName = 'Draft' | 'Pending' | 'Rejected' | 'Approved';

function getShowcasesByStatus(
    showcases: App.Http.Resources.Showcase.ShowcaseResource[],
    statuses: ShowcaseStatusName[],
) {
    return showcases.filter((s) =>
        statuses.includes(s.status.name as ShowcaseStatusName),
    );
}

function DraftButton({
    showcase,
}: {
    showcase: App.Http.Resources.Showcase.ShowcaseResource;
}) {
    const hasDraft = showcase.has_draft === true;
    const draftId = showcase.draft_id;
    const draftStatus = showcase.draft_status?.name;

    const handleClick = () => {
        if (hasDraft && draftStatus !== 'Draft') {
            return;
        } else if (hasDraft && draftId !== null && draftId !== undefined) {
            router.visit(ShowcaseDraftEditController.url({ draft: draftId }));
        } else {
            router.post(
                ShowcaseDraftStoreController.url({ showcase: showcase.slug }),
            );
        }
    };

    const getLabel = () => {
        if (!hasDraft) {
            return 'Edit';
        }
        if (draftStatus === 'Pending') {
            return 'Changes Pending';
        }
        if (draftStatus === 'Rejected') {
            return 'Changes Rejected';
        }
        return 'Continue Editing';
    };

    const getVariant = () => {
        if (draftStatus === 'Rejected') {
            return 'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950';
        }
        if (draftStatus === 'Pending') {
            return 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950';
        }
        return 'text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800';
    };

    return (
        <button
            onClick={handleClick}
            className={`flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium transition ${getVariant()}`}
        >
            <FilePenLine className="size-4" />
            {getLabel()}
        </button>
    );
}

export default function MyShowcases({ showcases }: MyShowcasesProps) {
    const pendingShowcases = getShowcasesByStatus(showcases, [
        'Pending',
        'Rejected',
    ]);
    const draftShowcases = getShowcasesByStatus(showcases, ['Draft']);
    const liveShowcases = getShowcasesByStatus(showcases, ['Approved']);

    return (
        <UserAreaLayout fullWidth>
            <Head title="My Showcases" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="My Showcases"
                        description="Manage your showcase submissions"
                    />
                    <Button asChild>
                        <Link href={ShowcaseCreateController.url()}>
                            <Plus className="size-4" />
                            New Showcase
                        </Link>
                    </Button>
                </div>

                <div className="space-y-4">
                    <ShowcaseSection
                        title="Pending Approval"
                        items={pendingShowcases}
                        emptyMessage="No showcases awaiting approval"
                    >
                        {(showcase) => (
                            <ShowcaseListItem
                                key={showcase.id}
                                showcase={showcase}
                                href={
                                    showcase.status.name === 'Rejected'
                                        ? ShowcaseEditController.url({
                                              showcase: showcase.slug,
                                          })
                                        : ShowcaseShowController.url({
                                              showcase: showcase.slug,
                                          })
                                }
                                linkIcon={
                                    showcase.status.name === 'Rejected'
                                        ? 'edit'
                                        : 'view'
                                }
                            />
                        )}
                    </ShowcaseSection>

                    <ShowcaseSection
                        title="Drafts"
                        items={draftShowcases}
                        emptyMessage="No draft showcases"
                    >
                        {(showcase) => (
                            <ShowcaseListItem
                                key={showcase.id}
                                showcase={showcase}
                                href={ShowcaseEditController.url({
                                    showcase: showcase.slug,
                                })}
                                linkIcon="edit"
                            />
                        )}
                    </ShowcaseSection>

                    <ShowcaseSection
                        title="Live"
                        items={liveShowcases}
                        emptyMessage="No live showcases yet"
                    >
                        {(showcase) => (
                            <ShowcaseListItem
                                key={showcase.id}
                                showcase={showcase}
                                href={ShowcaseShowController.url({
                                    showcase: showcase.slug,
                                })}
                                trailingSlot={
                                    <ShowcaseStats
                                        viewCount={showcase.view_count ?? 0}
                                        upvotesCount={
                                            showcase.upvotes_count ?? 0
                                        }
                                    />
                                }
                                actions={<DraftButton showcase={showcase} />}
                            />
                        )}
                    </ShowcaseSection>
                </div>
            </div>
        </UserAreaLayout>
    );
}
