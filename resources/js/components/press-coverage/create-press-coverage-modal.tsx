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
import { Label } from '@/components/ui/label';
import { SubmitButton } from '@/components/ui/submit-button';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { ThumbnailSelector } from '@/components/ui/thumbnail-selector';
import { useModalForm } from '@/hooks/use-modal-form';
import { type CropData } from '@/components/ui/image-crop-modal';
import { router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';

interface CreatePressCoverageModalProps {
    storeUrl: string;
}

export function CreatePressCoverageModal({
    storeUrl,
}: CreatePressCoverageModalProps) {
    const {
        isOpen,
        handleOpenChange: baseHandleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
    } = useModalForm<{
        title?: string;
        publication_name?: string;
        publication_date?: string;
        url?: string;
        excerpt?: string;
        thumbnail?: string;
        display_order?: string;
    }>();

    const [title, setTitle] = useState('');
    const [publicationName, setPublicationName] = useState('');
    const [publicationDate, setPublicationDate] = useState('');
    const [url, setUrl] = useState('');
    const [excerpt, setExcerpt] = useState('');
    const [displayOrder, setDisplayOrder] = useState('0');
    const [isPublished, setIsPublished] = useState(false);
    const [thumbnailCropData, setThumbnailCropData] = useState<CropData | null>(
        null,
    );

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

        router.post(storeUrl, formData, {
            onSuccess: () => {
                baseHandleOpenChange(false);
                setTitle('');
                setPublicationName('');
                setPublicationDate('');
                setUrl('');
                setExcerpt('');
                setDisplayOrder('0');
                setIsPublished(false);
                setThumbnailCropData(null);
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
        if (open === false) {
            setTitle('');
            setPublicationName('');
            setPublicationDate('');
            setUrl('');
            setExcerpt('');
            setDisplayOrder('0');
            setIsPublished(false);
            setThumbnailCropData(null);
        }
        baseHandleOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button>
                    <Plus className="size-4" />
                    Add Press Coverage
                </Button>
            </DialogTrigger>
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Create Press Coverage</DialogTitle>
                        <DialogDescription>
                            Add a new press article to the Wall of Love.
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
                                    htmlFor="title"
                                    error={errors.title}
                                    required
                                >
                                    <Input
                                        id="title"
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
                                    htmlFor="publication_name"
                                    error={errors.publication_name}
                                    required
                                >
                                    <Input
                                        id="publication_name"
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
                            htmlFor="publication_date"
                            error={errors.publication_date}
                            required
                        >
                            <Input
                                id="publication_date"
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
                            htmlFor="url"
                            error={errors.url}
                            required
                        >
                            <Input
                                id="url"
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
                            htmlFor="excerpt"
                            error={errors.excerpt}
                        >
                            <Textarea
                                id="excerpt"
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
                            htmlFor="display_order"
                            error={errors.display_order}
                        >
                            <Input
                                id="display_order"
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
                            <Label htmlFor="is_published">
                                Publish Immediately
                            </Label>
                            <Checkbox
                                id="is_published"
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
