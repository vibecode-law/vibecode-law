import { OrganisationSearchSelect } from '@/components/challenges/organisation-search-select';
import { CreateOrganisationModal } from '@/components/organisation/create-organisation-modal';
import { EditOrganisationModal } from '@/components/organisation/edit-organisation-modal';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { type SimpleCropData } from '@/components/ui/image-crop-modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { CHALLENGE_VISIBILITY } from '@/lib/challenge-utils';
import { slugify } from '@/lib/slug';
import { Pencil } from 'lucide-react';
import { useRef, useState } from 'react';

interface Organisation {
    id: number;
    name: string;
    tagline?: string | null;
    about?: string | null;
    thumbnail_url?: string | null;
    thumbnail_crops?: Record<string, SimpleCropData> | null;
}

interface ChallengeFormFieldsProps {
    processing: boolean;
    errors: Record<string, string>;
    defaultValues?: {
        title?: string;
        slug?: string;
        tagline?: string;
        description?: string;
        involvement_instructions?: string | null;
        participant_instructions?: string | null;
        starts_at?: string | null;
        ends_at?: string | null;
        is_active?: boolean;
        is_featured?: boolean;
        live_view_enabled?: boolean;
        live_view_access_token?: string | null;
        live_view_heading?: string | null;
        live_view_subheading?: string | null;
        visibility?: number;
        organisation?: Organisation | null;
        thumbnail_url?: string | null;
        thumbnail_crops?: Record<string, SimpleCropData> | null;
    };
    visibilityOptions: App.ValueObjects.FrontendEnum[];
    mode: 'create' | 'edit';
}

export default function ChallengeFormFields({
    processing,
    errors,
    defaultValues,
    visibilityOptions,
    mode,
}: ChallengeFormFieldsProps) {
    const [autoSlug, setAutoSlug] = useState(mode === 'create');
    const [selectedOrganisation, setSelectedOrganisation] =
        useState<Organisation | null>(defaultValues?.organisation ?? null);
    const [hasOrganisation, setHasOrganisation] = useState(
        defaultValues?.organisation !== null &&
            defaultValues?.organisation !== undefined,
    );
    const [orgKey, setOrgKey] = useState(0);
    const [isEditOrgOpen, setIsEditOrgOpen] = useState(false);
    const [activeChecked, setActiveChecked] = useState(
        defaultValues?.is_active ?? false,
    );
    const [featuredChecked, setFeaturedChecked] = useState(
        defaultValues?.is_featured ?? false,
    );
    const [liveViewEnabled, setLiveViewEnabled] = useState(
        defaultValues?.live_view_enabled ?? false,
    );
    const [visibility, setVisibility] = useState(
        String(defaultValues?.visibility ?? 1),
    );
    const slugRef = useRef<HTMLInputElement>(null);

    const isInviteToView =
        visibility === String(CHALLENGE_VISIBILITY.InviteToViewAndSubmit);
    const requiresInvite =
        visibility === String(CHALLENGE_VISIBILITY.InviteToSubmit) ||
        isInviteToView;
    const featuredEnabled =
        featuredChecked === true && isInviteToView === false;

    const handleVisibilityChange = (value: string) => {
        setVisibility(value);

        if (value === String(CHALLENGE_VISIBILITY.InviteToViewAndSubmit)) {
            setFeaturedChecked(false);
        }
    };

    const handleTitleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        if (autoSlug === true && slugRef.current !== null) {
            slugRef.current.value = slugify(event.target.value);
        }
    };

    const handleSlugChange = () => {
        setAutoSlug(false);
    };

    const isActive = mode === 'edit' && defaultValues?.is_active === true;

    return (
        <div className="space-y-6">
            <div className="grid items-start gap-4 sm:grid-cols-2">
                <FormField
                    label="Title"
                    htmlFor="title"
                    error={errors.title}
                    required
                >
                    <Input
                        id="title"
                        name="title"
                        defaultValue={defaultValues?.title}
                        disabled={processing}
                        maxLength={80}
                        onChange={handleTitleChange}
                        aria-invalid={
                            errors.title !== undefined ? true : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Slug"
                    htmlFor="slug"
                    error={errors.slug}
                    required
                >
                    <Input
                        ref={slugRef}
                        id="slug"
                        name="slug"
                        defaultValue={defaultValues?.slug}
                        disabled={processing || isActive}
                        readOnly={isActive}
                        placeholder="my-challenge-slug"
                        onChange={handleSlugChange}
                        aria-invalid={
                            errors.slug !== undefined ? true : undefined
                        }
                    />
                </FormField>
            </div>

            <FormField
                label="Tagline"
                htmlFor="tagline"
                error={errors.tagline}
                required
            >
                <Input
                    id="tagline"
                    name="tagline"
                    defaultValue={defaultValues?.tagline}
                    disabled={processing}
                    aria-invalid={
                        errors.tagline !== undefined ? true : undefined
                    }
                />
            </FormField>

            <FormField
                label="Description"
                htmlFor="description"
                error={errors.description}
                required
            >
                <MarkdownEditor
                    name="description"
                    defaultValue={defaultValues?.description}
                    height={200}
                />
            </FormField>

            <Separator />

            <div className="grid items-start gap-4 sm:grid-cols-2">
                <FormField
                    label="Start date"
                    htmlFor="starts_at"
                    error={errors.starts_at}
                    optional
                >
                    <Input
                        id="starts_at"
                        name="starts_at"
                        type="date"
                        defaultValue={
                            defaultValues?.starts_at
                                ? new Date(defaultValues.starts_at)
                                      .toISOString()
                                      .split('T')[0]
                                : ''
                        }
                        disabled={processing}
                        aria-invalid={
                            errors.starts_at !== undefined ? true : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="End date"
                    htmlFor="ends_at"
                    error={errors.ends_at}
                    optional
                >
                    <Input
                        id="ends_at"
                        name="ends_at"
                        type="date"
                        defaultValue={
                            defaultValues?.ends_at
                                ? new Date(defaultValues.ends_at)
                                      .toISOString()
                                      .split('T')[0]
                                : ''
                        }
                        disabled={processing}
                        aria-invalid={
                            errors.ends_at !== undefined ? true : undefined
                        }
                    />
                </FormField>
            </div>

            <Separator />

            <FormField
                label="Organisation"
                htmlFor="organisation_id"
                error={errors.organisation_id}
                optional
            >
                <div className="flex items-start gap-2">
                    <div className="flex-1">
                        <OrganisationSearchSelect
                            key={orgKey}
                            name="organisation_id"
                            defaultValue={selectedOrganisation}
                            disabled={processing}
                            error={errors.organisation_id}
                            onChange={(org) => {
                                setSelectedOrganisation(
                                    org as Organisation | null,
                                );
                                setHasOrganisation(org !== null);
                            }}
                        />
                    </div>
                    {hasOrganisation === true &&
                    selectedOrganisation !== null ? (
                        <>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setIsEditOrgOpen(true)}
                            >
                                <Pencil className="size-4" />
                                Edit Org Details
                            </Button>
                            <EditOrganisationModal
                                organisation={selectedOrganisation}
                                isOpen={isEditOrgOpen}
                                onOpenChange={setIsEditOrgOpen}
                                onUpdated={(updatedOrg) => {
                                    setSelectedOrganisation(updatedOrg);
                                    setOrgKey((prev) => prev + 1);
                                }}
                            />
                        </>
                    ) : (
                        <CreateOrganisationModal
                            onCreated={(org) => {
                                setSelectedOrganisation(org);
                                setHasOrganisation(true);
                                setOrgKey((prev) => prev + 1);
                            }}
                        />
                    )}
                </div>
            </FormField>

            {hasOrganisation === false && (
                <>
                    <Separator />

                    <div className="space-y-4">
                        <Label className="text-base font-medium">
                            Thumbnail
                        </Label>
                        <ThumbnailSelector
                            name="thumbnail"
                            currentOriginalUrl={defaultValues?.thumbnail_url}
                            currentCropData={defaultValues?.thumbnail_crops}
                            crops={[
                                {
                                    key: 'square',
                                    label: 'Square crop',
                                    aspectRatio: 1,
                                },
                                {
                                    key: 'landscape',
                                    label: 'Landscape crop',
                                    aspectRatio: 16 / 9,
                                },
                            ]}
                            error={errors.thumbnail}
                            size="lg"
                        />
                    </div>
                </>
            )}

            <Separator />

            <div className="grid grid-cols-2 gap-4">
                <label className="flex cursor-pointer items-start gap-3">
                    <Switch
                        checked={activeChecked}
                        onCheckedChange={setActiveChecked}
                        disabled={processing || isActive}
                        className="mt-0.5"
                    />
                    <div>
                        <span className="text-sm font-medium">Active</span>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            {isActive
                                ? 'This challenge is active and cannot be deactivated.'
                                : 'Make this challenge visible and accepting submissions.'}
                        </p>
                    </div>
                </label>
                <label className="flex cursor-pointer items-start gap-3">
                    <Switch
                        checked={featuredEnabled}
                        onCheckedChange={setFeaturedChecked}
                        disabled={processing || isInviteToView}
                        className="mt-0.5"
                    />
                    <div>
                        <span className="text-sm font-medium">Featured</span>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            {isInviteToView
                                ? 'Invite to view challenges cannot be featured on the homepage.'
                                : 'Highlight this challenge on the homepage.'}
                        </p>
                    </div>
                </label>
                <input
                    type="hidden"
                    name="is_active"
                    value={activeChecked ? '1' : '0'}
                />
                <input
                    type="hidden"
                    name="is_featured"
                    value={featuredEnabled ? '1' : '0'}
                />
            </div>

            <Separator />

            <div className="space-y-4">
                <label className="flex cursor-pointer items-start gap-3">
                    <Switch
                        checked={liveViewEnabled}
                        onCheckedChange={setLiveViewEnabled}
                        disabled={processing}
                        className="mt-0.5"
                    />
                    <div>
                        <span className="text-sm font-medium">
                            Live leaderboard view
                        </span>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Enable a full-screen, auto-updating leaderboard for
                            big screens at in-person events.
                        </p>
                    </div>
                </label>
                <input
                    type="hidden"
                    name="live_view_enabled"
                    value={liveViewEnabled ? '1' : '0'}
                />

                {liveViewEnabled === true && (
                    <div className="space-y-4 border-l-2 border-neutral-200 pl-4 dark:border-neutral-800">
                        <FormField
                            label="Screen heading"
                            htmlFor="live_view_heading"
                            error={errors.live_view_heading}
                            optional
                        >
                            <Input
                                id="live_view_heading"
                                name="live_view_heading"
                                defaultValue={
                                    defaultValues?.live_view_heading ?? ''
                                }
                                disabled={processing}
                                maxLength={120}
                                placeholder={defaultValues?.title}
                            />
                            <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Shown big on the screen. Defaults to the
                                challenge title.
                            </p>
                        </FormField>
                        <FormField
                            label="Screen subheading"
                            htmlFor="live_view_subheading"
                            error={errors.live_view_subheading}
                            optional
                        >
                            <Input
                                id="live_view_subheading"
                                name="live_view_subheading"
                                defaultValue={
                                    defaultValues?.live_view_subheading ?? ''
                                }
                                disabled={processing}
                                maxLength={160}
                                placeholder={defaultValues?.tagline}
                            />
                            <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                Defaults to the challenge tagline.
                            </p>
                        </FormField>
                        <FormField
                            label="Secret access key"
                            htmlFor="live_view_access_token"
                            error={errors.live_view_access_token}
                            optional
                        >
                            <Input
                                id="live_view_access_token"
                                name="live_view_access_token"
                                defaultValue={
                                    defaultValues?.live_view_access_token ?? ''
                                }
                                disabled={processing}
                                maxLength={64}
                                placeholder="Leave blank for an open URL"
                            />
                            <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                If set, the live URL must include{' '}
                                <code>?key=…</code> to be viewable.
                            </p>
                        </FormField>
                    </div>
                )}
            </div>

            <Separator />

            <FormField
                label="Visibility"
                htmlFor="visibility"
                error={errors.visibility}
            >
                <Select
                    value={visibility}
                    onValueChange={handleVisibilityChange}
                    disabled={processing}
                >
                    <SelectTrigger
                        id="visibility"
                        aria-invalid={
                            errors.visibility !== undefined ? true : undefined
                        }
                    >
                        <SelectValue placeholder="Select visibility" />
                    </SelectTrigger>
                    <SelectContent>
                        {visibilityOptions.map((option) => (
                            <SelectItem
                                key={option.value}
                                value={String(option.value)}
                            >
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <input type="hidden" name="visibility" value={visibility} />
            </FormField>

            {/*
                Kept mounted (and hidden via CSS) rather than conditionally
                rendered: mounting the editor as the visibility Select closes
                strands Radix's react-remove-scroll body lock and freezes page
                scrolling. The backend nulls this for non-invite visibilities.
            */}
            <div className={requiresInvite === false ? 'hidden' : undefined}>
                <FormField
                    label="How to get involved"
                    htmlFor="involvement_instructions"
                    error={errors.involvement_instructions}
                    required
                >
                    <p className="mb-2 text-sm text-neutral-500 dark:text-neutral-400">
                        Shown to people who aren’t yet eligible to submit,
                        explaining how they can take part.
                    </p>
                    <MarkdownEditor
                        name="involvement_instructions"
                        defaultValue={
                            defaultValues?.involvement_instructions ?? undefined
                        }
                        height={160}
                    />
                </FormField>
            </div>

            <FormField
                label="Participant instructions"
                htmlFor="participant_instructions"
                error={errors.participant_instructions}
                optional
            >
                <p className="mb-2 text-sm text-neutral-500 dark:text-neutral-400">
                    Private instructions shown only to entrants once they’re
                    eligible to take part.
                </p>
                <MarkdownEditor
                    name="participant_instructions"
                    defaultValue={
                        defaultValues?.participant_instructions ?? undefined
                    }
                    height={160}
                />
            </FormField>
        </div>
    );
}
