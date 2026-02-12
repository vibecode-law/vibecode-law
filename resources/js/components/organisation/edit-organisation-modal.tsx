import UpdateController from '@/actions/App/Http/Controllers/Staff/Organisations/UpdateController';
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
import { type SimpleCropData } from '@/components/ui/image-crop-modal';
import { Input } from '@/components/ui/input';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { SubmitButton } from '@/components/ui/submit-button';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { useModalForm } from '@/hooks/use-modal-form';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface Organisation {
    id: number;
    name: string;
    tagline?: string | null;
    about?: string | null;
    thumbnail_url?: string | null;
    thumbnail_crops?: Record<string, SimpleCropData> | null;
}

interface EditOrganisationModalProps {
    organisation: Organisation;
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    onUpdated: (organisation: Organisation) => void;
}

export function EditOrganisationModal({
    organisation,
    isOpen,
    onOpenChange,
    onUpdated,
}: EditOrganisationModalProps) {
    const { isSubmitting, setIsSubmitting, errors, setErrors, clearErrors } =
        useModalForm<{
            name?: string;
            tagline?: string;
            about?: string;
            thumbnail?: string;
        }>();

    const [name, setName] = useState(organisation.name);
    const [tagline, setTagline] = useState(organisation.tagline ?? '');
    const [about, setAbout] = useState(organisation.about ?? '');
    const [lastOrganisationId, setLastOrganisationId] = useState(
        organisation.id,
    );

    // Reset form when a different organisation is selected (during render, not in effect)
    if (organisation.id !== lastOrganisationId) {
        setLastOrganisationId(organisation.id);
        setName(organisation.name);
        setTagline(organisation.tagline ?? '');
        setAbout(organisation.about ?? '');
        clearErrors();
    }

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        const formData = new FormData(e.currentTarget);
        formData.append('_method', 'PATCH');

        router.post(UpdateController.url(organisation.id), formData, {
            preserveScroll: true,
            onSuccess: () => {
                onUpdated({
                    id: organisation.id,
                    name,
                    tagline,
                    about,
                    thumbnail_url: organisation.thumbnail_url,
                    thumbnail_crops: organisation.thumbnail_crops,
                });
                onOpenChange(false);
            },
            onError: (newErrors) => {
                setErrors(
                    newErrors as {
                        name?: string;
                        tagline?: string;
                        about?: string;
                    },
                );
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    const handleOpenChange = (open: boolean) => {
        // Reset form when opening or closing
        setName(organisation.name);
        setTagline(organisation.tagline ?? '');
        setAbout(organisation.about ?? '');
        clearErrors();
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Edit Organisation</DialogTitle>
                        <DialogDescription>
                            Update the organisation details.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <FormField
                            label="Name"
                            htmlFor="edit-org-name"
                            error={errors.name}
                            required
                        >
                            <Input
                                id="edit-org-name"
                                name="name"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                disabled={isSubmitting}
                                maxLength={255}
                                aria-invalid={
                                    errors.name !== undefined ? true : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Tagline"
                            htmlFor="edit-org-tagline"
                            error={errors.tagline}
                            required
                        >
                            <Input
                                id="edit-org-tagline"
                                name="tagline"
                                value={tagline}
                                onChange={(e) => setTagline(e.target.value)}
                                disabled={isSubmitting}
                                maxLength={255}
                                aria-invalid={
                                    errors.tagline !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="About"
                            htmlFor="edit-org-about"
                            error={errors.about}
                            required
                        >
                            <MarkdownEditor
                                name="about"
                                value={about}
                                onChange={setAbout}
                                height={150}
                            />
                        </FormField>

                        <ThumbnailSelector
                            name="thumbnail"
                            currentOriginalUrl={organisation.thumbnail_url}
                            currentCropData={organisation.thumbnail_crops}
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
