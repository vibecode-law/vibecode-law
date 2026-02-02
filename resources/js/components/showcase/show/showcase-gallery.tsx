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
import { Play, X } from 'lucide-react';
import { useState } from 'react';

type ShowcaseImage = App.Http.Resources.Showcase.ShowcaseImageResource;

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
    const hasVideo =
        youtubeId !== null && youtubeId !== undefined && youtubeId !== '';
    const isVideoSelected = hasVideo && selectedIndex === 0;
    const imageIndex = hasVideo ? selectedIndex - 1 : selectedIndex;
    const selectedImage = images[imageIndex];
    const totalItems = images.length + (hasVideo ? 1 : 0);

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
                        onClick={() => setModalOpen(true)}
                        className="w-full cursor-zoom-in"
                    >
                        <img
                            src={
                                transformImages === true
                                    ? `${selectedImage.url}?w=700`
                                    : selectedImage.url
                            }
                            alt={selectedImage.alt_text ?? fallbackAlt}
                            className="aspect-video w-full object-cover"
                        />
                    </button>
                )}
            </div>

            <Dialog open={modalOpen} onOpenChange={setModalOpen}>
                <DialogPortal>
                    <DialogOverlay />
                    <DialogPrimitive.Content className="fixed top-1/2 left-1/2 z-50 max-h-[90vh] max-w-[90vw] -translate-x-1/2 -translate-y-1/2 focus:outline-none">
                        <img
                            src={selectedImage?.url}
                            alt={selectedImage?.alt_text ?? fallbackAlt}
                            className="max-h-[90vh] max-w-[90vw] rounded-lg object-contain"
                        />
                        <DialogClose className="absolute -top-2 -right-2 rounded-full bg-white p-1.5 text-neutral-900 shadow-lg transition-opacity hover:opacity-80">
                            <X className="size-5" />
                            <span className="sr-only">Close</span>
                        </DialogClose>
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
                                    src={
                                        transformImages === true
                                            ? `${image.url}?w=700`
                                            : image.url
                                    }
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
