import ChallengeShowController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeShowController';
import { LiveLeaderboard } from '@/components/challenges/live/live-leaderboard';
import { LiveQrCode } from '@/components/challenges/live/live-qr-code';
import {
    type LiveColumns,
    type LiveLayoutMode,
    type LiveTopLimit,
    LiveSettingsMenu,
    SIZE_MAX,
    SIZE_MIN,
} from '@/components/challenges/live/live-settings-menu';
import { useAppearance } from '@/hooks/use-appearance';
import { useWakeLock } from '@/hooks/use-wake-lock';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Head, usePage, usePoll } from '@inertiajs/react';
import { useCallback, useEffect, useSyncExternalStore } from 'react';

const settingListeners = new Set<() => void>();

function subscribeSetting(callback: () => void): () => void {
    settingListeners.add(callback);

    return () => {
        settingListeners.delete(callback);
    };
}

/**
 * Reads a per-challenge display preference from localStorage via an external
 * store so it stays hydration-safe under SSR (the server snapshot is always
 * the fallback value).
 */
function usePersistedSetting<T extends string>(
    storageKey: string,
    isValid: (value: string) => value is T,
    fallback: T,
): [T, (value: T) => void] {
    const value = useSyncExternalStore(
        subscribeSetting,
        () => {
            const stored = localStorage.getItem(storageKey);

            return stored !== null && isValid(stored) ? stored : fallback;
        },
        () => fallback,
    );

    const setValue = useCallback(
        (next: T): void => {
            localStorage.setItem(storageKey, next);
            settingListeners.forEach((listener) => listener());
        },
        [storageKey],
    );

    return [value, setValue];
}

const isLayoutMode = (value: string): value is LiveLayoutMode =>
    value === 'single' || value === 'per-sub-challenge';

const isTopLimit = (value: string): value is LiveTopLimit =>
    value === '3' || value === '5';

const isSizeScale = (value: string): value is string => {
    const parsed = Number(value);

    return Number.isInteger(parsed) && parsed >= SIZE_MIN && parsed <= SIZE_MAX;
};

const isColumns = (value: string): value is LiveColumns =>
    value === '1' || value === '2' || value === '3' || value === '4';

// Full class strings so Tailwind detects them at build time.
const columnClasses: Record<LiveColumns, string> = {
    '1': 'grid-cols-1',
    '2': 'grid-cols-1 lg:grid-cols-2',
    '3': 'grid-cols-1 lg:grid-cols-3',
    '4': 'grid-cols-1 lg:grid-cols-4',
};

interface ChallengeLiveProps {
    challenge: App.Http.Resources.Challenge.ChallengeResource;
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
}

const POLL_INTERVAL_MS = 12000;

export default function ChallengeLive({
    challenge,
    showcases,
}: ChallengeLiveProps) {
    const { appUrl } = usePage<SharedData>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();

    // Keep the screen awake and refresh the standings on an interval.
    useWakeLock();
    usePoll(POLL_INTERVAL_MS, { only: ['showcases'] });

    const subChallenges = challenge.sub_challenges ?? [];
    const hasSubChallenges = subChallenges.length > 0;
    const partnerLogos = challenge.partner_logos ?? [];

    const [layoutMode, setLayoutMode] = usePersistedSetting(
        `live-layout-${challenge.id}`,
        isLayoutMode,
        hasSubChallenges === true ? 'per-sub-challenge' : 'single',
    );
    const [topLimit, setTopLimit] = usePersistedSetting(
        `live-top-${challenge.id}`,
        isTopLimit,
        '5',
    );
    const limit = Number(topLimit);

    const [columns, setColumns] = usePersistedSetting(
        `live-columns-${challenge.id}`,
        isColumns,
        '2',
    );

    const [sizeScale, setSizeScale] = usePersistedSetting(
        `live-size-${challenge.id}`,
        isSizeScale,
        '100',
    );
    const size = Number(sizeScale);

    // Scale the whole screen by adjusting the root rem; restore on leave.
    useEffect(() => {
        const previous = document.documentElement.style.fontSize;
        document.documentElement.style.fontSize = `${(16 * size) / 100}px`;

        return () => {
            document.documentElement.style.fontSize = previous;
        };
    }, [size]);

    const heading =
        challenge.live_view_heading !== null &&
        challenge.live_view_heading !== ''
            ? challenge.live_view_heading
            : challenge.title;
    const subheading =
        challenge.live_view_subheading !== null &&
        challenge.live_view_subheading !== ''
            ? challenge.live_view_subheading
            : challenge.tagline;

    const challengeUrl = `${appUrl}${ChallengeShowController.url({ challenge: challenge.slug })}`;

    const showPerSubChallenge =
        hasSubChallenges === true && layoutMode === 'per-sub-challenge';

    const uncategorised = showcases.filter(
        (showcase) =>
            showcase.sub_challenge_id === null ||
            showcase.sub_challenge_id === undefined,
    );

    return (
        <>
            <Head title={`${challenge.title} — Live`} />

            <div className="flex min-h-screen flex-col bg-white px-6 py-8 lg:px-12 lg:py-10 dark:bg-neutral-950">
                <LiveSettingsMenu
                    appearance={resolvedAppearance}
                    onAppearanceChange={updateAppearance}
                    layoutMode={layoutMode}
                    onLayoutModeChange={setLayoutMode}
                    hasSubChallenges={hasSubChallenges}
                    topLimit={topLimit}
                    onTopLimitChange={setTopLimit}
                    columns={columns}
                    onColumnsChange={setColumns}
                    size={size}
                    onSizeChange={(next) => setSizeScale(String(next))}
                />

                <div className="fixed bottom-6 left-6 z-40 inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-4 py-2 text-base font-semibold text-emerald-600 lg:text-lg dark:text-emerald-400">
                    <span className="relative flex size-2.5">
                        <span className="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-75" />
                        <span className="relative inline-flex size-2.5 rounded-full bg-emerald-500" />
                    </span>
                    Live leaderboard
                </div>

                <header className="mb-8 flex items-start justify-between gap-8 lg:mb-12">
                    <div className="min-w-0">
                        <h1 className="text-4xl font-bold tracking-tight text-neutral-900 lg:text-6xl dark:text-white">
                            <span className="bg-linear-to-r from-emerald-600 via-amber-500 to-orange-500 bg-clip-text text-transparent">
                                {heading}
                            </span>
                        </h1>
                        <p className="mt-3 text-lg text-neutral-600 lg:text-2xl dark:text-neutral-300">
                            {subheading}
                        </p>
                    </div>

                    <div className="hidden shrink-0 flex-col items-center gap-2 sm:flex">
                        <div className="rounded-xl bg-white p-3 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-800">
                            <LiveQrCode
                                url={challengeUrl}
                                dark="#0a0a0a"
                                light="#ffffff"
                                size={140}
                            />
                        </div>
                        <p className="text-sm font-medium text-neutral-500 dark:text-neutral-300">
                            Scan to vote
                        </p>
                    </div>
                </header>

                <main className="flex flex-1 flex-col">
                    {showPerSubChallenge === true ? (
                        <div className={`grid gap-8 ${columnClasses[columns]}`}>
                            {subChallenges.map((subChallenge) => (
                                <LiveLeaderboard
                                    key={subChallenge.id}
                                    title={subChallenge.name}
                                    subtitle={subChallenge.tagline}
                                    limit={limit}
                                    showcases={showcases.filter(
                                        (showcase) =>
                                            showcase.sub_challenge_id ===
                                            subChallenge.id,
                                    )}
                                />
                            ))}
                            {uncategorised.length > 0 && (
                                <LiveLeaderboard
                                    title="Other entries"
                                    limit={limit}
                                    showcases={uncategorised}
                                />
                            )}
                        </div>
                    ) : (
                        <LiveLeaderboard
                            showcases={showcases}
                            title={null}
                            limit={limit}
                        />
                    )}
                </main>

                <footer className="mt-12 border-t border-neutral-200 pt-8 dark:border-neutral-800">
                    <div className="flex flex-wrap items-center justify-center gap-x-20 gap-y-10">
                        <img
                            src="/static/text-logo-black.png"
                            alt="vibecode.law"
                            className="h-10 w-auto lg:h-12 dark:invert"
                        />
                        {partnerLogos.map((logo) => {
                            const image = (
                                <img
                                    src={logo.url}
                                    alt={logo.filename}
                                    className={cn(
                                        'h-10 w-auto lg:h-12',
                                        logo.invert_in_dark === true &&
                                            'dark:invert',
                                    )}
                                />
                            );

                            if (logo.href !== null && logo.href !== undefined) {
                                return (
                                    <a
                                        key={logo.id}
                                        href={logo.href}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {image}
                                    </a>
                                );
                            }

                            return <span key={logo.id}>{image}</span>;
                        })}
                    </div>
                </footer>
            </div>
        </>
    );
}
