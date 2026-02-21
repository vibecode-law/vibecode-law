import HeadingSmall from '@/components/heading/heading-small';
import { MetadataSubNav } from '@/components/staff/metadata-sub-nav';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { SubmitButton } from '@/components/ui/submit-button';
import { useModalForm } from '@/hooks/use-modal-form';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { destroy, store, update } from '@/routes/staff/metadata/tags';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface TagsIndexProps {
    tags: App.Http.Resources.TagResource[];
    tagTypes: App.ValueObjects.FrontendEnum[];
}

type TagFormErrors = {
    name?: string;
    type?: string;
};

function CreateTagModal({
    tagTypes,
}: {
    tagTypes: App.ValueObjects.FrontendEnum[];
}) {
    const {
        isOpen,
        handleOpenChange: baseHandleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
    } = useModalForm<TagFormErrors>();

    const [name, setName] = useState('');
    const [type, setType] = useState('');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        router.post(
            store.url(),
            { name, type },
            {
                onSuccess: () => {
                    baseHandleOpenChange(false);
                    setName('');
                    setType('');
                },
                onError: (newErrors) => {
                    setErrors(newErrors as TagFormErrors);
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const handleOpenChange = (open: boolean) => {
        if (open === false) {
            setName('');
            setType('');
        }
        baseHandleOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button>
                    <Plus className="size-4" />
                    Add Tag
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create Tag</DialogTitle>
                        <DialogDescription>
                            Add a new tag. The slug will be generated
                            automatically.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <FormField
                            label="Name"
                            htmlFor="name"
                            error={errors.name}
                            required
                        >
                            <Input
                                id="name"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder="e.g. React"
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.name !== undefined ? true : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Type"
                            htmlFor="type"
                            error={errors.type}
                            required
                        >
                            <Select
                                value={type}
                                onValueChange={setType}
                                disabled={isSubmitting}
                            >
                                <SelectTrigger
                                    id="type"
                                    aria-invalid={
                                        errors.type !== undefined
                                            ? true
                                            : undefined
                                    }
                                >
                                    <SelectValue placeholder="Select a type" />
                                </SelectTrigger>
                                <SelectContent>
                                    {tagTypes.map((tagType) => (
                                        <SelectItem
                                            key={tagType.value}
                                            value={tagType.value}
                                        >
                                            {tagType.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                    </div>

                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => handleOpenChange(false)}
                            disabled={isSubmitting}
                        >
                            Cancel
                        </Button>
                        <SubmitButton
                            processing={isSubmitting}
                            processingLabel="Creating..."
                        >
                            Create
                        </SubmitButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function EditTagModal({
    tag,
    tagTypes,
    isOpen,
    onOpenChange,
}: {
    tag: App.Http.Resources.TagResource;
    tagTypes: App.ValueObjects.FrontendEnum[];
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { isSubmitting, setIsSubmitting, errors, setErrors, clearErrors } =
        useModalForm<TagFormErrors>();

    const [name, setName] = useState(tag.name);
    const [type, setType] = useState(String(tag.type.value));

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        router.patch(
            update.url({ tag: tag.id }),
            { name, type },
            {
                onSuccess: () => {
                    onOpenChange(false);
                },
                onError: (newErrors) => {
                    setErrors(newErrors as TagFormErrors);
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const handleOpenChange = (open: boolean) => {
        if (open === false) {
            clearErrors();
        }
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Edit Tag</DialogTitle>
                        <DialogDescription>
                            Update the tag details. The slug will be regenerated
                            from the name.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <FormField
                            label="Name"
                            htmlFor="edit-name"
                            error={errors.name}
                            required
                        >
                            <Input
                                id="edit-name"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.name !== undefined ? true : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Type"
                            htmlFor="edit-type"
                            error={errors.type}
                            required
                        >
                            <Select
                                value={type}
                                onValueChange={setType}
                                disabled={isSubmitting}
                            >
                                <SelectTrigger
                                    id="edit-type"
                                    aria-invalid={
                                        errors.type !== undefined
                                            ? true
                                            : undefined
                                    }
                                >
                                    <SelectValue placeholder="Select a type" />
                                </SelectTrigger>
                                <SelectContent>
                                    {tagTypes.map((tagType) => (
                                        <SelectItem
                                            key={tagType.value}
                                            value={tagType.value}
                                        >
                                            {tagType.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormField>
                    </div>

                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => handleOpenChange(false)}
                            disabled={isSubmitting}
                        >
                            Cancel
                        </Button>
                        <SubmitButton
                            processing={isSubmitting}
                            processingLabel="Saving..."
                        >
                            Save
                        </SubmitButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function TagsIndex({ tags, tagTypes }: TagsIndexProps) {
    const [editingTag, setEditingTag] =
        useState<App.Http.Resources.TagResource | null>(null);

    const handleDelete = (tag: App.Http.Resources.TagResource) => {
        if (
            confirm(
                `Are you sure you want to delete the tag "${tag.name}"? This action cannot be undone.`,
            )
        ) {
            router.delete(destroy.url({ tag: tag.id }));
        }
    };

    return (
        <StaffAreaLayout fullWidth>
            <Head title="Tags" />

            <MetadataSubNav />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Tags"
                        description="Manage tags for courses and lessons"
                    />
                    <CreateTagModal tagTypes={tagTypes} />
                </div>

                {tags.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-neutral-300 py-12 text-center dark:border-neutral-700">
                        <p className="text-neutral-500 dark:text-neutral-400">
                            No tags yet
                        </p>
                    </div>
                ) : (
                    <div className="divide-y divide-neutral-200 rounded-lg border border-neutral-200 bg-white dark:divide-neutral-800 dark:border-neutral-800 dark:bg-neutral-950">
                        {tags.map((tag) => (
                            <div
                                key={tag.id}
                                className="flex items-center gap-4 px-4 py-3"
                            >
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center gap-2">
                                        <span className="font-medium text-neutral-900 dark:text-white">
                                            {tag.name}
                                        </span>
                                        <Badge variant="secondary">
                                            {tag.type.label}
                                        </Badge>
                                    </div>
                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                        {tag.slug}
                                    </p>
                                </div>
                                <div className="flex shrink-0 items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        className="gap-1.5"
                                        onClick={() => setEditingTag(tag)}
                                    >
                                        <Pencil className="size-4" />
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        className="gap-1.5"
                                        onClick={() => handleDelete(tag)}
                                    >
                                        <Trash2 className="size-4" />
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {editingTag !== null && (
                <EditTagModal
                    tag={editingTag}
                    tagTypes={tagTypes}
                    isOpen={editingTag !== null}
                    onOpenChange={(open) => {
                        if (open === false) {
                            setEditingTag(null);
                        }
                    }}
                />
            )}
        </StaffAreaLayout>
    );
}
