import { type FrontendEnum } from '@/types';

/**
 * Normalized image interface that handles both showcase and draft images
 */
export interface NormalizedImage {
    /** Unique identifier for the gallery component */
    id: string;
    /** Display URL for the image */
    url: string;
    /** For showcase images: the showcase image ID. For draft kept images: the original showcase image ID */
    originalImageId: number | null;
    /** Whether this is a newly added image in a draft (action: 'add') */
    isNewDraftImage: boolean;
    /** The draft image ID (only for draft images that were newly added) */
    draftImageId: number | null;
}

/**
 * Mode of the showcase form
 */
export type ShowcaseFormMode = 'create' | 'edit-showcase' | 'edit-draft';

/**
 * Image deletion configuration for the form
 */
export interface ImageDeletionConfig {
    /** Field name for tracking removed original/kept images */
    removedImagesFieldName: string;
    /** Field name for tracking deleted new draft images (only for edit-draft mode) */
    deletedNewImagesFieldName?: string;
}

/**
 * Configuration for moderation actions
 */
export interface ModerationUrls {
    approveUrl?: string;
    rejectUrl?: string;
}

/**
 * Breadcrumb item
 */
export interface BreadcrumbItem {
    label: string;
    href?: string;
}

/**
 * Status information for display
 */
export interface ShowcaseStatusInfo {
    value: string;
    label: string;
    name: string | null;
}

/**
 * Normalized form data that works for all modes
 */
export interface ShowcaseFormData {
    title: string;
    slug: string;
    tagline: string;
    description: string;
    keyFeatures: string;
    helpNeeded: string;
    url: string;
    videoUrl: string;
    sourceStatus: string;
    sourceUrl: string;
    selectedPracticeAreaIds: (number | string)[];
    thumbnailUrl: string | null;
    thumbnailCrop: App.ValueObjects.ImageCrop | null;
    images: NormalizedImage[];
    status: ShowcaseStatusInfo | null;
    rejectionReason: string | null;
}

/**
 * Props for the ShowcaseForm component
 */
export interface ShowcaseFormProps {
    mode: ShowcaseFormMode;
    formAction: { action: string; method: string };
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

/**
 * Normalize a showcase resource to form data
 */
export function normalizeShowcase(
    showcase: App.Http.Resources.Showcase.ShowcaseResource | undefined,
): ShowcaseFormData {
    if (showcase === undefined) {
        return getDefaultFormData();
    }

    const images: NormalizedImage[] = (
        (showcase.images as
            | App.Http.Resources.Showcase.ShowcaseImageResource[]
            | undefined) ?? []
    ).map((img) => ({
        id: `showcase-${img.id}`,
        url: img.url,
        originalImageId: img.id,
        isNewDraftImage: false,
        draftImageId: null,
    }));

    return {
        title: showcase.title,
        slug: showcase.slug,
        tagline: showcase.tagline,
        description: showcase.description ?? '',
        keyFeatures: showcase.key_features ?? '',
        helpNeeded: showcase.help_needed ?? '',
        url: showcase.url ?? '',
        videoUrl: showcase.video_url ?? '',
        sourceStatus: String(showcase.source_status.value),
        sourceUrl: showcase.source_url ?? '',
        selectedPracticeAreaIds:
            (
                showcase.practiceAreas as
                    | App.Http.Resources.PracticeAreaResource[]
                    | undefined
            )?.map((pa) => pa.id) ?? [],
        thumbnailUrl: showcase.thumbnail_url,
        thumbnailCrop: showcase.thumbnail_crop ?? null,
        images,
        status: showcase.status,
        rejectionReason: showcase.rejection_reason ?? null,
    };
}

/**
 * Normalize a draft resource to form data
 */
export function normalizeDraft(
    draft: App.Http.Resources.Showcase.ShowcaseDraftResource,
): ShowcaseFormData {
    const draftImages =
        (draft.images as
            | App.Http.Resources.Showcase.ShowcaseDraftImageResource[]
            | undefined) ?? [];

    // Filter out images marked for removal (action: 'remove')
    const visibleImages = draftImages.filter((img) => img.action !== 'remove');

    const images: NormalizedImage[] = visibleImages.map((img) => ({
        id: `draft-${img.id}`,
        url: img.url ?? '',
        originalImageId: img.original_image_id,
        isNewDraftImage: img.action === 'add',
        draftImageId: img.action === 'add' ? img.id : null,
    }));

    return {
        title: draft.title,
        slug: '', // Drafts don't have slugs
        tagline: draft.tagline,
        description: draft.description ?? '',
        keyFeatures: draft.key_features ?? '',
        helpNeeded: draft.help_needed ?? '',
        url: draft.url ?? '',
        videoUrl: draft.video_url ?? '',
        sourceStatus: String(draft.source_status.value),
        sourceUrl: draft.source_url ?? '',
        selectedPracticeAreaIds:
            (
                draft.practiceAreas as
                    | App.Http.Resources.PracticeAreaResource[]
                    | undefined
            )?.map((pa) => pa.id) ?? [],
        thumbnailUrl: draft.thumbnail_url,
        thumbnailCrop: draft.thumbnail_crop ?? null,
        images,
        status: draft.status,
        rejectionReason: draft.rejection_reason ?? null,
    };
}

/**
 * Get default form data for a new showcase
 */
export function getDefaultFormData(): ShowcaseFormData {
    return {
        title: '',
        slug: '',
        tagline: '',
        description: '',
        keyFeatures: '',
        helpNeeded: '',
        url: '',
        videoUrl: '',
        sourceStatus: '1',
        sourceUrl: '',
        selectedPracticeAreaIds: [],
        thumbnailUrl: null,
        thumbnailCrop: null,
        images: [],
        status: null,
        rejectionReason: null,
    };
}
