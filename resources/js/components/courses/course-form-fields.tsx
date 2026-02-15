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
import { UserSearchSelect } from '@/components/ui/user-search-select';
import { slugify } from '@/lib/slug';
import { useRef, useState } from 'react';

interface CourseFormFieldsProps {
    processing: boolean;
    errors: Record<string, string>;
    experienceLevels: App.ValueObjects.FrontendEnum[];
    defaultValues?: {
        title?: string;
        slug?: string;
        tagline?: string;
        description?: string;
        learning_objectives?: string | null;
        experience_level?: App.ValueObjects.FrontendEnum | null;
        publish_date?: string | null;
        user?: {
            id: number;
            name: string;
            job_title?: string | null;
            organisation?: string | null;
        } | null;
        visible?: boolean;
        is_featured?: boolean;
        thumbnail_url?: string | null;
        thumbnail_crops?: Record<string, SimpleCropData> | null;
    };
    mode: 'create' | 'edit';
}

export default function CourseFormFields({
    processing,
    errors,
    experienceLevels,
    defaultValues,
    mode,
}: CourseFormFieldsProps) {
    const [autoSlug, setAutoSlug] = useState(mode === 'create');
    const [visibleChecked, setVisibleChecked] = useState(
        defaultValues?.visible ?? false,
    );
    const [featuredChecked, setFeaturedChecked] = useState(
        defaultValues?.is_featured ?? false,
    );
    const [experienceLevel, setExperienceLevel] = useState<string>(
        defaultValues?.experience_level?.value?.toString() ?? '',
    );
    const [selectedUser, setSelectedUser] = useState<{
        id: number;
        name: string;
        email?: string | null;
        job_title?: string | null;
        organisation?: string | null;
    } | null>(defaultValues?.user ?? null);
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
                        placeholder="my-course-slug"
                        onChange={handleSlugChange}
                        aria-invalid={
                            errors.slug !== undefined ? true : undefined
                        }
                    />
                    {slugLocked === true && (
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Slug cannot be changed once the course is visible.
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

            <div>
                <UserSearchSelect
                    selectedUser={selectedUser}
                    onSelect={setSelectedUser}
                    disabled={processing}
                    label="Instructor"
                    selectedHelpText="Instructor details will be pulled from this user's profile"
                    searchHelpText="Search for a user to set as the course instructor"
                />
                <input
                    type="hidden"
                    name="user_id"
                    value={selectedUser?.id ?? ''}
                />
                {errors.user_id && (
                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                        {errors.user_id}
                    </p>
                )}
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
                                Make this course visible to users.
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
