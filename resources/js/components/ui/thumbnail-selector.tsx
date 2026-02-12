import { useEffect, useRef, useState } from 'react';
import { ImagePlus, Pencil } from 'lucide-react';

import { cn } from '@/lib/utils';
import {
    ImageCropModal,
    type CropData,
    type SimpleCropData,
} from '@/components/ui/image-crop-modal';

export interface CropConfig {
    key: string;
    label: string;
    aspectRatio: number;
}

interface ThumbnailSelectorProps {
    name: string;
    removeFieldName?: string;
    currentOriginalUrl?: string | null;
    currentCropData?:
        | SimpleCropData
        | Record<string, SimpleCropData>
        | null;
    error?: string;
    showError?: boolean;
    className?: string;
    aspectRatio?: number;
    crops?: CropConfig[];
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

function isMultiCropData(
    data: SimpleCropData | Record<string, SimpleCropData> | null | undefined,
): data is Record<string, SimpleCropData> {
    if (data === null || data === undefined) {
        return false;
    }
    return !('x' in data);
}

function getFirstCropData(
    data: SimpleCropData | Record<string, SimpleCropData> | null | undefined,
): SimpleCropData | null {
    if (data === null || data === undefined) {
        return null;
    }
    if (!isMultiCropData(data)) {
        return data;
    }
    const keys = Object.keys(data);
    return keys.length > 0 ? data[keys[0]] : null;
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
    crops,
    size = 'sm',
    onCropDataChange,
    onRemove,
}: ThumbnailSelectorProps) {
    const isMultiCrop = crops !== undefined && crops.length > 0;

    const [croppedPreviewUrl, setCroppedPreviewUrl] = useState<string | null>(
        null,
    );
    const [existingCroppedPreviewUrl, setExistingCroppedPreviewUrl] = useState<
        string | null
    >(null);
    const [originalUrl, setOriginalUrl] = useState<string | null>(null);
    const [originalFile, setOriginalFile] = useState<File | null>(null);

    // Single crop state
    const [cropData, setCropData] = useState<CropData | null>(null);

    // Multi-crop state
    const [cropsData, setCropsData] = useState<Record<string, CropData>>({});
    const [currentCropIndex, setCurrentCropIndex] = useState(0);

    const [modalOpen, setModalOpen] = useState(false);
    const [isRemoved, setIsRemoved] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    // Generate cropped preview from existing original URL and crop data
    useEffect(() => {
        if (currentOriginalUrl === null || currentOriginalUrl === undefined) {
            return;
        }

        const generatePreview = async () => {
            const previewCrop = getFirstCropData(currentCropData);
            if (previewCrop !== null) {
                try {
                    const preview = await createCroppedPreview(
                        currentOriginalUrl,
                        previewCrop,
                    );
                    setExistingCroppedPreviewUrl(preview);
                } catch (err) {
                    console.error('Failed to create cropped preview:', err);
                    setExistingCroppedPreviewUrl(currentOriginalUrl);
                }
            } else {
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
            if (isMultiCrop) {
                setCurrentCropIndex(0);
                setCropsData({});
            }
            setModalOpen(true);
        }
        if (inputRef.current !== null) {
            inputRef.current.value = '';
        }
    };

    const handleClick = () => {
        if (isRemoved === true) {
            inputRef.current?.click();
            return;
        }
        if (originalUrl !== null) {
            if (isMultiCrop) {
                setCurrentCropIndex(0);
            }
            setModalOpen(true);
        } else if (
            currentOriginalUrl !== null &&
            currentOriginalUrl !== undefined
        ) {
            if (isMultiCrop) {
                setCurrentCropIndex(0);
            }
            setModalOpen(true);
        } else {
            inputRef.current?.click();
        }
    };

    const handleCropComplete = (
        croppedPreviewFile: File,
        newCropData: CropData,
    ) => {
        if (isMultiCrop && crops !== undefined) {
            const currentKey = crops[currentCropIndex].key;
            const updatedCrops = { ...cropsData, [currentKey]: newCropData };
            setCropsData(updatedCrops);

            if (currentCropIndex < crops.length - 1) {
                // Move to next crop
                setCurrentCropIndex(currentCropIndex + 1);
            } else {
                // All crops done - generate preview from first crop
                setCroppedPreviewUrl(
                    URL.createObjectURL(croppedPreviewFile),
                );
                onCropDataChange?.(newCropData);
                setModalOpen(false);
            }
        } else {
            setCroppedPreviewUrl(URL.createObjectURL(croppedPreviewFile));
            setCropData(newCropData);
            onCropDataChange?.(newCropData);
            setModalOpen(false);
        }
    };

    const handleBack = () => {
        if (isMultiCrop && currentCropIndex > 0) {
            setCurrentCropIndex(currentCropIndex - 1);
            setModalOpen(true);
        }
    };

    const handleChangeImage = () => {
        setModalOpen(false);
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
        setCropsData({});
        onCropDataChange?.(null);
        onRemove?.();
        setModalOpen(false);
    };

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

    const displayUrl =
        isRemoved === true
            ? null
            : (croppedPreviewUrl ?? existingCroppedPreviewUrl);

    const modalImageUrl = originalUrl ?? currentOriginalUrl ?? null;

    // For single-crop mode
    const hasNewCropData = cropData !== null;
    const singleCurrentCropData = !isMultiCropData(currentCropData)
        ? currentCropData
        : null;

    // Multi-crop: determine which existing crop data to pass as initial
    const multiCurrentCropData = isMultiCropData(currentCropData)
        ? currentCropData
        : null;

    // Current crop config for multi-crop mode
    const currentCrop = isMultiCrop && crops !== undefined ? crops[currentCropIndex] : null;

    // Get initial crop data for the current modal
    const getInitialCropData = (): SimpleCropData | null | undefined => {
        if (originalUrl !== null) {
            // New file selected - check if we already cropped this key
            if (isMultiCrop && currentCrop !== null) {
                return cropsData[currentCrop.key] ?? null;
            }
            return null;
        }
        // Existing image - use saved data
        if (isMultiCrop && currentCrop !== null && multiCurrentCropData !== null) {
            return multiCurrentCropData[currentCrop.key] ?? null;
        }
        if (isMultiCrop && currentCrop !== null) {
            return cropsData[currentCrop.key] ?? null;
        }
        return singleCurrentCropData;
    };

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
            {/* Hidden inputs for crop data */}
            {isMultiCrop === true && crops !== undefined
                ? // Multi-crop hidden inputs
                  crops.map((cropConfig) => {
                      const data =
                          cropsData[cropConfig.key] ??
                          (multiCurrentCropData !== null
                              ? multiCurrentCropData[cropConfig.key]
                              : null);
                      if (data === null || data === undefined) {
                          return null;
                      }
                      return (
                          <div key={cropConfig.key}>
                              <input
                                  type="hidden"
                                  name={`${name}_crops[${cropConfig.key}][x]`}
                                  value={Math.round(data.x)}
                              />
                              <input
                                  type="hidden"
                                  name={`${name}_crops[${cropConfig.key}][y]`}
                                  value={Math.round(data.y)}
                              />
                              <input
                                  type="hidden"
                                  name={`${name}_crops[${cropConfig.key}][width]`}
                                  value={Math.round(data.width)}
                              />
                              <input
                                  type="hidden"
                                  name={`${name}_crops[${cropConfig.key}][height]`}
                                  value={Math.round(data.height)}
                              />
                          </div>
                      );
                  })
                : // Single-crop hidden inputs
                  (hasNewCropData ||
                      (singleCurrentCropData !== null &&
                          singleCurrentCropData !== undefined)) && (
                      <>
                          <input
                              type="hidden"
                              name={`${name}_crop[x]`}
                              value={Math.round(
                                  (hasNewCropData
                                      ? cropData
                                      : singleCurrentCropData)!.x,
                              )}
                          />
                          <input
                              type="hidden"
                              name={`${name}_crop[y]`}
                              value={Math.round(
                                  (hasNewCropData
                                      ? cropData
                                      : singleCurrentCropData)!.y,
                              )}
                          />
                          <input
                              type="hidden"
                              name={`${name}_crop[width]`}
                              value={Math.round(
                                  (hasNewCropData
                                      ? cropData
                                      : singleCurrentCropData)!.width,
                              )}
                          />
                          <input
                              type="hidden"
                              name={`${name}_crop[height]`}
                              value={Math.round(
                                  (hasNewCropData
                                      ? cropData
                                      : singleCurrentCropData)!.height,
                              )}
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
                aspectRatio={
                    currentCrop !== null
                        ? currentCrop.aspectRatio
                        : aspectRatio
                }
                initialCropData={getInitialCropData()}
                onCropComplete={handleCropCompleteWithReset}
                onChangeImage={handleChangeImage}
                onRemove={handleRemove}
                showRemoveButton={
                    originalUrl !== null ||
                    (currentOriginalUrl !== null &&
                        currentOriginalUrl !== undefined)
                }
                stepInfo={
                    isMultiCrop && currentCrop !== null && crops !== undefined
                        ? {
                              current: currentCropIndex + 1,
                              total: crops.length,
                              label: currentCrop.label,
                          }
                        : undefined
                }
                onBack={
                    isMultiCrop && currentCropIndex > 0
                        ? handleBack
                        : undefined
                }
            />
        </div>
    );
}
