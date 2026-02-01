import HomeController from '@/actions/App/Http/Controllers/HomeController';
import ShowcaseEditController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseEditController';
import ShowcaseDraftEditController from '@/actions/App/Http/Controllers/Showcase/ManageShowcaseDraft/ShowcaseDraftEditController';
import ShowcaseDraftStoreController from '@/actions/App/Http/Controllers/Showcase/ManageShowcaseDraft/ShowcaseDraftStoreController';
import ShowcaseMonthIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseMonthIndexController';
import ShowcasePracticeAreaIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcasePracticeAreaIndexController';
import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import ApproveController from '@/actions/App/Http/Controllers/Staff/ShowcaseModeration/ApproveController';
import RejectController from '@/actions/App/Http/Controllers/Staff/ShowcaseModeration/RejectController';
import { type BreadcrumbItem } from '@/components/navigation/breadcrumbs';
import { ApprovalCelebrationModal } from '@/components/showcase/approval-celebration-modal';
import { ApproveShowcaseButton } from '@/components/showcase/approve-showcase-button';
import { RejectShowcaseModal } from '@/components/showcase/reject-showcase-modal';
import { RichTextContent } from '@/components/showcase/rich-text-content';
import { CreatorSection } from '@/components/showcase/show/creator-section';
import { ShowcaseBadges } from '@/components/showcase/show/showcase-badges';
import { ShowcaseGallery } from '@/components/showcase/show/showcase-gallery';
import { ShowcaseSidebar } from '@/components/showcase/show/showcase-sidebar';
import { ShowcaseStatusBadge } from '@/components/showcase/showcase-status-badge';
import { usePermissions } from '@/hooks/use-permissions';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Calendar, FilePenLine, Pencil } from 'lucide-react';
import { useState } from 'react';

function EditDraftButton({
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
            return 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-950 dark:text-red-400 dark:hover:bg-red-900';
        }
        if (draftStatus === 'Pending') {
            return 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-400 dark:hover:bg-amber-900';
        }
        return 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700';
    };

    const isDisabled = hasDraft && draftStatus !== 'Draft';

    return (
        <button
            onClick={handleClick}
            disabled={isDisabled}
            className={`inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-sm font-medium transition-colors ${getVariant()} ${isDisabled ? 'cursor-not-allowed opacity-75' : ''}`}
        >
            <FilePenLine className="size-4" />
            {getLabel()}
        </button>
    );
}

interface PublicShowProps {
    showcase: App.Http.Resources.Showcase.ShowcaseResource;
    lifetimeRank: number | null;
    monthlyRank: number | null;
    canEdit: boolean;
    canCreateDraft: boolean;
}

export default function PublicShow({
    showcase,
    lifetimeRank,
    monthlyRank,
    canEdit,
    canCreateDraft,
}: PublicShowProps) {
    const page = usePage<SharedData>();
    const { auth, appUrl, transformImages } = page.props;
    const { isAdmin, hasPermission } = usePermissions();
    const isAuthenticated = auth?.user !== undefined && auth?.user !== null;
    const isOwner = isAuthenticated && auth?.user?.id === showcase.user?.id;
    const canApproveReject = hasPermission('showcase.approve-reject');
    const canViewStatus = isOwner || isAdmin || canApproveReject;
    const canModerate =
        canApproveReject &&
        (showcase.status.name === 'Pending' ||
            showcase.status.name === 'Rejected');

    const [selectedImageIndex, setSelectedImageIndex] = useState(0);
    const [showCelebrationModal, setShowCelebrationModal] = useState(
        showcase.show_approval_celebration === true,
    );

    const images = Array.isArray(showcase.images) ? showcase.images : [];

    const formatDate = (dateString: string | null) => {
        if (dateString === null) {
            return null;
        }
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
        });
    };

    const getMonthKey = (dateString: string | null): string | null => {
        if (dateString === null) {
            return null;
        }
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${year}-${month}`;
    };

    const formatMonth = (monthKey: string): string => {
        const [year, monthNum] = monthKey.split('-');
        const date = new Date(parseInt(year), parseInt(monthNum) - 1);
        return date.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric',
        });
    };

    const monthKey = getMonthKey(showcase.submitted_date);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            label: 'Home',
            href: HomeController.url(),
        },
        ...(monthKey !== null
            ? [
                  {
                      label: formatMonth(monthKey),
                      href: ShowcaseMonthIndexController.url(monthKey),
                  },
              ]
            : []),
        { label: showcase.title },
    ];

    const breadcrumbActions = (
        <div className="flex flex-wrap items-center gap-3">
            {canViewStatus === true && (
                <ShowcaseStatusBadge status={showcase.status} />
            )}
            {canModerate === true && (
                <>
                    <ApproveShowcaseButton
                        showcase={showcase}
                        approveUrl={ApproveController.url(showcase.slug)}
                    />
                    {showcase.status.name === 'Pending' && (
                        <RejectShowcaseModal
                            showcase={showcase}
                            rejectUrl={RejectController.url(showcase.slug)}
                        />
                    )}
                </>
            )}
            {canEdit === true && (
                <Link
                    href={ShowcaseEditController.url({
                        showcase: showcase.slug,
                    })}
                    className="inline-flex items-center gap-1.5 rounded-md border border-neutral-200 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                >
                    <Pencil className="size-4" />
                    Edit
                </Link>
            )}
            {canCreateDraft === true && canEdit === false && (
                <EditDraftButton showcase={showcase} />
            )}
        </div>
    );

    return (
        <>
            <PublicLayout
                breadcrumbs={breadcrumbs}
                breadcrumbActions={breadcrumbActions}
            >
                <Head title={showcase.title}>
                    <meta
                        head-key="description"
                        name="description"
                        content={showcase.description?.slice(0, 400)}
                    />
                    <meta
                        head-key="og-type"
                        property="og:type"
                        content="article"
                    />
                    <meta
                        head-key="og-title"
                        property="og:title"
                        content={showcase.title}
                    />
                    <meta
                        head-key="og-image"
                        property="og:image"
                        content={images[0]?.url}
                    />
                    <meta
                        head-key="og-description"
                        property="og:description"
                        content={showcase.description?.slice(0, 400)}
                    />
                    <meta
                        head-key="og-url"
                        property="og:url"
                        content={`${appUrl}${ShowcaseShowController.url(showcase.slug)}`}
                    />
                    {showcase.user && (
                        <meta
                            head-key="og-author"
                            property="og:author"
                            content={`${showcase.user.first_name} ${showcase.user.last_name}`}
                        />
                    )}
                    {showcase.submitted_date && (
                        <meta
                            head-key="og-publish-time"
                            property="og:publish_time"
                            content={`${showcase.submitted_date}`}
                        />
                    )}
                </Head>

                <div className="mx-auto max-w-5xl px-4 py-12">
                    <div className="flex flex-col gap-8 lg:flex-row-reverse">
                        <ShowcaseSidebar
                            monthlyRank={monthlyRank}
                            lifetimeRank={lifetimeRank}
                            hasUpvoted={showcase.has_upvoted ?? false}
                            upvotesCount={showcase.upvotes_count ?? 0}
                            showcaseSlug={showcase.slug}
                            linkedinShareUrl={showcase.linkedin_share_url ?? ''}
                        />

                        {/* Main Content */}
                        <div className="flex-1">
                            {/* Date */}
                            {showcase.submitted_date !== null && (
                                <div className="mb-4 flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400">
                                    <Calendar className="size-4" />
                                    {formatDate(showcase.submitted_date)}
                                </div>
                            )}

                            {/* Title with Icon */}
                            <div className="mb-4 flex items-center gap-4">
                                {showcase.thumbnail_url !== null &&
                                showcase.thumbnail_url !== undefined ? (
                                    <img
                                        src={
                                            transformImages === true
                                                ? `${showcase.thumbnail_url}?w=100${showcase.thumbnail_rect_string !== null ? `&${showcase.thumbnail_rect_string}` : ''}`
                                                : showcase.thumbnail_url
                                        }
                                        alt={showcase.title}
                                        className="size-14 shrink-0 rounded-lg object-cover"
                                    />
                                ) : (
                                    <div className="flex size-14 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900">
                                        <span className="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                            {showcase.title.charAt(0)}
                                        </span>
                                    </div>
                                )}
                                <h1 className="text-3xl font-bold text-neutral-900 dark:text-white">
                                    {showcase.title}
                                </h1>
                            </div>

                            {/* Tagline */}
                            <p className="mb-4 text-lg text-neutral-600 dark:text-neutral-400">
                                {showcase.tagline}
                            </p>

                            {/* Badges */}
                            <ShowcaseBadges
                                user={showcase.user}
                                sourceStatus={showcase.source_status}
                                sourceUrl={showcase.source_url}
                                url={showcase.url}
                                videoUrl={showcase.video_url}
                            />

                            {/* Gallery */}
                            {(images.length > 0 ||
                                (showcase.youtube_id !== null &&
                                    showcase.youtube_id !== undefined)) && (
                                <ShowcaseGallery
                                    images={images}
                                    selectedIndex={selectedImageIndex}
                                    onSelectIndex={setSelectedImageIndex}
                                    fallbackAlt={showcase.title}
                                    youtubeId={showcase.youtube_id}
                                />
                            )}

                            {/* About Section */}
                            <section className="mb-8">
                                <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                                    About the Project
                                </h2>
                                <RichTextContent
                                    html={showcase.description_html ?? ''}
                                    className="rich-text-content"
                                />
                            </section>

                            {/* Practice Areas */}
                            {Array.isArray(showcase.practiceAreas) &&
                                showcase.practiceAreas.length > 0 && (
                                    <section className="mb-8">
                                        <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                                            Practice Areas
                                        </h2>
                                        <div className="flex flex-wrap gap-2">
                                            {showcase.practiceAreas.map(
                                                (pa) => (
                                                    <Link
                                                        key={pa.id}
                                                        href={ShowcasePracticeAreaIndexController.url(
                                                            {
                                                                practiceArea:
                                                                    pa.slug,
                                                            },
                                                        )}
                                                        className="rounded-full bg-neutral-100 px-3 py-1 text-sm text-neutral-700 transition hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                                                    >
                                                        {pa.name}
                                                    </Link>
                                                ),
                                            )}
                                        </div>
                                    </section>
                                )}

                            {/* Key Features Section */}
                            {showcase.key_features_html !== null &&
                                showcase.key_features_html !== undefined && (
                                    <section className="mb-8">
                                        <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                                            Key Features
                                        </h2>
                                        <RichTextContent
                                            html={showcase.key_features_html}
                                            className="rich-text-content"
                                        />
                                    </section>
                                )}

                            {/* Help Needed Section */}
                            {showcase.help_needed_html !== null &&
                                showcase.help_needed_html !== undefined && (
                                    <section className="mb-8">
                                        <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                                            Help Needed
                                        </h2>
                                        <RichTextContent
                                            html={showcase.help_needed_html}
                                            className="rich-text-content"
                                        />
                                    </section>
                                )}

                            {/* About the Creator Section */}
                            {showcase.user !== null &&
                                showcase.user !== undefined && (
                                    <CreatorSection user={showcase.user} />
                                )}
                        </div>
                    </div>
                </div>
            </PublicLayout>

            {showcase.show_approval_celebration === true &&
                showcase.linkedin_share_url !== null &&
                showcase.linkedin_share_url !== undefined && (
                    <ApprovalCelebrationModal
                        isOpen={showCelebrationModal}
                        onClose={() => setShowCelebrationModal(false)}
                        showcaseSlug={showcase.slug}
                        linkedInShareUrl={showcase.linkedin_share_url}
                    />
                )}
        </>
    );
}
