import { useCallback, useState } from 'react';
import Cropper from 'react-easy-crop';
import type { Area, Point } from 'react-easy-crop';
import { ImagePlus, Minus, Plus, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

export interface CropData {
    x: number;
    y: number;
    width: number;
    height: number;
    naturalWidth: number;
    naturalHeight: number;
}

// Simpler crop data type for initializing from existing data (doesn't include naturalWidth/naturalHeight)
export interface SimpleCropData {
    x: number;
    y: number;
    width: number;
    height: number;
}

interface ImageCropModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    imageUrl: string | null;
    aspectRatio?: number;
    initialCropData?: SimpleCropData | null;
    onCropComplete: (croppedFile: File, cropData: CropData) => void;
    onChangeImage: () => void;
    onRemove?: () => void;
    showRemoveButton?: boolean;
}

async function createCroppedImage(
    imageSrc: string,
    pixelCrop: Area,
): Promise<File> {
    const image = await loadImage(imageSrc);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    if (ctx === null) {
        throw new Error('Failed to get canvas context');
    }

    canvas.width = pixelCrop.width;
    canvas.height = pixelCrop.height;

    ctx.drawImage(
        image,
        pixelCrop.x,
        pixelCrop.y,
        pixelCrop.width,
        pixelCrop.height,
        0,
        0,
        pixelCrop.width,
        pixelCrop.height,
    );

    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (blob === null) {
                    reject(new Error('Failed to create blob'));
                    return;
                }
                const file = new File([blob], 'cropped-image.jpg', {
                    type: 'image/jpeg',
                });
                resolve(file);
            },
            'image/jpeg',
            0.9,
        );
    });
}

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = src;
    });
}

export function ImageCropModal({
    open,
    onOpenChange,
    imageUrl,
    aspectRatio = 1,
    initialCropData,
    onCropComplete,
    onChangeImage,
    onRemove,
    showRemoveButton = false,
}: ImageCropModalProps) {
    const [crop, setCrop] = useState<Point>({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState<Area | null>(
        null,
    );
    const [naturalSize, setNaturalSize] = useState<{
        width: number;
        height: number;
    } | null>(null);
    const [isSaving, setIsSaving] = useState(false);

    const handleCropComplete = useCallback(
        (_croppedArea: Area, croppedAreaPixels: Area) => {
            setCroppedAreaPixels(croppedAreaPixels);
        },
        [],
    );

    const handleMediaLoaded = useCallback(
        (mediaSize: { naturalWidth: number; naturalHeight: number }) => {
            setNaturalSize({
                width: mediaSize.naturalWidth,
                height: mediaSize.naturalHeight,
            });
        },
        [],
    );

    const handleSave = async () => {
        if (
            imageUrl === null ||
            croppedAreaPixels === null ||
            naturalSize === null
        ) {
            return;
        }

        setIsSaving(true);
        try {
            const croppedFile = await createCroppedImage(
                imageUrl,
                croppedAreaPixels,
            );
            const cropData: CropData = {
                x: croppedAreaPixels.x,
                y: croppedAreaPixels.y,
                width: croppedAreaPixels.width,
                height: croppedAreaPixels.height,
                naturalWidth: naturalSize.width,
                naturalHeight: naturalSize.height,
            };
            onCropComplete(croppedFile, cropData);
            onOpenChange(false);
        } catch (error) {
            console.error('Failed to crop image:', error);
        } finally {
            setIsSaving(false);
        }
    };

    const handleCancel = () => {
        onOpenChange(false);
    };

    const handleChangeImage = () => {
        setCrop({ x: 0, y: 0 });
        setZoom(1);
        setCroppedAreaPixels(null);
        setNaturalSize(null);
        onChangeImage();
    };

    const handleZoomChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setZoom(Number(e.target.value));
    };

    const decreaseZoom = () => {
        setZoom((prev) => Math.max(1, prev - 0.1));
    };

    const increaseZoom = () => {
        setZoom((prev) => Math.min(3, prev + 0.1));
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>Crop Image</DialogTitle>
                    <DialogDescription>
                        Drag to reposition and use the slider to zoom in or out.
                    </DialogDescription>
                </DialogHeader>

                <div className="relative h-75 overflow-hidden rounded-md bg-neutral-100 sm:h-100 dark:bg-neutral-900">
                    {imageUrl !== null ? (
                        <Cropper
                            image={imageUrl}
                            crop={crop}
                            zoom={zoom}
                            aspect={aspectRatio}
                            onCropChange={setCrop}
                            onZoomChange={setZoom}
                            onCropComplete={handleCropComplete}
                            onMediaLoaded={handleMediaLoaded}
                            initialCroppedAreaPixels={
                                initialCropData !== null &&
                                initialCropData !== undefined
                                    ? initialCropData
                                    : undefined
                            }
                        />
                    ) : (
                        <div className="flex size-full items-center justify-center text-neutral-400">
                            <ImagePlus className="size-12" />
                        </div>
                    )}
                </div>

                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={decreaseZoom}
                        disabled={zoom <= 1}
                        className="flex size-8 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 disabled:opacity-50 dark:hover:bg-neutral-800"
                    >
                        <Minus className="size-4" />
                    </button>
                    <input
                        type="range"
                        min={1}
                        max={3}
                        step={0.01}
                        value={zoom}
                        onChange={handleZoomChange}
                        className="h-2 flex-1 cursor-pointer appearance-none rounded-full bg-neutral-200 accent-amber-500 dark:bg-neutral-700"
                    />
                    <button
                        type="button"
                        onClick={increaseZoom}
                        disabled={zoom >= 3}
                        className="flex size-8 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 disabled:opacity-50 dark:hover:bg-neutral-800"
                    >
                        <Plus className="size-4" />
                    </button>
                </div>

                <DialogFooter className="flex-col gap-2 sm:flex-row sm:justify-between">
                    <div className="flex w-full gap-2 sm:mr-auto sm:w-auto">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleChangeImage}
                            className="flex-1 sm:flex-none"
                        >
                            <ImagePlus className="size-4" />
                            <span className="sm:hidden">Change</span>
                            <span className="hidden sm:inline">Change Image</span>
                        </Button>
                        {showRemoveButton === true && onRemove !== undefined && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onRemove}
                                className="flex-1 text-red-600 hover:bg-red-50 hover:text-red-700 sm:flex-none dark:text-red-400 dark:hover:bg-red-950 dark:hover:text-red-300"
                            >
                                <Trash2 className="size-4" />
                                Remove
                            </Button>
                        )}
                    </div>
                    <div className="flex w-full gap-2 sm:w-auto">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleCancel}
                            className="flex-1 sm:flex-none"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            onClick={handleSave}
                            disabled={
                                isSaving ||
                                imageUrl === null ||
                                croppedAreaPixels === null
                            }
                            className="flex-1 sm:flex-none"
                        >
                            {isSaving === true ? 'Saving...' : 'Save'}
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
