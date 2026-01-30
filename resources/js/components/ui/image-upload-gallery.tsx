import { type NormalizedImage } from '@/components/showcase/form/types';
import { cn } from '@/lib/utils';
import { ImagePlus, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

function generateId(): string {
    if (
        typeof crypto !== 'undefined' &&
        typeof crypto.randomUUID === 'function'
    ) {
        return crypto.randomUUID();
    }
    return `${Date.now()}-${Math.random().toString(36).substring(2, 11)}`;
}

interface ImageUploadGalleryProps {
    name: string;
    label?: string;
    labelIcon?: React.ReactNode;
    description?: string;
    className?: string;
    error?: string;
    imageErrors?: Record<string, string>;
    maxImages?: number;
    existingImages?: NormalizedImage[];
    /** Field name for tracking removed images (defaults to 'removed_images') */
    removedImagesFieldName?: string;
    /** Field name for tracking deleted new draft images (only for edit-draft mode) */
    deletedNewImagesFieldName?: string;
    onChange?: () => void;
}

interface GalleryImage {
    id: string;
    url: string;
    isExisting: boolean;
    /** For showcase images: the image ID. For draft kept images: the original image ID */
    originalImageId?: number | null;
    /** Whether this is a newly added draft image */
    isNewDraftImage?: boolean;
    /** The draft image ID (only for new draft images) */
    draftImageId?: number | null;
    file?: File;
}

function HiddenFileInput({ name, file }: { name: string; file: File }) {
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (inputRef.current !== null && file !== undefined) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            inputRef.current.files = dataTransfer.files;
        }
    }, [file]);

    return <input ref={inputRef} type="file" name={name} className="hidden" />;
}

export function ImageUploadGallery({
    name,
    label = 'Gallery',
    labelIcon,
    description,
    className,
    error,
    imageErrors,
    maxImages = 10,
    existingImages,
    removedImagesFieldName = 'removed_images',
    deletedNewImagesFieldName,
    onChange,
}: ImageUploadGalleryProps) {
    const [images, setImages] = useState<GalleryImage[]>(() => {
        if (existingImages === undefined || existingImages.length === 0) {
            return [];
        }
        return existingImages.map((img) => ({
            id: img.id,
            url: img.url,
            isExisting: true,
            originalImageId: img.originalImageId,
            isNewDraftImage: img.isNewDraftImage,
            draftImageId: img.draftImageId,
        }));
    });
    const [removedImageIds, setRemovedImageIds] = useState<number[]>([]);
    const [deletedNewDraftImageIds, setDeletedNewDraftImageIds] = useState<
        number[]
    >([]);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files ?? []);
        const remainingSlots = maxImages - images.length;
        const filesToAdd = files.slice(0, remainingSlots);

        const newImages: GalleryImage[] = filesToAdd.map((file) => ({
            id: generateId(),
            file,
            url: URL.createObjectURL(file),
            isExisting: false,
        }));

        setImages((prev) => [...prev, ...newImages]);
        onChange?.();

        if (fileInputRef.current !== null) {
            fileInputRef.current.value = '';
        }
    };

    const removeImage = (id: string) => {
        const imageIndex = images.findIndex((img) => img.id === id);
        const image = images[imageIndex];

        if (image === undefined) {
            return;
        }

        // Handle removal tracking based on image type
        if (image.isExisting === false) {
            // Newly uploaded file (not yet saved) - just revoke the object URL
            URL.revokeObjectURL(image.url);
        } else if (
            image.isNewDraftImage === true &&
            image.draftImageId !== null &&
            image.draftImageId !== undefined
        ) {
            // Existing draft image that was newly added - track in deleted_new_images
            setDeletedNewDraftImageIds((prev) => [...prev, image.draftImageId!]);
        } else if (
            image.originalImageId !== null &&
            image.originalImageId !== undefined
        ) {
            // Original/kept image - track in removed_images
            setRemovedImageIds((prev) => [...prev, image.originalImageId!]);
        }

        // Calculate new indices before updating state
        const newImagesLength = images.length - 1;
        const remainingImages = images.filter((img) => img.id !== id);

        // Update selected index
        if (newImagesLength === 0) {
            setSelectedIndex(0);
        } else if (selectedIndex >= newImagesLength) {
            setSelectedIndex(newImagesLength - 1);
        } else if (imageIndex < selectedIndex) {
            setSelectedIndex((prev) => prev - 1);
        }

        // Remove the image from state
        setImages(remainingImages);
        onChange?.();
    };

    const selectedImage = images[selectedIndex];
    const canAddMore = images.length < maxImages;
    const newImages = images.filter((img) => img.isExisting === false);

    const getImageError = (image: GalleryImage): string | undefined => {
        if (image.isExisting === true || imageErrors === undefined) {
            return undefined;
        }
        const newImageIndex = newImages.findIndex((img) => img.id === image.id);
        return imageErrors[`images.${newImageIndex}`];
    };

    return (
        <div className={cn('space-y-3', className)}>
            <p className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                {labelIcon}
                {label}
            </p>
            {description !== undefined && (
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    {description}
                </p>
            )}

            {images.length > 0 ? (
                <>
                    {/* Main preview */}
                    <div className="relative overflow-hidden rounded-lg">
                        <img
                            src={selectedImage.url}
                            alt={`Preview ${selectedIndex + 1}`}
                            className="aspect-video w-full object-cover"
                        />
                        <button
                            type="button"
                            onClick={() => removeImage(selectedImage.id)}
                            className="absolute top-2 right-2 rounded-full bg-black/50 p-1.5 text-white transition-colors hover:bg-black/70"
                        >
                            <X className="size-4" />
                        </button>
                    </div>

                    {/* Thumbnails + Add button */}
                    <div className="flex gap-2">
                        {images.map((image, index) => {
                            const hasError = getImageError(image) !== undefined;
                            return (
                                <button
                                    key={image.id}
                                    type="button"
                                    onClick={() => setSelectedIndex(index)}
                                    className={cn(
                                        'relative size-16 shrink-0 overflow-hidden rounded-md border-2 transition-all',
                                        hasError
                                            ? 'border-red-500 dark:border-red-500'
                                            : selectedIndex === index
                                              ? 'border-neutral-900 dark:border-white'
                                              : 'border-transparent opacity-70 hover:opacity-100',
                                    )}
                                >
                                    <img
                                        src={image.url}
                                        alt={`Thumbnail ${index + 1}`}
                                        className="size-full object-cover"
                                    />
                                </button>
                            );
                        })}

                        {canAddMore === true && (
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="flex size-16 shrink-0 items-center justify-center rounded-md border-2 border-dashed border-neutral-300 text-neutral-400 transition-colors hover:border-amber-400 hover:text-amber-500 dark:border-neutral-700 dark:hover:border-amber-500"
                            >
                                <ImagePlus className="size-5" />
                            </button>
                        )}
                    </div>
                </>
            ) : (
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className={cn(
                        'flex aspect-video w-full flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed bg-neutral-50 text-neutral-400 transition-colors hover:border-amber-400 hover:bg-amber-50 hover:text-amber-500 dark:bg-neutral-900 dark:hover:border-amber-500 dark:hover:bg-amber-950/20',
                        error !== undefined ||
                            (imageErrors !== undefined &&
                                Object.keys(imageErrors).length > 0)
                            ? 'border-red-300 dark:border-red-700'
                            : 'border-neutral-300 dark:border-neutral-700',
                    )}
                >
                    <ImagePlus className="size-8" />
                    <span className="text-sm">Click to add images</span>
                    <span className="text-xs">
                        Up to {maxImages} images, 4MB each, min 400×225px
                    </span>
                </button>
            )}

            <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                multiple
                className="hidden"
                onChange={handleFileChange}
            />

            {/* Hidden file inputs for new images only */}
            {newImages.map(
                (image, index) =>
                    image.file !== undefined && (
                        <HiddenFileInput
                            key={image.id}
                            name={`${name}[${index}]`}
                            file={image.file}
                        />
                    ),
            )}

            {/* Hidden inputs for removed image IDs */}
            {removedImageIds.map((id, index) => (
                <input
                    key={`removed-${id}`}
                    type="hidden"
                    name={`${removedImagesFieldName}[${index}]`}
                    value={id}
                />
            ))}

            {/* Hidden inputs for deleted new draft image IDs */}
            {deletedNewImagesFieldName !== undefined &&
                deletedNewDraftImageIds.map((id, index) => (
                    <input
                        key={`deleted-new-${id}`}
                        type="hidden"
                        name={`${deletedNewImagesFieldName}[${index}]`}
                        value={id}
                    />
                ))}

            {error !== undefined && (
                <p className="text-sm text-red-500 dark:text-red-400">
                    {error}
                </p>
            )}
            {imageErrors !== undefined &&
                Object.keys(imageErrors).length > 0 && (
                    <div className="space-y-1">
                        {Object.entries(imageErrors).map(([key, message]) => (
                            <p
                                key={key}
                                className="text-sm text-red-500 dark:text-red-400"
                            >
                                {message}
                            </p>
                        ))}
                    </div>
                )}
        </div>
    );
}
