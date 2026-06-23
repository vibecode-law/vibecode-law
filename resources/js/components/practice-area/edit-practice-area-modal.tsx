import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { SubmitButton } from '@/components/ui/submit-button';
import { useModalForm } from '@/hooks/use-modal-form';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface EditPracticeAreaModalProps {
    practiceArea: App.Http.Resources.PracticeAreaResource;
    updateUrl: string;
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}

export function EditPracticeAreaModal({
    practiceArea,
    updateUrl,
    isOpen,
    onOpenChange,
}: EditPracticeAreaModalProps) {
    const { isSubmitting, setIsSubmitting, errors, setErrors, clearErrors } =
        useModalForm<{ name?: string }>();

    const [name, setName] = useState(practiceArea.name);
    const [lastPracticeAreaId, setLastPracticeAreaId] = useState(
        practiceArea.id,
    );

    // Reset form when a different practice area is selected (during render, not in effect)
    if (practiceArea.id !== lastPracticeAreaId) {
        setLastPracticeAreaId(practiceArea.id);
        setName(practiceArea.name);
        clearErrors();
    }

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        router.put(
            updateUrl,
            { name },
            {
                onSuccess: () => {
                    onOpenChange(false);
                },
                onError: (newErrors) => {
                    setErrors(newErrors as { name?: string });
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const handleOpenChange = (open: boolean) => {
        // Reset form when opening or closing
        setName(practiceArea.name);
        clearErrors();
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Edit Practice Area</DialogTitle>
                        <DialogDescription>
                            Update the practice area name.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <FormField
                            label="Name"
                            htmlFor="edit-name"
                            error={errors.name}
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

                        <div className="space-y-1">
                            <p className="text-sm text-neutral-500 dark:text-neutral-300">
                                Slug:{' '}
                                <code className="rounded bg-neutral-100 px-1.5 py-0.5 text-xs dark:bg-neutral-800">
                                    {practiceArea.slug}
                                </code>
                            </p>
                            <p className="text-xs text-neutral-400 dark:text-neutral-400">
                                The slug cannot be changed after creation.
                            </p>
                        </div>
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
                            Save Changes
                        </SubmitButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
