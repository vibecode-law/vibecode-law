import { MuxVideoPlayer } from '@/components/courses/mux-video-player';
import { VideoPlayerFallback } from '@/components/courses/video-player-fallback';

export interface PlayerEventCallbacks {
    onPlaying?: () => void;
    onTimeUpdate?: (currentTime: number) => void;
    onEnded?: () => void;
}

interface LessonVideoPlayerProps extends PlayerEventCallbacks {
    playbackId?: string;
    host?: App.ValueObjects.FrontendEnum | null;
    playbackTokens?: Record<string, string>;
    title?: string;
    startTime?: number;
}

export function LessonVideoPlayer({
    playbackId,
    host,
    playbackTokens,
    title,
    startTime,
    onPlaying,
    onTimeUpdate,
    onEnded,
}: LessonVideoPlayerProps) {
    const renderPlayer = () => {
        if (!playbackId || !host) {
            return <VideoPlayerFallback />;
        }

        switch (host.name) {
            case 'Mux':
                return (
                    <MuxVideoPlayer
                        playbackId={playbackId}
                        tokens={playbackTokens ?? {}}
                        title={title}
                        startTime={startTime}
                        onPlaying={onPlaying}
                        onTimeUpdate={onTimeUpdate}
                        onEnded={onEnded}
                    />
                );
            default:
                return (
                    <VideoPlayerFallback
                        message={`Unsupported video host: ${host.label}`}
                    />
                );
        }
    };

    return (
        <div className="relative mb-8 overflow-hidden rounded-xl bg-neutral-900">
            <div className="aspect-video w-full">{renderPlayer()}</div>
        </div>
    );
}
