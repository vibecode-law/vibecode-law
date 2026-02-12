import { OrganisationSearchSelect } from '@/components/challenges/organisation-search-select';
import { CreateOrganisationModal } from '@/components/organisation/create-organisation-modal';
import { EditOrganisationModal } from '@/components/organisation/edit-organisation-modal';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { type SimpleCropData } from '@/components/ui/image-crop-modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
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
        starts_at?: string | null;
        ends_at?: string | null;
        is_active?: boolean;
        is_featured?: boolean;
        organisation?: Organisation | null;
        thumbnail_url?: string | null;
        thumbnail_crops?: Record<string, SimpleCropData> | null;
    };
    mode: 'create' | 'edit';
}

export default function ChallengeFormFields({
    processing,
    errors,
    defaultValues,
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
    const slugRef = useRef<HTMLInputElement>(null);

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
                        checked={featuredChecked}
                        onCheckedChange={setFeaturedChecked}
                        disabled={processing}
                        className="mt-0.5"
                    />
                    <div>
                        <span className="text-sm font-medium">Featured</span>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Highlight this challenge on the homepage.
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
                    value={featuredChecked ? '1' : '0'}
                />
            </div>
        </div>
    );
}
