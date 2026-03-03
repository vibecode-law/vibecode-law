import { type NormalizedImage } from '@/components/showcase/form/types';
import { cn } from '@/lib/utils';
import { Crop, ImagePlus, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
    ImageCropModal,
    type CropData,
} from '@/components/ui/image-crop-modal';

function generateId(): string {
    if (
        typeof crypto !== 'undefined' &&
        typeof crypto.randomUUID === 'function'
    ) {
        return crypto.randomUUID();
    }
    return `${Date.now()}-${Math.random().toString(36).substring(2, 11)}`;
}

type CropRegion = App.ValueObjects.ImageCrop;

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
    /** Whether to require cropping when uploading new images */
    requireCrop?: boolean;
    /** Aspect ratio for the crop modal (e.g. 16/9) */
    cropAspectRatio?: number;
    /** The crop region name (e.g. 'landscape') */
    cropName?: string;
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
    crops?: Record<string, CropRegion> | null;
    /** The original uncropped image URL (for re-cropping) */
    originalUrl?: string;
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
    requireCrop = false,
    cropAspectRatio = 16 / 9,
    cropName = 'landscape',
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
            crops: img.crops,
        }));
    });
    const [removedImageIds, setRemovedImageIds] = useState<number[]>([]);
    const [deletedNewDraftImageIds, setDeletedNewDraftImageIds] = useState<
        number[]
    >([]);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const fileInputRef = useRef<HTMLInputElement>(null);

    // Crop modal state
    const [cropModalOpen, setCropModalOpen] = useState(false);
    const [pendingCropFiles, setPendingCropFiles] = useState<File[]>([]);
    const [pendingCropUrls, setPendingCropUrls] = useState<string[]>([]);
    const [currentCropIndex, setCurrentCropIndex] = useState(0);

    // Re-crop modal state
    const [recropModalOpen, setRecropModalOpen] = useState(false);
    const [recropImageId, setRecropImageId] = useState<string | null>(null);

    // Track which existing images have had their crops changed
    const [changedExistingImageIds, setChangedExistingImageIds] = useState<
        Set<string>
    >(new Set());

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = Array.from(e.target.files ?? []);
        const remainingSlots = maxImages - images.length;
        const filesToAdd = files.slice(0, remainingSlots);

        if (filesToAdd.length === 0) {
            return;
        }

        if (requireCrop === true) {
            const urls = filesToAdd.map((file) => URL.createObjectURL(file));
            setPendingCropFiles(filesToAdd);
            setPendingCropUrls(urls);
            setCurrentCropIndex(0);
            setCropModalOpen(true);
        } else {
            const newImages: GalleryImage[] = filesToAdd.map((file) => ({
                id: generateId(),
                file,
                url: URL.createObjectURL(file),
                isExisting: false,
            }));

            setImages((prev) => [...prev, ...newImages]);
            onChange?.();
        }

        if (fileInputRef.current !== null) {
            fileInputRef.current.value = '';
        }
    };

    const handleCropComplete = (croppedFile: File, cropData: CropData) => {
        const crops: Record<string, CropRegion> = {
            [cropName]: {
                x: cropData.x,
                y: cropData.y,
                width: cropData.width,
                height: cropData.height,
            },
        };

        const newImage: GalleryImage = {
            id: generateId(),
            file: pendingCropFiles[currentCropIndex],
            url: URL.createObjectURL(croppedFile),
            isExisting: false,
            crops,
            originalUrl: pendingCropUrls[currentCropIndex],
        };

        setImages((prev) => [...prev, newImage]);

        const nextIndex = currentCropIndex + 1;

        if (nextIndex < pendingCropFiles.length) {
            setCurrentCropIndex(nextIndex);
        } else {
            cleanupPendingCrop();
            onChange?.();
        }
    };

    const cleanupPendingCrop = () => {
        // Don't revoke pending crop URLs — they're stored as originalUrl for re-cropping
        setPendingCropFiles([]);
        setPendingCropUrls([]);
        setCurrentCropIndex(0);
        setCropModalOpen(false);
    };

    const handleCropCancel = () => {
        cleanupPendingCrop();
    };

    const handleChangeCrop = (imageId: string) => {
        setRecropImageId(imageId);
        setRecropModalOpen(true);
    };

    const handleRecropComplete = (croppedFile: File, cropData: CropData) => {
        const image = images.find((img) => img.id === recropImageId);

        if (image === undefined) {
            return;
        }

        const newCrops: Record<string, CropRegion> = {
            [cropName]: {
                x: cropData.x,
                y: cropData.y,
                width: cropData.width,
                height: cropData.height,
            },
        };

        if (image.isExisting === false) {
            // New image: update preview and crops, keep original file
            const newUrl = URL.createObjectURL(croppedFile);

            setImages((prev) =>
                prev.map((img) =>
                    img.id === recropImageId
                        ? { ...img, url: newUrl, crops: newCrops }
                        : img,
                ),
            );
        } else {
            // Existing image: update crops and url (cropped blob for preview), store server URL as originalUrl
            const newUrl = URL.createObjectURL(croppedFile);

            setImages((prev) =>
                prev.map((img) =>
                    img.id === recropImageId
                        ? {
                              ...img,
                              url: newUrl,
                              crops: newCrops,
                              originalUrl: img.originalUrl ?? img.url,
                          }
                        : img,
                ),
            );

            setChangedExistingImageIds((prev) => {
                const next = new Set(prev);
                next.add(image.id);
                return next;
            });
        }

        setRecropModalOpen(false);
        setRecropImageId(null);
        onChange?.();
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
                        {requireCrop === true && (
                            <button
                                type="button"
                                onClick={() =>
                                    handleChangeCrop(selectedImage.id)
                                }
                                className="absolute bottom-2 left-2 flex items-center gap-1.5 rounded-full bg-white/90 py-1.5 pr-3 pl-2.5 text-sm font-medium text-neutral-800 shadow-sm transition-colors hover:bg-white dark:bg-neutral-800/90 dark:text-neutral-200 dark:hover:bg-neutral-800"
                            >
                                <Crop className="size-4" />
                                Re-crop
                            </button>
                        )}
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

            {/* Hidden inputs for crop data */}
            {newImages.map((image, index) =>
                image.crops !== null &&
                image.crops !== undefined
                    ? Object.entries(image.crops).map(
                          ([regionName, region]) => (
                              <div key={`crops-${image.id}-${regionName}`}>
                                  <input
                                      type="hidden"
                                      name={`image_crops[${index}][${regionName}][x]`}
                                      value={region.x}
                                  />
                                  <input
                                      type="hidden"
                                      name={`image_crops[${index}][${regionName}][y]`}
                                      value={region.y}
                                  />
                                  <input
                                      type="hidden"
                                      name={`image_crops[${index}][${regionName}][width]`}
                                      value={region.width}
                                  />
                                  <input
                                      type="hidden"
                                      name={`image_crops[${index}][${regionName}][height]`}
                                      value={region.height}
                                  />
                              </div>
                          ),
                      )
                    : null,
            )}

            {/* Hidden inputs for existing image crop updates */}
            {images
                .filter(
                    (image) =>
                        image.isExisting === true &&
                        changedExistingImageIds.has(image.id) &&
                        image.crops !== null &&
                        image.crops !== undefined,
                )
                .map((image) => {
                    const idKey =
                        image.originalImageId !== null &&
                        image.originalImageId !== undefined
                            ? image.originalImageId
                            : null;
                    const draftIdKey =
                        image.isNewDraftImage === true &&
                        image.draftImageId !== null &&
                        image.draftImageId !== undefined
                            ? image.draftImageId
                            : null;

                    if (idKey === null && draftIdKey === null) {
                        return null;
                    }

                    const fieldPrefix =
                        draftIdKey !== null && idKey === null
                            ? `draft_image_crop_updates[${draftIdKey}]`
                            : `image_crop_updates[${idKey}]`;

                    return Object.entries(image.crops!).map(
                        ([regionName, region]) => (
                            <div
                                key={`crop-update-${image.id}-${regionName}`}
                            >
                                <input
                                    type="hidden"
                                    name={`${fieldPrefix}[${regionName}][x]`}
                                    value={region.x}
                                />
                                <input
                                    type="hidden"
                                    name={`${fieldPrefix}[${regionName}][y]`}
                                    value={region.y}
                                />
                                <input
                                    type="hidden"
                                    name={`${fieldPrefix}[${regionName}][width]`}
                                    value={region.width}
                                />
                                <input
                                    type="hidden"
                                    name={`${fieldPrefix}[${regionName}][height]`}
                                    value={region.height}
                                />
                            </div>
                        ),
                    );
                })}

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

            {/* Crop modal for new image uploads */}
            {requireCrop === true && (
                <>
                    <ImageCropModal
                        open={cropModalOpen}
                        onOpenChange={(open) => {
                            if (open === false) {
                                handleCropCancel();
                            }
                        }}
                        imageUrl={pendingCropUrls[currentCropIndex] ?? null}
                        aspectRatio={cropAspectRatio}
                        onCropComplete={handleCropComplete}
                        onChangeImage={() => fileInputRef.current?.click()}
                        stepInfo={
                            pendingCropFiles.length > 1
                                ? {
                                      current: currentCropIndex + 1,
                                      total: pendingCropFiles.length,
                                      label: 'Crop Image',
                                  }
                                : undefined
                        }
                    />

                    {/* Re-crop modal for existing images */}
                    <ImageCropModal
                        open={recropModalOpen}
                        onOpenChange={(open) => {
                            if (open === false) {
                                setRecropModalOpen(false);
                                setRecropImageId(null);
                            }
                        }}
                        imageUrl={
                            recropImageId !== null
                                ? (images.find(
                                      (img) => img.id === recropImageId,
                                  )?.originalUrl ??
                                  images.find(
                                      (img) => img.id === recropImageId,
                                  )?.url ??
                                  null)
                                : null
                        }
                        aspectRatio={cropAspectRatio}
                        initialCropData={
                            recropImageId !== null
                                ? (images.find(
                                      (img) => img.id === recropImageId,
                                  )?.crops?.[cropName] ?? null)
                                : null
                        }
                        onCropComplete={handleRecropComplete}
                        onChangeImage={() => {}}
                    />
                </>
            )}
        </div>
    );
}
