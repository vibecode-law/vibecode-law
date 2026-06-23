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
import { SubmitButton } from '@/components/ui/submit-button';
import { useModalForm } from '@/hooks/use-modal-form';
import { slugify } from '@/lib/slug';
import { router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';

interface CreatePracticeAreaModalProps {
    storeUrl: string;
}

export function CreatePracticeAreaModal({
    storeUrl,
}: CreatePracticeAreaModalProps) {
    const {
        isOpen,
        handleOpenChange: baseHandleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
    } = useModalForm<{ name?: string; slug?: string }>();

    const [name, setName] = useState('');
    const [slug, setSlug] = useState('');
    const [autoSlug, setAutoSlug] = useState(true);

    const handleNameChange = (value: string) => {
        setName(value);
        if (autoSlug === true) {
            setSlug(slugify(value));
        }
    };

    const handleSlugChange = (value: string) => {
        setSlug(value);
        setAutoSlug(false);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        router.post(
            storeUrl,
            { name, slug },
            {
                onSuccess: () => {
                    baseHandleOpenChange(false);
                    setName('');
                    setSlug('');
                    setAutoSlug(true);
                },
                onError: (newErrors) => {
                    setErrors(newErrors as { name?: string; slug?: string });
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
            setSlug('');
            setAutoSlug(true);
        }
        baseHandleOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button>
                    <Plus className="size-4" />
                    Add Practice Area
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create Practice Area</DialogTitle>
                        <DialogDescription>
                            Add a new practice area to the showcase.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <FormField
                            label="Name"
                            htmlFor="name"
                            error={errors.name}
                        >
                            <Input
                                id="name"
                                value={name}
                                onChange={(e) =>
                                    handleNameChange(e.target.value)
                                }
                                placeholder="e.g. Web Development"
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.name !== undefined ? true : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Slug"
                            htmlFor="slug"
                            error={errors.slug}
                        >
                            <Input
                                id="slug"
                                value={slug}
                                onChange={(e) =>
                                    handleSlugChange(e.target.value)
                                }
                                placeholder="e.g. web-development"
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.slug !== undefined ? true : undefined
                                }
                            />
                            {slug !== '' && (
                                <p className="text-xs text-neutral-500 dark:text-neutral-300">
                                    URL: /showcase/practice-area/{slug}
                                </p>
                            )}
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
