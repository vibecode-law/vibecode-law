import { FormField } from '@/components/ui/form-field';
import { GroupedPillSelect } from '@/components/ui/grouped-pill-select';
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
import { slugify } from '@/lib/slug';
import { useMemo, useRef, useState } from 'react';

interface CourseFormFieldsProps {
    processing: boolean;
    errors: Record<string, string>;
    experienceLevels: App.ValueObjects.FrontendEnum[];
    availableTags: App.Http.Resources.TagResource[];
    defaultTagIds?: number[];
    defaultValues?: {
        title?: string;
        slug?: string;
        tagline?: string;
        description?: string;
        learning_objectives?: string | null;
        experience_level?: App.ValueObjects.FrontendEnum | null;
        is_featured?: boolean;
        thumbnail_url?: string | null;
        thumbnail_crops?: Record<string, SimpleCropData> | null;
    };
    allowPreview?: boolean;
    mode: 'create' | 'edit';
}

export default function CourseFormFields({
    processing,
    errors,
    experienceLevels,
    availableTags,
    defaultTagIds,
    defaultValues,
    allowPreview = false,
    mode,
}: CourseFormFieldsProps) {
    const [autoSlug, setAutoSlug] = useState(mode === 'create');
    const [featuredChecked, setFeaturedChecked] = useState(
        defaultValues?.is_featured ?? false,
    );
    const [experienceLevel, setExperienceLevel] = useState<string>(
        defaultValues?.experience_level?.value?.toString() ?? '',
    );
    const [selectedTagIds, setSelectedTagIds] = useState<number[]>(
        defaultTagIds ?? [],
    );
    const tagGroups = useMemo(() => {
        const groupMap = new Map<
            string,
            { label: string; options: { value: number; label: string }[] }
        >();
        for (const tag of availableTags) {
            const key = tag.type.label;
            if (groupMap.has(key) === false) {
                groupMap.set(key, { label: key, options: [] });
            }
            groupMap.get(key)!.options.push({ value: tag.id, label: tag.name });
        }
        return Array.from(groupMap.values());
    }, [availableTags]);
    const slugRef = useRef<HTMLInputElement>(null);
    const slugLocked = mode === 'edit' && allowPreview === true;

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
                        placeholder="my-course-slug"
                        onChange={handleSlugChange}
                        aria-invalid={
                            errors.slug !== undefined ? true : undefined
                        }
                    />
                    {slugLocked === true && (
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Slug cannot be changed once the course allows
                            preview or has a publish date set.
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

            <Separator />

            <FormField
                label="Experience Level"
                htmlFor="experience_level"
                error={errors.experience_level}
                required
            >
                <Select
                    value={experienceLevel}
                    onValueChange={setExperienceLevel}
                    disabled={processing}
                >
                    <SelectTrigger id="experience_level">
                        <SelectValue placeholder="Select level" />
                    </SelectTrigger>
                    <SelectContent>
                        {experienceLevels.map((level) => (
                            <SelectItem
                                key={level.value}
                                value={level.value.toString()}
                            >
                                {level.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <input
                    type="hidden"
                    name="experience_level"
                    value={experienceLevel}
                />
            </FormField>

            <GroupedPillSelect
                name="tags"
                groups={tagGroups}
                selected={selectedTagIds}
                onChange={setSelectedTagIds}
                label="Tags"
                error={errors.tags}
            />

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

            <div>
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
                            Highlight this course on the homepage.
                        </p>
                    </div>
                </label>
                <input
                    type="hidden"
                    name="is_featured"
                    value={featuredChecked ? '1' : '0'}
                />
            </div>
        </div>
    );
}
