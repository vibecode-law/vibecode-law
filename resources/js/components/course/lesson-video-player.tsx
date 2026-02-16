import { Play } from 'lucide-react';

export function LessonVideoPlayer() {
    return (
        <div className="relative mb-8 overflow-hidden rounded-xl bg-neutral-900">
            <div className="aspect-video w-full">
                <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-neutral-800 to-neutral-900">
                    <div className="text-center">
                        <div className="mx-auto mb-4 flex size-20 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                            <Play className="size-10 text-white" />
                        </div>
                        <p className="text-lg font-semibold text-white">
                            Video Player
                        </p>
                        <p className="mt-2 text-sm text-neutral-400">
                            Mux video will be embedded here
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
