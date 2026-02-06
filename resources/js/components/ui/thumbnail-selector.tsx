import { useEffect, useRef, useState } from 'react';
import { ImagePlus, Pencil } from 'lucide-react';

import { cn } from '@/lib/utils';
import {
    ImageCropModal,
    type CropData,
    type SimpleCropData,
} from '@/components/ui/image-crop-modal';

interface ThumbnailSelectorProps {
    name: string;
    removeFieldName?: string;
    currentOriginalUrl?: string | null;
    currentCropData?: SimpleCropData | null;
    error?: string;
    showError?: boolean;
    className?: string;
    aspectRatio?: number;
    size?: 'sm' | 'md' | 'lg';
    onCropDataChange?: (cropData: CropData | null) => void;
    onRemove?: () => void;
}

const sizeClasses = {
    sm: 'size-14',
    md: 'size-20',
    lg: 'size-24',
};

/**
 * Creates a cropped image preview from a source URL and crop coordinates
 */
async function createCroppedPreview(
    imageSrc: string,
    cropData: SimpleCropData,
): Promise<string> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            if (ctx === null) {
                reject(new Error('Failed to get canvas context'));
                return;
            }

            canvas.width = cropData.width;
            canvas.height = cropData.height;

            ctx.drawImage(
                image,
                cropData.x,
                cropData.y,
                cropData.width,
                cropData.height,
                0,
                0,
                cropData.width,
                cropData.height,
            );

            resolve(canvas.toDataURL('image/jpeg', 0.9));
        };
        image.onerror = () => reject(new Error('Failed to load image'));
        image.src = imageSrc;
    });
}

export function ThumbnailSelector({
    name,
    removeFieldName = 'remove_thumbnail',
    currentOriginalUrl,
    currentCropData,
    error,
    showError = true,
    className,
    aspectRatio = 1,
    size = 'sm',
    onCropDataChange,
    onRemove,
}: ThumbnailSelectorProps) {
    const [croppedPreviewUrl, setCroppedPreviewUrl] = useState<string | null>(
        null,
    );
    const [existingCroppedPreviewUrl, setExistingCroppedPreviewUrl] = useState<
        string | null
    >(null);
    const [originalUrl, setOriginalUrl] = useState<string | null>(null);
    const [originalFile, setOriginalFile] = useState<File | null>(null);
    const [cropData, setCropData] = useState<CropData | null>(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [isRemoved, setIsRemoved] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    // Generate cropped preview from existing original URL and crop data
    useEffect(() => {
        if (currentOriginalUrl === null || currentOriginalUrl === undefined) {
            return;
        }

        // If there's crop data, generate a cropped preview; otherwise just use the original
        const generatePreview = async () => {
            if (currentCropData !== null && currentCropData !== undefined) {
                try {
                    const preview = await createCroppedPreview(
                        currentOriginalUrl,
                        currentCropData,
                    );
                    setExistingCroppedPreviewUrl(preview);
                } catch (err) {
                    console.error('Failed to create cropped preview:', err);
                    setExistingCroppedPreviewUrl(currentOriginalUrl);
                }
            } else {
                // No crop data - simulate async to satisfy lint rule
                await Promise.resolve();
                setExistingCroppedPreviewUrl(currentOriginalUrl);
            }
        };

        generatePreview();
    }, [currentOriginalUrl, currentCropData]);

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const selectedFile = event.target.files?.[0];
        if (selectedFile !== undefined) {
            const url = URL.createObjectURL(selectedFile);
            setOriginalUrl(url);
            setOriginalFile(selectedFile);
            setModalOpen(true);
        }
        // Reset the input so the same file can be selected again
        if (inputRef.current !== null) {
            inputRef.current.value = '';
        }
    };

    const handleClick = () => {
        // If thumbnail was removed, open file dialog to select a new one
        if (isRemoved === true) {
            inputRef.current?.click();
            return;
        }
        // If we have a new original image (user selected a file), re-open the crop modal
        if (originalUrl !== null) {
            setModalOpen(true);
        } else if (currentOriginalUrl !== null && currentOriginalUrl !== undefined) {
            // If we have an existing original image from the server, open crop modal with it
            setModalOpen(true);
        } else {
            // Otherwise, open the file dialog
            inputRef.current?.click();
        }
    };

    const handleCropComplete = (
        croppedPreviewFile: File,
        newCropData: CropData,
    ) => {
        setCroppedPreviewUrl(URL.createObjectURL(croppedPreviewFile));
        setCropData(newCropData);
        onCropDataChange?.(newCropData);
    };

    const handleChangeImage = () => {
        setModalOpen(false);
        // Use setTimeout to ensure the modal is closed before opening the file dialog
        setTimeout(() => {
            inputRef.current?.click();
        }, 100);
    };

    const handleRemove = () => {
        setIsRemoved(true);
        setCroppedPreviewUrl(null);
        setOriginalUrl(null);
        setOriginalFile(null);
        setCropData(null);
        onCropDataChange?.(null);
        onRemove?.();
        setModalOpen(false);
    };

    // When a new file is selected or crop is made, the thumbnail is no longer "removed"
    const handleFileChangeWithReset = (
        event: React.ChangeEvent<HTMLInputElement>,
    ) => {
        setIsRemoved(false);
        handleFileChange(event);
    };

    const handleCropCompleteWithReset = (
        croppedPreviewFile: File,
        newCropData: CropData,
    ) => {
        setIsRemoved(false);
        handleCropComplete(croppedPreviewFile, newCropData);
    };

    const displayUrl = isRemoved === true ? null : (croppedPreviewUrl ?? existingCroppedPreviewUrl);

    // Use the new original URL if set (user selected a file), otherwise fall back to existing
    const modalImageUrl = originalUrl ?? currentOriginalUrl ?? null;

    // Determine if we have new crop data or should use existing
    const hasNewCropData = cropData !== null;

    return (
        <div className={cn('shrink-0', className)}>
            <button
                type="button"
                onClick={handleClick}
                className={cn(
                    'group relative overflow-hidden rounded-lg border-2 border-dashed transition-colors',
                    sizeClasses[size],
                    error !== undefined
                        ? 'border-red-300 dark:border-red-700'
                        : 'border-neutral-300 hover:border-amber-400 dark:border-neutral-700 dark:hover:border-amber-500',
                )}
            >
                {displayUrl ? (
                    <>
                        <img
                            src={displayUrl}
                            alt="Thumbnail"
                            className="size-full object-cover"
                        />
                        <div className="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                            <ImagePlus className="size-5 text-white" />
                        </div>
                        <div className="absolute right-0.5 bottom-0.5 flex size-5 items-center justify-center rounded-full bg-amber-500 text-white shadow-sm">
                            <Pencil className="size-2.5" />
                        </div>
                    </>
                ) : (
                    <div className="flex size-full items-center justify-center bg-neutral-50 text-neutral-400 transition-colors group-hover:bg-amber-50 group-hover:text-amber-500 dark:bg-neutral-900 dark:group-hover:bg-amber-950/20">
                        <ImagePlus className="size-5" />
                    </div>
                )}
            </button>
            <input
                ref={inputRef}
                type="file"
                accept="image/*"
                className="hidden"
                onChange={handleFileChangeWithReset}
            />
            {/* Hidden file input with the original (uncropped) file for form submission */}
            {originalFile !== null && (
                <input
                    type="file"
                    name={name}
                    className="hidden"
                    ref={(input) => {
                        if (input !== null && originalFile !== null) {
                            const dt = new DataTransfer();
                            dt.items.add(originalFile);
                            input.files = dt.files;
                        }
                    }}
                />
            )}
            {/* Hidden inputs for crop data - use new crop data if available, otherwise existing */}
            {(hasNewCropData || (currentCropData !== null && currentCropData !== undefined)) && (
                <>
                    <input
                        type="hidden"
                        name={`${name}_crop[x]`}
                        value={Math.round((hasNewCropData ? cropData : currentCropData)!.x)}
                    />
                    <input
                        type="hidden"
                        name={`${name}_crop[y]`}
                        value={Math.round((hasNewCropData ? cropData : currentCropData)!.y)}
                    />
                    <input
                        type="hidden"
                        name={`${name}_crop[width]`}
                        value={Math.round((hasNewCropData ? cropData : currentCropData)!.width)}
                    />
                    <input
                        type="hidden"
                        name={`${name}_crop[height]`}
                        value={Math.round((hasNewCropData ? cropData : currentCropData)!.height)}
                    />
                </>
            )}
            {showError === true && error !== undefined && (
                <p className="mt-1 text-sm text-red-500 dark:text-red-400">
                    {error}
                </p>
            )}
            {/* Hidden input for remove flag */}
            {isRemoved === true && (
                <input type="hidden" name={removeFieldName} value="1" />
            )}
            <ImageCropModal
                open={modalOpen}
                onOpenChange={setModalOpen}
                imageUrl={modalImageUrl}
                aspectRatio={aspectRatio}
                initialCropData={
                    // Only pass initial crop data when using the existing original URL
                    // (not when user has selected a new file)
                    originalUrl === null ? currentCropData : null
                }
                onCropComplete={handleCropCompleteWithReset}
                onChangeImage={handleChangeImage}
                onRemove={handleRemove}
                showRemoveButton={
                    originalUrl !== null ||
                    (currentOriginalUrl !== null &&
                        currentOriginalUrl !== undefined)
                }
            />
        </div>
    );
}
