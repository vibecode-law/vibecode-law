import { VideoOff } from 'lucide-react';

interface VideoPlayerFallbackProps {
    message?: string;
}

export function VideoPlayerFallback({
    message = 'Video is not available for this lesson.',
}: VideoPlayerFallbackProps) {
    return (
        <div className="flex h-full w-full items-center justify-center bg-linear-to-br from-neutral-800 to-neutral-900">
            <div className="text-center">
                <div className="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <VideoOff className="size-8 text-neutral-400" />
                </div>
                <p className="text-sm text-neutral-400">{message}</p>
            </div>
        </div>
    );
}
