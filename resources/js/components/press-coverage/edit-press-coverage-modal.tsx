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
import { Label } from '@/components/ui/label';
import { SubmitButton } from '@/components/ui/submit-button';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { type CropData } from '@/components/ui/image-crop-modal';
import { useModalForm } from '@/hooks/use-modal-form';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface EditPressCoverageModalProps {
    pressCoverage: App.Http.Resources.PressCoverageResource;
    updateUrl: string;
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}

export function EditPressCoverageModal({
    pressCoverage,
    updateUrl,
    isOpen,
    onOpenChange,
}: EditPressCoverageModalProps) {
    const { isSubmitting, setIsSubmitting, errors, setErrors, clearErrors } =
        useModalForm<{
            title?: string;
            publication_name?: string;
            publication_date?: string;
            url?: string;
            excerpt?: string;
            thumbnail?: string;
            display_order?: string;
        }>();

    const [title, setTitle] = useState(pressCoverage.title);
    const [publicationName, setPublicationName] = useState(
        pressCoverage.publication_name,
    );
    const [publicationDate, setPublicationDate] = useState(
        pressCoverage.publication_date,
    );
    const [url, setUrl] = useState(pressCoverage.url);
    const [excerpt, setExcerpt] = useState(pressCoverage.excerpt ?? '');
    const [displayOrder, setDisplayOrder] = useState(
        String(pressCoverage.display_order),
    );
    const [isPublished, setIsPublished] = useState(pressCoverage.is_published);
    const [thumbnailCropData, setThumbnailCropData] = useState<CropData | null>(
        null,
    );
    const [lastPressCoverageId, setLastPressCoverageId] = useState(
        pressCoverage.id,
    );

    // Reset form when a different press coverage is selected (during render, not in effect)
    if (pressCoverage.id !== lastPressCoverageId) {
        setLastPressCoverageId(pressCoverage.id);
        setTitle(pressCoverage.title);
        setPublicationName(pressCoverage.publication_name);
        setPublicationDate(pressCoverage.publication_date);
        setUrl(pressCoverage.url);
        setExcerpt(pressCoverage.excerpt ?? '');
        setDisplayOrder(String(pressCoverage.display_order));
        setIsPublished(pressCoverage.is_published);
        setThumbnailCropData(null);
        clearErrors();
    }

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        setIsSubmitting(true);
        setErrors({});

        const formElement = e.target as HTMLFormElement;
        const formData = new FormData(formElement);
        formData.append('title', title);
        formData.append('publication_name', publicationName);
        formData.append('publication_date', publicationDate);
        formData.append('url', url);
        formData.append('excerpt', excerpt);
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
                        title?: string;
                        publication_name?: string;
                        publication_date?: string;
                        url?: string;
                        excerpt?: string;
                        thumbnail?: string;
                        display_order?: string;
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
        setTitle(pressCoverage.title);
        setPublicationName(pressCoverage.publication_name);
        setPublicationDate(pressCoverage.publication_date);
        setUrl(pressCoverage.url);
        setExcerpt(pressCoverage.excerpt ?? '');
        setDisplayOrder(String(pressCoverage.display_order));
        setIsPublished(pressCoverage.is_published);
        setThumbnailCropData(null);
        clearErrors();
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Edit Press Coverage</DialogTitle>
                        <DialogDescription>
                            Update the press coverage details.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4 space-y-4">
                        <div className="flex gap-4">
                            <FormField
                                label="Thumbnail"
                                htmlFor="thumbnail"
                                error={errors.thumbnail}
                            >
                                <ThumbnailSelector
                                    name="thumbnail"
                                    currentOriginalUrl={
                                        pressCoverage.thumbnail_url
                                    }
                                    currentCropData={
                                        pressCoverage.thumbnail_crop
                                            ? {
                                                  x: pressCoverage.thumbnail_crop
                                                      .x,
                                                  y: pressCoverage.thumbnail_crop
                                                      .y,
                                                  width: pressCoverage
                                                      .thumbnail_crop.width,
                                                  height: pressCoverage
                                                      .thumbnail_crop.height,
                                              }
                                            : null
                                    }
                                    aspectRatio={1}
                                    size="lg"
                                    error={errors.thumbnail}
                                    onCropDataChange={setThumbnailCropData}
                                />
                                <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Square image (1:1 ratio). Max 2MB.
                                </p>
                            </FormField>

                            <div className="flex-1 space-y-4">
                                <FormField
                                    label="Title"
                                    htmlFor="edit-title"
                                    error={errors.title}
                                    required
                                >
                                    <Input
                                        id="edit-title"
                                        value={title}
                                        onChange={(e) =>
                                            setTitle(e.target.value)
                                        }
                                        placeholder="e.g. Revolutionizing Legal Tech"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.title !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Publication Name"
                                    htmlFor="edit-publication_name"
                                    error={errors.publication_name}
                                    required
                                >
                                    <Input
                                        id="edit-publication_name"
                                        value={publicationName}
                                        onChange={(e) =>
                                            setPublicationName(e.target.value)
                                        }
                                        placeholder="e.g. Legal Tech News"
                                        disabled={isSubmitting}
                                        aria-invalid={
                                            errors.publication_name !== undefined
                                                ? true
                                                : undefined
                                        }
                                    />
                                </FormField>
                            </div>
                        </div>

                        <FormField
                            label="Publication Date"
                            htmlFor="edit-publication_date"
                            error={errors.publication_date}
                            required
                        >
                            <Input
                                id="edit-publication_date"
                                type="date"
                                value={publicationDate}
                                onChange={(e) =>
                                    setPublicationDate(e.target.value)
                                }
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.publication_date !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Article URL"
                            htmlFor="edit-url"
                            error={errors.url}
                            required
                        >
                            <Input
                                id="edit-url"
                                type="url"
                                value={url}
                                onChange={(e) => setUrl(e.target.value)}
                                placeholder="https://example.com/article"
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.url !== undefined ? true : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Excerpt"
                            htmlFor="edit-excerpt"
                            error={errors.excerpt}
                        >
                            <Textarea
                                id="edit-excerpt"
                                value={excerpt}
                                onChange={(e) => setExcerpt(e.target.value)}
                                placeholder="Optional brief description or quote from the article..."
                                disabled={isSubmitting}
                                rows={3}
                                aria-invalid={
                                    errors.excerpt !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>

                        <FormField
                            label="Display Order"
                            htmlFor="edit-display_order"
                            error={errors.display_order}
                        >
                            <Input
                                id="edit-display_order"
                                type="number"
                                value={displayOrder}
                                onChange={(e) => setDisplayOrder(e.target.value)}
                                placeholder="0"
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.display_order !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                            <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Lower numbers appear first
                            </p>
                        </FormField>

                        <div className="flex items-center justify-between">
                            <Label htmlFor="edit-is_published">Published</Label>
                            <Checkbox
                                id="edit-is_published"
                                checked={isPublished}
                                onCheckedChange={setIsPublished}
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
