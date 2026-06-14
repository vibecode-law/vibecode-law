import { useEffect } from 'react';

/**
 * Keeps the screen awake while the component is mounted and the document is
 * visible. The browser automatically releases a wake lock when the tab is
 * hidden, so we re-acquire it whenever the document becomes visible again.
 *
 * No-ops gracefully on browsers without the Screen Wake Lock API.
 */
export function useWakeLock(enabled = true): void {
    useEffect(() => {
        if (enabled === false) {
            return;
        }

        if (
            typeof navigator === 'undefined' ||
            'wakeLock' in navigator === false
        ) {
            return;
        }

        let sentinel: WakeLockSentinel | null = null;
        let released = false;

        const request = async (): Promise<void> => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            try {
                sentinel = await navigator.wakeLock.request('screen');
            } catch {
                // Ignore: the request can reject (e.g. low battery) — not fatal.
            }
        };

        const handleVisibilityChange = (): void => {
            if (released === false && document.visibilityState === 'visible') {
                void request();
            }
        };

        void request();
        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            released = true;
            document.removeEventListener(
                'visibilitychange',
                handleVisibilityChange,
            );
            void sentinel?.release();
            sentinel = null;
        };
    }, [enabled]);
}
