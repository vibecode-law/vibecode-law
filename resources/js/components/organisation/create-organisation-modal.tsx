import StoreController from '@/actions/App/Http/Controllers/Staff/Organisations/StoreController';
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
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { SubmitButton } from '@/components/ui/submit-button';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { useModalForm } from '@/hooks/use-modal-form';
import { type FlashData } from '@/types';
import { router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';

interface CreateOrganisationModalProps {
    onCreated: (organisation: { id: number; name: string }) => void;
}

export function CreateOrganisationModal({
    onCreated,
}: CreateOrganisationModalProps) {
    const {
        isOpen,
        handleOpenChange: baseHandleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
    } = useModalForm<{
        name?: string;
        tagline?: string;
        about?: string;
        thumbnail?: string;
    }>();

    const [name, setName] = useState('');
    const [tagline, setTagline] = useState('');
    const [about, setAbout] = useState('');

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        const formData = new FormData(e.currentTarget);

        router.post(StoreController.url(), formData, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = (page.props as unknown as { flash: FlashData })
                    .flash;

                if (flash.created_organisation) {
                    onCreated(flash.created_organisation);
                }

                baseHandleOpenChange(false);
                setName('');
                setTagline('');
                setAbout('');
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
        if (open === false) {
            setName('');
            setTagline('');
            setAbout('');
        }
        baseHandleOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button type="button" variant="outline">
                    <Plus className="size-4" />
                    New
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create Organisation</DialogTitle>
                        <DialogDescription>
                            Add a new organisation for challenges.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <FormField
                            label="Name"
                            htmlFor="org-name"
                            error={errors.name}
                            required
                        >
                            <Input
                                id="org-name"
                                name="name"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder="e.g. Acme Legal"
                                disabled={isSubmitting}
                                maxLength={255}
                                aria-invalid={
                                    errors.name !== undefined ? true : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Tagline"
                            htmlFor="org-tagline"
                            error={errors.tagline}
                            required
                        >
                            <Input
                                id="org-tagline"
                                name="tagline"
                                value={tagline}
                                onChange={(e) => setTagline(e.target.value)}
                                placeholder="A short description"
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
                            htmlFor="org-about"
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
