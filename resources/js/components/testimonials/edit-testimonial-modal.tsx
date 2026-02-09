import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { type CropData } from '@/components/ui/image-crop-modal';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SubmitButton } from '@/components/ui/submit-button';
import { Textarea } from '@/components/ui/textarea';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { useModalForm } from '@/hooks/use-modal-form';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface EditTestimonialModalProps {
    testimonial: App.Http.Resources.TestimonialResource;
    updateUrl: string;
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}

export function EditTestimonialModal({
    testimonial,
    updateUrl,
    isOpen,
    onOpenChange,
}: EditTestimonialModalProps) {
    const { isSubmitting, setIsSubmitting, errors, setErrors, clearErrors } =
        useModalForm<{
            name?: string;
            job_title?: string;
            organisation?: string;
            content?: string;
            avatar?: string;
            display_order?: string;
        }>();

    const [name, setName] = useState(testimonial.name ?? '');
    const [jobTitle, setJobTitle] = useState(testimonial.job_title ?? '');
    const [organisation, setOrganisation] = useState(
        testimonial.organisation ?? '',
    );
    const [content, setContent] = useState(testimonial.content);
    const [displayOrder, setDisplayOrder] = useState(
        String(testimonial.display_order),
    );
    const [isPublished, setIsPublished] = useState(testimonial.is_published);
    const [lastTestimonialId, setLastTestimonialId] = useState(testimonial.id);
    const [isLinkedToUser, setIsLinkedToUser] = useState(
        testimonial.user_id !== null,
    );
    const [, setAvatarCropData] = useState<CropData | null>(null);

    // Reset form when a different testimonial is selected (during render, not in effect)
    if (testimonial.id !== lastTestimonialId) {
        setLastTestimonialId(testimonial.id);
        setIsLinkedToUser(testimonial.user_id !== null);
        // If linked to user, show the display_* values, otherwise show the stored values
        setName(
            testimonial.user_id !== null
                ? testimonial.display_name
                : (testimonial.name ?? ''),
        );
        setJobTitle(
            testimonial.user_id !== null
                ? (testimonial.display_job_title ?? '')
                : (testimonial.job_title ?? ''),
        );
        setOrganisation(
            testimonial.user_id !== null
                ? (testimonial.display_organisation ?? '')
                : (testimonial.organisation ?? ''),
        );
        setContent(testimonial.content);
        setDisplayOrder(String(testimonial.display_order));
        setIsPublished(testimonial.is_published);
        setAvatarCropData(null);
        clearErrors();
    }

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        const formElement = e.target as HTMLFormElement;
        const formData = new FormData(formElement);
        if (isLinkedToUser) {
            // Preserve the existing user link
            formData.append('user_id', String(testimonial.user_id));
        } else {
            // Unlink or no user: null out user_id and send editable fields
            formData.append('user_id', '');
            formData.append('name', name);
            formData.append('job_title', jobTitle);
            formData.append('organisation', organisation);
        }
        formData.append('content', content);
        formData.append('display_order', displayOrder);
        formData.append('is_published', isPublished ? '1' : '0');
        formData.append('_method', 'PUT');

        router.post(updateUrl, formData, {
            onSuccess: () => {
                onOpenChange(false);
            },
            onError: (newErrors) => {
                setErrors(
                    newErrors as {
                        name?: string;
                        job_title?: string;
                        organisation?: string;
                        content?: string;
                        avatar?: string;
                        display_order?: string;
                    },
                );
            },
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    const handleUnlink = () => {
        if (
            window.confirm(
                'Are you sure you want to unlink this testimonial from the user profile? The current name, job title, and organisation will become editable and will no longer be synced with the user profile.',
            )
        ) {
            setIsLinkedToUser(false);
            // Keep the current display values but enable editing
        }
    };

    const handleOpenChange = (open: boolean) => {
        // Reset form when opening or closing
        setIsLinkedToUser(testimonial.user_id !== null);
        setName(
            testimonial.user_id !== null
                ? testimonial.display_name
                : (testimonial.name ?? ''),
        );
        setJobTitle(
            testimonial.user_id !== null
                ? (testimonial.display_job_title ?? '')
                : (testimonial.job_title ?? ''),
        );
        setOrganisation(
            testimonial.user_id !== null
                ? (testimonial.display_organisation ?? '')
                : (testimonial.organisation ?? ''),
        );
        setContent(testimonial.content);
        setDisplayOrder(String(testimonial.display_order));
        setIsPublished(testimonial.is_published);
        setAvatarCropData(null);
        clearErrors();
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Edit Testimonial</DialogTitle>
                        <DialogDescription>
                            Update the testimonial details.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        {isLinkedToUser && (
                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-900 dark:bg-blue-950/30">
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-medium text-blue-900 dark:text-blue-300">
                                            Linked to User Profile
                                        </p>
                                        <p className="mt-1 text-xs text-blue-700 dark:text-blue-400">
                                            Name, job title, organisation, and
                                            avatar are pulled from the linked
                                            user profile.
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={handleUnlink}
                                        disabled={isSubmitting}
                                    >
                                        Unlink
                                    </Button>
                                </div>
                            </div>
                        )}

                        {!isLinkedToUser && (
                            <>
                                <FormField
                                    label="Name"
                                    htmlFor="edit-name"
                                    error={errors.name}
                                >
                                    <Input
                                        id="edit-name"
                                        value={name}
                                        onChange={(e) =>
                                            setName(e.target.value)
                                        }
                                        placeholder="e.g. Jane Doe"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.name !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Job Title"
                                    htmlFor="edit-job_title"
                                    error={errors.job_title}
                                >
                                    <Input
                                        id="edit-job_title"
                                        value={jobTitle}
                                        onChange={(e) =>
                                            setJobTitle(e.target.value)
                                        }
                                        placeholder="e.g. Senior Associate"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.job_title !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Organisation"
                                    htmlFor="edit-organisation"
                                    error={errors.organisation}
                                >
                                    <Input
                                        id="edit-organisation"
                                        value={organisation}
                                        onChange={(e) =>
                                            setOrganisation(e.target.value)
                                        }
                                        placeholder="e.g. ABC Law Firm"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.organisation !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                            </>
                        )}

                        <FormField
                            label="Testimonial Content"
                            htmlFor="edit-content"
                            error={errors.content}
                        >
                            <Textarea
                                id="edit-content"
                                value={content}
                                onChange={(e) => setContent(e.target.value)}
                                placeholder="Enter the testimonial quote..."
                                disabled={isSubmitting}
                                rows={4}
                                aria-invalid={
                                    errors.content !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Avatar"
                            htmlFor="edit-avatar"
                            error={errors.avatar}
                        >
                            {!isLinkedToUser ? (
                                <>
                                    <ThumbnailSelector
                                        name="avatar"
                                        removeFieldName="remove_avatar"
                                        currentOriginalUrl={testimonial.avatar}
                                        currentCropData={
                                            testimonial.avatar_crop
                                                ? {
                                                      x: testimonial.avatar_crop
                                                          .x,
                                                      y: testimonial.avatar_crop
                                                          .y,
                                                      width: testimonial
                                                          .avatar_crop.width,
                                                      height: testimonial
                                                          .avatar_crop.height,
                                                  }
                                                : null
                                        }
                                        aspectRatio={1}
                                        size="lg"
                                        error={errors.avatar}
                                        onCropDataChange={setAvatarCropData}
                                    />
                                    <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        Square image (1:1 ratio). Max 2MB.
                                    </p>
                                </>
                            ) : (
                                <>
                                    {testimonial.avatar && (
                                        <div className="mb-2">
                                            <img
                                                src={testimonial.avatar}
                                                alt="Current avatar"
                                                className="size-16 rounded-full object-cover"
                                            />
                                        </div>
                                    )}
                                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                        Avatar is from the linked user profile
                                    </p>
                                </>
                            )}
                        </FormField>

                        <div className="flex items-center justify-between">
                            <Label htmlFor="edit-is_published">Published</Label>
                            <Checkbox
                                id="edit-is_published"
                                checked={isPublished}
                                onCheckedChange={(checked) =>
                                    setIsPublished(checked === true)
                                }
                                disabled={isSubmitting}
                            />
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
