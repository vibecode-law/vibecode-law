import {
    Dialog,
    DialogClose,
    DialogOverlay,
    DialogPortal,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import { ChevronLeft, ChevronRight, Play, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

type ShowcaseImage = App.Http.Resources.Showcase.ShowcaseImageResource;

function buildImageUrl(
    baseUrl: string,
    transform: boolean,
    image: ShowcaseImage,
    width?: number,
): string {
    if (transform === false) {
        return baseUrl;
    }

    const params = new URLSearchParams();

    if (width !== undefined) {
        params.set('w', String(width));
    }

    const landscapeCrop = image.crops?.landscape;

    if (landscapeCrop !== undefined && landscapeCrop !== null) {
        params.set(
            'rect',
            `${landscapeCrop.x},${landscapeCrop.y},${landscapeCrop.width},${landscapeCrop.height}`,
        );
    }

    const paramString = params.toString();
    return paramString !== '' ? `${baseUrl}?${paramString}` : baseUrl;
}

interface ShowcaseGalleryProps {
    images: ShowcaseImage[];
    selectedIndex: number;
    onSelectIndex: (index: number) => void;
    fallbackAlt: string;
    youtubeId?: string | null;
}

export function ShowcaseGallery({
    images,
    selectedIndex,
    onSelectIndex,
    fallbackAlt,
    youtubeId,
}: ShowcaseGalleryProps) {
    const { transformImages } = usePage<SharedData>().props;
    const [modalOpen, setModalOpen] = useState(false);
    const [modalImageIndex, setModalImageIndex] = useState(0);
    const touchStartX = useRef<number | null>(null);
    const hasVideo =
        youtubeId !== null && youtubeId !== undefined && youtubeId !== '';
    const isVideoSelected = hasVideo && selectedIndex === 0;
    const imageIndex = hasVideo ? selectedIndex - 1 : selectedIndex;
    const selectedImage = images[imageIndex];
    const totalItems = images.length + (hasVideo ? 1 : 0);
    const modalImage = images[modalImageIndex];
    const hasMultipleImages = images.length > 1;

    const goToPrevImage = useCallback(() => {
        setModalImageIndex((prev) => (prev > 0 ? prev - 1 : images.length - 1));
    }, [images.length]);

    const goToNextImage = useCallback(() => {
        setModalImageIndex((prev) => (prev < images.length - 1 ? prev + 1 : 0));
    }, [images.length]);

    useEffect(() => {
        if (modalOpen === false || hasMultipleImages === false) {
            return;
        }

        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                goToPrevImage();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                goToNextImage();
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [modalOpen, hasMultipleImages, goToPrevImage, goToNextImage]);

    const handleTouchStart = (e: React.TouchEvent) => {
        touchStartX.current = e.touches[0].clientX;
    };

    const handleTouchEnd = (e: React.TouchEvent) => {
        if (touchStartX.current === null || hasMultipleImages === false) {
            return;
        }

        const diff = touchStartX.current - e.changedTouches[0].clientX;
        const threshold = 50;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                goToNextImage();
            } else {
                goToPrevImage();
            }
        }

        touchStartX.current = null;
    };

    const openModal = (index: number) => {
        setModalImageIndex(index);
        setModalOpen(true);
    };

    return (
        <div className="mb-8 space-y-3">
            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                Gallery
            </p>
            <div className="overflow-hidden rounded-lg">
                {isVideoSelected ? (
                    <iframe
                        src={`https://www.youtube-nocookie.com/embed/${youtubeId}`}
                        title="YouTube video"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        className="aspect-video w-full"
                    />
                ) : (
                    <button
                        type="button"
                        onClick={() => openModal(imageIndex)}
                        className="w-full cursor-zoom-in"
                    >
                        <img
                            src={buildImageUrl(
                                selectedImage.url,
                                transformImages === true,
                                selectedImage,
                                700,
                            )}
                            alt={selectedImage.alt_text ?? fallbackAlt}
                            className="aspect-video w-full object-cover"
                        />
                    </button>
                )}
            </div>

            <Dialog open={modalOpen} onOpenChange={setModalOpen}>
                <DialogPortal>
                    <DialogOverlay />
                    <DialogPrimitive.Content
                        className="fixed top-1/2 left-1/2 z-50 max-h-[90vh] max-w-[90vw] -translate-x-1/2 -translate-y-1/2 focus:outline-none"
                        onTouchStart={handleTouchStart}
                        onTouchEnd={handleTouchEnd}
                    >
                        <img
                            src={modalImage?.url}
                            alt={modalImage?.alt_text ?? fallbackAlt}
                            className="max-h-[90vh] max-w-[90vw] rounded-lg object-contain"
                            draggable={false}
                        />
                        <DialogClose className="absolute -top-10 -right-12 rounded-full bg-white/90 p-1.5 text-neutral-900 shadow-lg transition-opacity hover:opacity-80">
                            <X className="size-5" />
                            <span className="sr-only">Close</span>
                        </DialogClose>
                        {hasMultipleImages && (
                            <>
                                <button
                                    type="button"
                                    onClick={goToPrevImage}
                                    className="absolute top-1/2 -left-12 hidden -translate-y-1/2 rounded-full bg-white/90 p-1.5 text-neutral-900 shadow-lg transition-opacity hover:opacity-80 md:block"
                                >
                                    <ChevronLeft className="size-5" />
                                    <span className="sr-only">
                                        Previous image
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    onClick={goToNextImage}
                                    className="absolute top-1/2 -right-12 hidden -translate-y-1/2 rounded-full bg-white/90 p-1.5 text-neutral-900 shadow-lg transition-opacity hover:opacity-80 md:block"
                                >
                                    <ChevronRight className="size-5" />
                                    <span className="sr-only">Next image</span>
                                </button>
                                <div className="absolute -bottom-8 left-1/2 -translate-x-1/2 text-sm text-white">
                                    {modalImageIndex + 1} / {images.length}
                                </div>
                            </>
                        )}
                    </DialogPrimitive.Content>
                </DialogPortal>
            </Dialog>
            {totalItems > 1 && (
                <div className="flex flex-wrap gap-2">
                    {hasVideo && (
                        <button
                            onClick={() => onSelectIndex(0)}
                            className={cn(
                                'relative size-16 shrink-0 overflow-hidden rounded-md border-2 transition-all',
                                selectedIndex === 0
                                    ? 'border-neutral-900 dark:border-white'
                                    : 'border-transparent opacity-70 hover:opacity-100',
                            )}
                        >
                            <img
                                src={`https://img.youtube.com/vi/${youtubeId}/mqdefault.jpg`}
                                alt="Video thumbnail"
                                className="size-full object-cover"
                            />
                            <div className="absolute inset-0 flex items-center justify-center bg-black/30">
                                <Play className="size-6 fill-white text-white" />
                            </div>
                        </button>
                    )}
                    {images.map((image, index) => {
                        const itemIndex = hasVideo ? index + 1 : index;
                        return (
                            <button
                                key={image.id}
                                onClick={() => onSelectIndex(itemIndex)}
                                className={cn(
                                    'size-16 shrink-0 overflow-hidden rounded-md border-2 transition-all',
                                    selectedIndex === itemIndex
                                        ? 'border-neutral-900 dark:border-white'
                                        : 'border-transparent opacity-70 hover:opacity-100',
                                )}
                            >
                                <img
                                    src={buildImageUrl(
                                        image.url,
                                        transformImages === true,
                                        image,
                                        700,
                                    )}
                                    alt={
                                        image.alt_text ??
                                        `${fallbackAlt} - Image ${index + 1}`
                                    }
                                    className="size-full object-cover"
                                />
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
