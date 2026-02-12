import ShowcaseStoreController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseStoreController';
import ShowcaseUpdateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseUpdateController';
import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import ApproveController from '@/actions/App/Http/Controllers/Staff/ShowcaseModeration/ApproveController';
import RejectController from '@/actions/App/Http/Controllers/Staff/ShowcaseModeration/RejectController';
import UserShowcaseIndexController from '@/actions/App/Http/Controllers/User/UserShowcaseIndexController';
import { ShowcaseForm } from '@/components/showcase/form/showcase-form';
import { normalizeShowcase } from '@/components/showcase/form/types';
import { usePermissions } from '@/hooks/use-permissions';
import { home } from '@/routes';
import { type FrontendEnum } from '@/types';

interface ChallengeContext {
    id: number;
    title: string;
    slug: string;
}

interface CreateProps {
    showcase?: App.Http.Resources.Showcase.ShowcaseResource;
    practiceAreas: App.Http.Resources.PracticeAreaResource[];
    sourceStatuses: FrontendEnum<number>[];
    challenge?: ChallengeContext | null;
}

export default function Create(props: CreateProps) {
    // Use key to reset all form state when showcase changes (e.g., after creation redirects to edit)
    return <CreateWrapper key={props.showcase?.id ?? 'create'} {...props} />;
}

function CreateWrapper({
    showcase,
    practiceAreas,
    sourceStatuses,
    challenge,
}: CreateProps) {
    const { hasPermission } = usePermissions();
    const isEditing = showcase !== undefined;

    // Determine permissions and capabilities
    const canApproveReject = hasPermission('showcase.approve-reject');
    const canModerate =
        canApproveReject &&
        isEditing &&
        (showcase.status.name === 'Pending' ||
            showcase.status.name === 'Rejected');

    // Slug can only be edited by mods/admins on non-approved showcases
    const canEditSlug =
        isEditing && canApproveReject && showcase.status.name !== 'Approved';

    // Can submit if creating new, or if editing a draft/rejected showcase
    const canSubmit =
        isEditing === false ||
        showcase.status.name === 'Draft' ||
        showcase.status.name === 'Rejected';

    // Build form action
    const formAction = isEditing
        ? ShowcaseUpdateController.form(showcase.slug)
        : ShowcaseStoreController.form();

    // Normalize data for the form
    const initialData = normalizeShowcase(showcase);

    // Build breadcrumbs
    const breadcrumbs = [
        { label: 'Home', href: home.url() },
        {
            label: 'My Draft Showcases',
            href: UserShowcaseIndexController.url(),
        },
        {
            label: isEditing ? `Editing ${showcase.title}` : 'Create Showcase',
        },
    ];

    // Build page title
    const pageTitle = isEditing ? 'Edit Showcase' : 'Create New Showcase';

    // Build moderation URLs
    const moderationUrls = canModerate
        ? {
              approveUrl: ApproveController.url(showcase.slug),
              rejectUrl:
                  showcase.status.name === 'Pending'
                      ? RejectController.url(showcase.slug)
                      : undefined,
          }
        : undefined;

    // Build preview URL
    const previewUrl = isEditing
        ? ShowcaseShowController.url({ showcase: showcase.slug })
        : undefined;

    return (
        <ShowcaseForm
            mode={isEditing ? 'edit-showcase' : 'create'}
            formAction={formAction}
            initialData={initialData}
            practiceAreas={practiceAreas}
            sourceStatuses={sourceStatuses}
            imageDeletionConfig={{
                removedImagesFieldName: 'removed_images',
            }}
            moderationUrls={moderationUrls}
            previewUrl={previewUrl}
            breadcrumbs={breadcrumbs}
            pageTitle={pageTitle}
            showSlugField={canEditSlug}
            canSubmit={canSubmit}
            challenge={challenge ?? undefined}
        />
    );
}
