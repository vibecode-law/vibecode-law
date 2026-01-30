import { PublicFooter } from '@/components/layout/public-footer';
import { PublicHeader } from '@/components/layout/public-header';
import { Breadcrumbs } from '@/components/navigation/breadcrumbs';
import { ApproveShowcaseButton } from '@/components/showcase/approve-showcase-button';
import { RejectShowcaseModal } from '@/components/showcase/reject-showcase-modal';
import { ShowcaseStatusBadge } from '@/components/showcase/showcase-status-badge';
import { ImageUploadGallery } from '@/components/ui/image-upload-gallery';
import {
    InfoBox,
    InfoBoxDescription,
    InfoBoxTitle,
} from '@/components/ui/info-box';
import { InlineRichText } from '@/components/ui/inline/inline-rich-text';
import { InlineText } from '@/components/ui/inline/inline-text';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { PillSelect } from '@/components/ui/pill-select';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
    Tags,
    Video,
} from 'lucide-react';
import { useState } from 'react';
import { SaveButtonGroup } from './save-button-group';
import {
    type BreadcrumbItem,
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
                                        {/* Title Section */}
                                        <div>
                                            <InlineText
                                                name="title"
                                                value={title}
                                                onChange={(e) =>
                                                    setTitle(e.target.value)
                                                }
                                                placeholder="Enter your project title..."
                                                textClasses="text-xl lg:text-3xl"
                                                weight="bold"
                                                error={errors.title}
                                                label="Project Title"
                                            />
                                        </div>

                                        {/* Tagline */}
                                        <div>
                                            <InlineText
                                                name="tagline"
                                                value={tagline}
                                                onChange={(e) =>
                                                    setTagline(e.target.value)
                                                }
                                                placeholder="A short, catchy description of your project..."
                                                textClasses="text-base lg:text-xl"
                                                className="text-neutral-600 dark:text-neutral-400"
                                                error={errors.tagline}
                                                label="Tagline"
                                            />
                                        </div>

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
                                                        showcase listings.
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
                                                <div className="space-y-2">
                                                    <Label
                                                        htmlFor="video_url"
                                                        className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white"
                                                    >
                                                        <Video className="size-5" />
                                                        Video URL
                                                    </Label>
                                                    <Input
                                                        id="video_url"
                                                        name="video_url"
                                                        type="url"
                                                        value={videoUrl}
                                                        onChange={(e) =>
                                                            setVideoUrl(
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="https://youtube.com/..."
                                                        aria-invalid={
                                                            errors.video_url !==
                                                            undefined
                                                        }
                                                    />
                                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                                        YouTube videos will
                                                        display embedded in your
                                                        showcase page. Other
                                                        platforms will just be
                                                        links.
                                                    </p>
                                                    <InputError
                                                        message={
                                                            errors.video_url
                                                        }
                                                    />
                                                </div>
                                                {/* Demo URL */}
                                                <div className="space-y-2">
                                                    <Label
                                                        htmlFor="url"
                                                        className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white"
                                                    >
                                                        <Globe className="size-5" />
                                                        Demo URL
                                                    </Label>
                                                    <Input
                                                        id="url"
                                                        name="url"
                                                        type="url"
                                                        value={url}
                                                        onChange={(e) =>
                                                            setUrl(
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="https://your-project.com"
                                                        aria-invalid={
                                                            errors.url !==
                                                            undefined
                                                        }
                                                    />
                                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                                        Link to a prototype/demo
                                                        of your project, rather
                                                        than a marketing or
                                                        landing page.
                                                    </p>
                                                    <InputError
                                                        message={errors.url}
                                                    />
                                                </div>
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
                                                    required
                                                />
                                                {/* Source Code Status */}
                                                <div className="space-y-2">
                                                    <Label
                                                        htmlFor="source_status"
                                                        className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white"
                                                    >
                                                        <Code className="size-5" />
                                                        Source Code Availability
                                                    </Label>
                                                    <Select
                                                        name="source_status"
                                                        value={sourceStatus}
                                                        onValueChange={
                                                            setSourceStatus
                                                        }
                                                    >
                                                        <SelectTrigger
                                                            aria-invalid={
                                                                errors.source_status !==
                                                                undefined
                                                            }
                                                        >
                                                            <SelectValue placeholder="Select status" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {sourceStatuses.map(
                                                                (status) => (
                                                                    <SelectItem
                                                                        key={
                                                                            status.value
                                                                        }
                                                                        value={String(
                                                                            status.value,
                                                                        )}
                                                                    >
                                                                        {
                                                                            status.label
                                                                        }
                                                                    </SelectItem>
                                                                ),
                                                            )}
                                                        </SelectContent>
                                                    </Select>
                                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                                        Is the code for your
                                                        project available to the
                                                        public (e.g. on Github)?
                                                        If not, leave this as
                                                        "Not Available".
                                                    </p>
                                                    <InputError
                                                        message={
                                                            errors.source_status
                                                        }
                                                    />
                                                </div>

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
                                            className="hidden items-center justify-end gap-3 pt-6 lg:flex"
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
