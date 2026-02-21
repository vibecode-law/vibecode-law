import { type PlayerEventCallbacks } from '@/components/courses/lesson-video-player';
import MuxPlayer from '@mux/mux-player-react';
import { useCallback } from 'react';

interface MuxVideoPlayerProps extends PlayerEventCallbacks {
    playbackId: string;
    tokens: Record<string, string>;
    title?: string;
    startTime?: number;
}

export function MuxVideoPlayer({
    playbackId,
    tokens,
    title,
    startTime,
    onPlaying,
    onTimeUpdate,
    onEnded,
}: MuxVideoPlayerProps) {
    const handleTimeUpdate = useCallback(
        (event: Event) => {
            if (onTimeUpdate) {
                const target = event.target as HTMLMediaElement;
                onTimeUpdate(target.currentTime);
            }
        },
        [onTimeUpdate],
    );

    return (
        <MuxPlayer
            playbackId={playbackId}
            tokens={tokens}
            streamType="on-demand"
            startTime={startTime}
            metadata={{
                video_title: title,
            }}
            accentColor="#6366f1"
            onPlaying={onPlaying}
            onTimeUpdate={handleTimeUpdate}
            onEnded={onEnded}
        />
    );
}
