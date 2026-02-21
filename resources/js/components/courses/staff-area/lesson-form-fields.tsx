import { FormField } from '@/components/ui/form-field';
import { GroupedPillSelect } from '@/components/ui/grouped-pill-select';
import { type SimpleCropData } from '@/components/ui/image-crop-modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { UserSearchSelect } from '@/components/ui/user-search-select';
import { slugify } from '@/lib/slug';
import { useMemo, useRef, useState } from 'react';

interface LessonFormFieldsProps {
    processing: boolean;
    errors: Record<string, string>;
    availableTags: App.Http.Resources.TagResource[];
    defaultTagIds?: number[];
    isPublished?: boolean;
    defaultValues?: {
        title?: string;
        slug?: string;
        tagline?: string | null;
        description?: string | null;
        learning_objectives?: string | null;
        copy?: string | null;
        gated?: boolean;
        thumbnail_url?: string | null;
        thumbnail_crops?: Record<string, SimpleCropData> | null;
        instructors?: {
            id: number;
            first_name: string;
            last_name: string;
            job_title?: string | null;
            organisation?: string | null;
        }[];
    };
    mode: 'create' | 'edit';
}

export default function LessonFormFields({
    processing,
    errors,
    availableTags,
    defaultTagIds,
    isPublished,
    defaultValues,
    mode,
}: LessonFormFieldsProps) {
    const [autoSlug, setAutoSlug] = useState(mode === 'create');
    const [gatedChecked, setGatedChecked] = useState(
        defaultValues?.gated ?? true,
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
    const [selectedInstructors, setSelectedInstructors] = useState<
        {
            id: number;
            name: string;
            email?: string | null;
            job_title?: string | null;
            organisation?: string | null;
        }[]
    >(
        (defaultValues?.instructors ?? []).map((u) => ({
            id: u.id,
            name: `${u.first_name} ${u.last_name}`,
            job_title: u.job_title,
            organisation: u.organisation,
        })),
    );
    const slugRef = useRef<HTMLInputElement>(null);
    const slugLocked = mode === 'edit' && isPublished === true;

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
                            Slug cannot be changed once the lesson allows
                            preview or has a publish date.
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
                    defaultValue={defaultValues?.tagline ?? undefined}
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
                    defaultValue={defaultValues?.description ?? undefined}
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

            <div>
                <UserSearchSelect
                    selectedUser={null}
                    onSelect={(user) => {
                        if (
                            user &&
                            !selectedInstructors.some((i) => i.id === user.id)
                        ) {
                            setSelectedInstructors((prev) => [...prev, user]);
                        }
                    }}
                    disabled={processing}
                    label={
                        <>
                            Instructors
                            <span className="ml-1 text-sm font-normal text-neutral-400 dark:text-neutral-500">
                                (optional)
                            </span>
                        </>
                    }
                    selectedHelpText=""
                    searchHelpText="Search for users to add as lesson instructors"
                />
                {selectedInstructors.length > 0 && (
                    <div className="mt-2 space-y-2">
                        {selectedInstructors.map((instructor) => (
                            <div
                                key={instructor.id}
                                className="flex items-center justify-between rounded-md border border-neutral-300 bg-neutral-50 px-3 py-2 dark:border-neutral-700 dark:bg-neutral-900"
                            >
                                <div>
                                    <p className="text-sm font-medium text-neutral-900 dark:text-white">
                                        {instructor.name}
                                    </p>
                                    {(instructor.job_title ||
                                        instructor.organisation) && (
                                        <p className="text-xs text-neutral-500 dark:text-neutral-400">
                                            {instructor.job_title}
                                            {instructor.job_title &&
                                                instructor.organisation &&
                                                ' at '}
                                            {instructor.organisation}
                                        </p>
                                    )}
                                </div>
                                <button
                                    type="button"
                                    onClick={() =>
                                        setSelectedInstructors((prev) =>
                                            prev.filter(
                                                (i) => i.id !== instructor.id,
                                            ),
                                        )
                                    }
                                    className="text-sm text-neutral-500 hover:text-red-600 dark:text-neutral-400 dark:hover:text-red-400"
                                    disabled={processing}
                                >
                                    Remove
                                </button>
                            </div>
                        ))}
                    </div>
                )}
                {selectedInstructors.map((instructor) => (
                    <input
                        key={instructor.id}
                        type="hidden"
                        name="instructor_ids[]"
                        value={instructor.id}
                    />
                ))}
                {errors.instructor_ids && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {errors.instructor_ids}
                    </p>
                )}
            </div>

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
                            key: 'landscape',
                            label: 'Landscape crop (16:9)',
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
