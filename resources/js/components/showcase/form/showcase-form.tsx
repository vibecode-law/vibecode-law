import { PublicFooter } from '@/components/layout/public-footer';
import { PublicHeader } from '@/components/layout/public-header';
import { Breadcrumbs } from '@/components/navigation/breadcrumbs';
import { ApproveShowcaseButton } from '@/components/showcase/approve-showcase-button';
import { RejectShowcaseModal } from '@/components/showcase/reject-showcase-modal';
import { ShowcaseStatusBadge } from '@/components/showcase/showcase-status-badge';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { FancySelect } from '@/components/ui/fancy-select';
import { FancyTextInput } from '@/components/ui/fancy-text-input';
import { ImageUploadGallery } from '@/components/ui/image-upload-gallery';
import {
    InfoBox,
    InfoBoxDescription,
    InfoBoxTitle,
} from '@/components/ui/info-box';
import { InlineRichText } from '@/components/ui/inline/inline-rich-text';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { PillSelect } from '@/components/ui/pill-select';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { slugify } from '@/lib/slug';
import { type FrontendEnum } from '@/types';
import { Form, Head } from '@inertiajs/react';
import {
    AlertCircle,
    Code,
    FileText,
    Globe,
    Hash,
    HelpCircle,
    Image,
    Images,
    LinkIcon,
    List,
    Quote,
    Tags,
    Trophy,
    Type,
    Video,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { SaveButtonGroup } from './save-button-group';
import {
    type BreadcrumbItem,
    type ChallengeContext,
    type ImageDeletionConfig,
    type ModerationUrls,
    type ShowcaseFormData,
    type ShowcaseFormMode,
} from './types';

type HttpMethod = 'get' | 'post' | 'put' | 'patch' | 'delete';

interface ShowcaseFormProps {
    mode: ShowcaseFormMode;
    formAction: { action: string; method: HttpMethod };
    initialData: ShowcaseFormData;
    practiceAreas: App.Http.Resources.PracticeAreaResource[];
    sourceStatuses: FrontendEnum<number>[];
    imageDeletionConfig: ImageDeletionConfig;
    moderationUrls?: ModerationUrls;
    previewUrl?: string;
    breadcrumbs: BreadcrumbItem[];
    pageTitle: string;
    showSlugField: boolean;
    canSubmit: boolean;
    challenge?: ChallengeContext;
}

export function ShowcaseForm({
    mode,
    formAction,
    initialData,
    practiceAreas,
    sourceStatuses,
    imageDeletionConfig,
    moderationUrls,
    previewUrl,
    breadcrumbs,
    pageTitle,
    showSlugField,
    canSubmit,
    challenge,
}: ShowcaseFormProps) {
    const [title, setTitle] = useState(initialData.title);
    const [slug, setSlug] = useState(initialData.slug);
    const [tagline, setTagline] = useState(initialData.tagline);
    const [description, setDescription] = useState(initialData.description);
    const [keyFeatures, setKeyFeatures] = useState(initialData.keyFeatures);
    const [helpNeeded, setHelpNeeded] = useState(initialData.helpNeeded);
    const [url, setUrl] = useState(initialData.url);
    const [videoUrl, setVideoUrl] = useState(initialData.videoUrl);
    const [sourceStatus, setSourceStatus] = useState<string>(
        initialData.sourceStatus,
    );
    const [sourceUrl, setSourceUrl] = useState(initialData.sourceUrl);
    const [selectedPracticeAreas, setSelectedPracticeAreas] = useState<
        (number | string)[]
    >(initialData.selectedPracticeAreaIds);
    const [activeChallenge, setActiveChallenge] = useState(challenge);

    const showSourceUrl = sourceStatus === '2' || sourceStatus === '3';

    const saveButtonText =
        initialData.status !== null &&
        ['Pending', 'Approved'].includes(initialData.status.name ?? '')
            ? 'Save'
            : 'Save Draft';

    const showModeration =
        moderationUrls !== undefined &&
        (moderationUrls.approveUrl !== undefined ||
            moderationUrls.rejectUrl !== undefined);

    return (
        <>
            <Head title={pageTitle} />

            <div className="flex min-h-screen flex-col bg-white dark:bg-neutral-950">
                <PublicHeader />

                <main className="flex-1">
                    <Form
                        {...formAction}
                        onSuccess={() => {
                            if (typeof window === 'undefined') return;

                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }}
                        onError={() => {
                            if (typeof window === 'undefined') return;

                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }}
                        className="flex-1"
                    >
                        {({
                            processing,
                            errors,
                            hasErrors,
                            recentlySuccessful,
                        }) => {
                            const imageErrors = Object.fromEntries(
                                Object.entries(errors).filter(([key]) =>
                                    key.startsWith('images.'),
                                ),
                            );
                            return (
                                <div className="mx-auto max-w-5xl px-4 py-8">
                                    {/* Breadcrumbs */}
                                    <div className="mb-6">
                                        <Breadcrumbs items={breadcrumbs} />
                                    </div>

                                    {/* Validation Errors Alert */}
                                    {hasErrors === true && (
                                        <InfoBox
                                            variant="error"
                                            icon={
                                                <AlertCircle className="size-5 text-red-600 dark:text-red-400" />
                                            }
                                            className="mb-6"
                                        >
                                            <InfoBoxTitle className="text-red-800 dark:text-red-200">
                                                Validation errors
                                            </InfoBoxTitle>
                                            <InfoBoxDescription>
                                                Please fix the validation errors
                                                highlighted below before saving.
                                            </InfoBoxDescription>
                                        </InfoBox>
                                    )}

                                    {/* Rejection Reason Alert */}
                                    {initialData.rejectionReason !== null && (
                                        <InfoBox
                                            variant="error"
                                            icon={
                                                <AlertCircle className="size-5 text-red-600 dark:text-red-400" />
                                            }
                                            className="mb-6"
                                        >
                                            <InfoBoxTitle className="text-red-800 dark:text-red-200">
                                                {mode === 'edit-draft'
                                                    ? 'Draft Rejected'
                                                    : 'Showcase Rejected'}
                                            </InfoBoxTitle>
                                            <InfoBoxDescription>
                                                {initialData.rejectionReason}
                                            </InfoBoxDescription>
                                        </InfoBox>
                                    )}

                                    {/* Header with Save Button */}
                                    <div className="flex items-center justify-between">
                                        <div className="flex flex-wrap items-center gap-3">
                                            <h1 className="text-2xl font-bold text-neutral-900 dark:text-white">
                                                {pageTitle}
                                            </h1>
                                            {initialData.status !== null && (
                                                <ShowcaseStatusBadge
                                                    status={initialData.status}
                                                />
                                            )}
                                            {showModeration === true && (
                                                <>
                                                    {moderationUrls?.approveUrl !==
                                                        undefined && (
                                                        <ApproveShowcaseButton
                                                            showcase={{
                                                                title: initialData.title,
                                                            }}
                                                            approveUrl={
                                                                moderationUrls.approveUrl
                                                            }
                                                        />
                                                    )}
                                                    {moderationUrls?.rejectUrl !==
                                                        undefined && (
                                                        <RejectShowcaseModal
                                                            showcase={{
                                                                title: initialData.title,
                                                            }}
                                                            rejectUrl={
                                                                moderationUrls.rejectUrl
                                                            }
                                                        />
                                                    )}
                                                </>
                                            )}
                                        </div>
                                        <SaveButtonGroup
                                            recentlySuccessful={
                                                recentlySuccessful
                                            }
                                            processing={processing}
                                            saveButtonText={saveButtonText}
                                            showSubmitButton={canSubmit}
                                            className="hidden items-center gap-3 lg:flex"
                                            previewUrl={previewUrl}
                                        />
                                    </div>

                                    <div className="mx-auto mt-8 max-w-3xl space-y-8">
                                        {/* Challenge tip */}
                                        {activeChallenge !== undefined && (
                                            <InfoBox
                                                variant="info"
                                                icon={
                                                    <Trophy className="size-5" />
                                                }
                                            >
                                                <div className="flex items-center justify-between gap-2">
                                                    <p>
                                                        You&apos;re entering the{' '}
                                                        <strong>
                                                            {
                                                                activeChallenge.title
                                                            }
                                                        </strong>{' '}
                                                        challenge.
                                                    </p>
                                                    <AlertDialog>
                                                        <AlertDialogTrigger
                                                            asChild
                                                        >
                                                            <button
                                                                type="button"
                                                                className="shrink-0 rounded-md p-1 hover:bg-blue-100 dark:hover:bg-blue-800/40"
                                                                aria-label="Remove challenge"
                                                            >
                                                                <X className="size-4" />
                                                            </button>
                                                        </AlertDialogTrigger>
                                                        <AlertDialogContent>
                                                            <AlertDialogHeader>
                                                                <AlertDialogTitle>
                                                                    Remove
                                                                    challenge?
                                                                </AlertDialogTitle>
                                                                <AlertDialogDescription>
                                                                    Your
                                                                    showcase
                                                                    will no
                                                                    longer be
                                                                    linked to
                                                                    the{' '}
                                                                    <strong>
                                                                        {
                                                                            activeChallenge.title
                                                                        }
                                                                    </strong>{' '}
                                                                    challenge.
                                                                </AlertDialogDescription>
                                                            </AlertDialogHeader>
                                                            <AlertDialogFooter>
                                                                <AlertDialogCancel>
                                                                    Keep
                                                                </AlertDialogCancel>
                                                                <AlertDialogAction
                                                                    onClick={() =>
                                                                        setActiveChallenge(
                                                                            undefined,
                                                                        )
                                                                    }
                                                                >
                                                                    Remove
                                                                </AlertDialogAction>
                                                            </AlertDialogFooter>
                                                        </AlertDialogContent>
                                                    </AlertDialog>
                                                </div>
                                                <input
                                                    type="hidden"
                                                    name="challenge_id"
                                                    value={activeChallenge.id}
                                                />
                                                <InputError
                                                    message={
                                                        errors.challenge_id
                                                    }
                                                />
                                            </InfoBox>
                                        )}

                                        {/* Title Section */}
                                        <FancyTextInput
                                            name="title"
                                            value={title}
                                            onChange={(e) =>
                                                setTitle(e.target.value)
                                            }
                                            placeholder="Enter your project title..."
                                            error={errors.title}
                                            label="Project Title"
                                            labelIcon={
                                                <Type className="size-5" />
                                            }
                                            required
                                        />

                                        {/* Tagline */}
                                        <FancyTextInput
                                            name="tagline"
                                            value={tagline}
                                            onChange={(e) =>
                                                setTagline(e.target.value)
                                            }
                                            placeholder="A short, catchy description of your project..."
                                            error={errors.tagline}
                                            label="Tagline"
                                            labelIcon={
                                                <Quote className="size-5" />
                                            }
                                            required
                                        />

                                        {/* Gallery - key resets state when images change (e.g., after save) */}
                                        <ImageUploadGallery
                                            key={initialData.images
                                                .map((img) => img.id)
                                                .join(',')}
                                            name="images"
                                            label="Gallery"
                                            labelIcon={
                                                <Images className="size-5" />
                                            }
                                            description="Up to 10 images. Min 400x225px and max 4MB per image."
                                            existingImages={initialData.images}
                                            removedImagesFieldName={
                                                imageDeletionConfig.removedImagesFieldName
                                            }
                                            deletedNewImagesFieldName={
                                                imageDeletionConfig.deletedNewImagesFieldName
                                            }
                                            error={
                                                errors.images as
                                                    | string
                                                    | undefined
                                            }
                                            imageErrors={imageErrors}
                                        />

                                        {/* About Section */}
                                        <InlineRichText
                                            name="description"
                                            value={description}
                                            onChange={setDescription}
                                            label="About the Project"
                                            labelIcon={
                                                <FileText className="size-5" />
                                            }
                                            placeholder="Tell us about your project. What does it do? What problem does it solve?"
                                            height={250}
                                            error={errors.description}
                                            required
                                        />

                                        {/* Practice Areas */}
                                        <PillSelect
                                            name="practice_area_ids"
                                            label="Practice Areas"
                                            labelIcon={
                                                <Tags className="size-5" />
                                            }
                                            options={practiceAreas.map(
                                                (pa) => ({
                                                    value: pa.id,
                                                    label: pa.name,
                                                }),
                                            )}
                                            selected={selectedPracticeAreas}
                                            onChange={setSelectedPracticeAreas}
                                            placeholder="Select at least one practice area"
                                            error={
                                                errors.practice_area_ids as
                                                    | string
                                                    | undefined
                                            }
                                            required
                                        />

                                        {/* Key Features */}
                                        <InlineRichText
                                            name="key_features"
                                            value={keyFeatures}
                                            onChange={setKeyFeatures}
                                            label="Key Features"
                                            labelIcon={
                                                <List className="size-5" />
                                            }
                                            placeholder="List the main features of your project..."
                                            height={200}
                                            error={errors.key_features}
                                            required
                                        />

                                        {/* Slug field (moderators only) */}
                                        {showSlugField === true && (
                                            <div className="space-y-2">
                                                <Label
                                                    htmlFor="slug"
                                                    className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white"
                                                >
                                                    <Hash className="size-5" />
                                                    URL Slug *
                                                </Label>
                                                <Input
                                                    id="slug"
                                                    name="slug"
                                                    value={slug}
                                                    onChange={(e) =>
                                                        setSlug(e.target.value)
                                                    }
                                                    onBlur={(e) =>
                                                        setSlug(
                                                            slugify(
                                                                e.target.value,
                                                            ),
                                                        )
                                                    }
                                                    placeholder="my-awesome-project"
                                                    aria-invalid={
                                                        errors.slug !==
                                                        undefined
                                                    }
                                                />
                                                <InputError
                                                    message={errors.slug}
                                                />
                                            </div>
                                        )}

                                        {/* Optional Fields Section */}
                                        <fieldset className="rounded-xl border border-neutral-200 p-6 dark:border-neutral-800">
                                            <legend className="px-2 text-sm font-medium text-neutral-500 dark:text-neutral-400">
                                                Optional
                                            </legend>

                                            <div className="space-y-6">
                                                {/* Thumbnail */}
                                                <div className="space-y-2">
                                                    <Label className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                                                        <Image className="size-5" />
                                                        Thumbnail
                                                    </Label>
                                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                                        A square image used in
                                                        showcase listings. Min
                                                        100×100px, max 2MB.
                                                    </p>
                                                    <ThumbnailSelector
                                                        name="thumbnail"
                                                        currentOriginalUrl={
                                                            initialData.thumbnailUrl ??
                                                            undefined
                                                        }
                                                        currentCropData={
                                                            initialData.thumbnailCrop ??
                                                            undefined
                                                        }
                                                        error={errors.thumbnail}
                                                        size="md"
                                                    />
                                                </div>
                                                {/* Video URL */}
                                                <FancyTextInput
                                                    name="video_url"
                                                    type="url"
                                                    value={videoUrl}
                                                    onChange={(e) =>
                                                        setVideoUrl(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="https://youtube.com/..."
                                                    label="Video URL"
                                                    labelIcon={
                                                        <Video className="size-5" />
                                                    }
                                                    description="YouTube videos will display embedded in your showcase page. Other platforms will just be links."
                                                    error={errors.video_url}
                                                    showOptionalLabel={false}
                                                />
                                                {/* Demo URL */}
                                                <FancyTextInput
                                                    name="url"
                                                    type="url"
                                                    value={url}
                                                    onChange={(e) =>
                                                        setUrl(e.target.value)
                                                    }
                                                    placeholder="https://your-project.com"
                                                    label="Demo URL"
                                                    labelIcon={
                                                        <Globe className="size-5" />
                                                    }
                                                    description="Link to a prototype/demo of your project, rather than a marketing or landing page."
                                                    error={errors.url}
                                                    showOptionalLabel={false}
                                                />
                                                {/* Help Needed */}
                                                <InlineRichText
                                                    name="help_needed"
                                                    value={helpNeeded}
                                                    onChange={setHelpNeeded}
                                                    label="Help Needed"
                                                    labelIcon={
                                                        <HelpCircle className="size-5" />
                                                    }
                                                    placeholder="Are you looking for collaborators, feedback, or specific help?"
                                                    height={200}
                                                    error={errors.help_needed}
                                                    showOptionalLabel={false}
                                                />
                                                {/* Source Code Status */}
                                                <FancySelect
                                                    name="source_status"
                                                    value={sourceStatus}
                                                    onValueChange={
                                                        setSourceStatus
                                                    }
                                                    options={sourceStatuses.map(
                                                        (status) => ({
                                                            value: String(
                                                                status.value,
                                                            ),
                                                            label: status.label,
                                                        }),
                                                    )}
                                                    placeholder="Select source code availability..."
                                                    label="Source Code Availability"
                                                    labelIcon={
                                                        <Code className="size-5" />
                                                    }
                                                    description="Is the code for your project available to the public (e.g. on Github)? If not, leave this as 'Not Available'."
                                                    error={errors.source_status}
                                                    showOptionalLabel={false}
                                                />

                                                {showSourceUrl === true && (
                                                    <div className="space-y-2">
                                                        <Label
                                                            htmlFor="source_url"
                                                            className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white"
                                                        >
                                                            <LinkIcon className="size-5" />
                                                            Source URL *
                                                        </Label>
                                                        <Input
                                                            id="source_url"
                                                            name="source_url"
                                                            type="url"
                                                            value={sourceUrl}
                                                            onChange={(e) =>
                                                                setSourceUrl(
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            placeholder="https://github.com/..."
                                                            aria-invalid={
                                                                errors.source_url !==
                                                                undefined
                                                            }
                                                        />
                                                        <InputError
                                                            message={
                                                                errors.source_url
                                                            }
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                        </fieldset>

                                        {/* Bottom Save Button - Desktop */}
                                        <SaveButtonGroup
                                            recentlySuccessful={
                                                recentlySuccessful
                                            }
                                            processing={processing}
                                            saveButtonText={saveButtonText}
                                            showSubmitButton={canSubmit}
                                            className="hidden items-center justify-end gap-3 pt-3 lg:flex"
                                            size="lg"
                                        />
                                    </div>

                                    {/* Bottom Save Button - Mobile */}
                                    <div className="mx-auto max-w-3xl">
                                        <SaveButtonGroup
                                            recentlySuccessful={
                                                recentlySuccessful
                                            }
                                            processing={processing}
                                            saveButtonText={saveButtonText}
                                            showSubmitButton={canSubmit}
                                            className="flex items-center justify-end gap-3 pt-6 lg:hidden"
                                            size="lg"
                                        />
                                    </div>
                                </div>
                            );
                        }}
                    </Form>
                </main>

                <PublicFooter />
            </div>
        </>
    );
}
