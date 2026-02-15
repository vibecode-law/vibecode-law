import { FormField } from '@/components/ui/form-field';
import { type SimpleCropData } from '@/components/ui/image-crop-modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { slugify } from '@/lib/slug';
import { useRef, useState } from 'react';

interface LessonFormFieldsProps {
    processing: boolean;
    errors: Record<string, string>;
    defaultValues?: {
        title?: string;
        slug?: string;
        tagline?: string;
        description?: string;
        learning_objectives?: string | null;
        copy?: string | null;
        asset_id?: string | null;
        gated?: boolean;
        visible?: boolean;
        publish_date?: string | null;
        thumbnail_url?: string | null;
        thumbnail_crops?: Record<string, SimpleCropData> | null;
    };
    mode: 'create' | 'edit';
}

export default function LessonFormFields({
    processing,
    errors,
    defaultValues,
    mode,
}: LessonFormFieldsProps) {
    const [autoSlug, setAutoSlug] = useState(mode === 'create');
    const [gatedChecked, setGatedChecked] = useState(
        defaultValues?.gated ?? true,
    );
    const [visibleChecked, setVisibleChecked] = useState(
        defaultValues?.visible ?? false,
    );
    const slugRef = useRef<HTMLInputElement>(null);
    const slugLocked = mode === 'edit' && defaultValues?.visible === true;

    const handleTitleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        if (
            autoSlug === true &&
            slugLocked !== true &&
            slugRef.current !== null
        ) {
            slugRef.current.value = slugify(event.target.value);
        }
    };

    const handleSlugChange = () => {
        setAutoSlug(false);
    };

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
                        maxLength={255}
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
                        disabled={processing || slugLocked}
                        placeholder="my-lesson-slug"
                        onChange={handleSlugChange}
                        aria-invalid={
                            errors.slug !== undefined ? true : undefined
                        }
                    />
                    {slugLocked === true && (
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Slug cannot be changed once the lesson is visible.
                        </p>
                    )}
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

            <FormField
                label="Learning Objectives"
                htmlFor="learning_objectives"
                error={errors.learning_objectives}
                required
            >
                <MarkdownEditor
                    name="learning_objectives"
                    defaultValue={defaultValues?.learning_objectives}
                    height={150}
                />
            </FormField>

            <FormField label="Copy" htmlFor="copy" error={errors.copy} optional>
                <MarkdownEditor
                    name="copy"
                    defaultValue={defaultValues?.copy}
                    height={200}
                />
            </FormField>

            <Separator />

            <div className="grid items-start gap-4 sm:grid-cols-2">
                <FormField
                    label="Mux Asset ID"
                    htmlFor="asset_id"
                    error={errors.asset_id}
                    optional
                >
                    <Input
                        id="asset_id"
                        name="asset_id"
                        defaultValue={defaultValues?.asset_id ?? ''}
                        disabled={processing}
                        placeholder="Enter Mux asset ID"
                        aria-invalid={
                            errors.asset_id !== undefined ? true : undefined
                        }
                    />
                </FormField>
            </div>

            <Separator />

            <div className="space-y-4">
                <Label className="text-base font-medium">
                    Thumbnail
                    <span className="ml-1 text-sm font-normal text-neutral-400 dark:text-neutral-500">
                        (optional)
                    </span>
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

            <Separator />

            <div className="grid items-start gap-4 sm:grid-cols-2">
                <div>
                    <label className="flex cursor-pointer items-start gap-3">
                        <Switch
                            checked={visibleChecked}
                            onCheckedChange={setVisibleChecked}
                            disabled={processing}
                            className="mt-0.5"
                        />
                        <div>
                            <span className="text-sm font-medium">Visible</span>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Make this lesson publicly visible.
                            </p>
                        </div>
                    </label>
                    <input
                        type="hidden"
                        name="visible"
                        value={visibleChecked ? '1' : '0'}
                    />
                </div>

                <FormField
                    label="Publish Date"
                    htmlFor="publish_date"
                    error={errors.publish_date}
                    optional
                >
                    <Input
                        id="publish_date"
                        name="publish_date"
                        type="date"
                        defaultValue={defaultValues?.publish_date ?? ''}
                        disabled={processing}
                        aria-invalid={
                            errors.publish_date !== undefined ? true : undefined
                        }
                    />
                </FormField>
            </div>

            <div>
                <label className="flex cursor-pointer items-start gap-3">
                    <Switch
                        checked={gatedChecked}
                        onCheckedChange={setGatedChecked}
                        disabled={processing}
                        className="mt-0.5"
                    />
                    <div>
                        <span className="text-sm font-medium">Gated</span>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Require authentication to access this lesson.
                        </p>
                    </div>
                </label>
                <input
                    type="hidden"
                    name="gated"
                    value={gatedChecked ? '1' : '0'}
                />
            </div>
        </div>
    );
}
