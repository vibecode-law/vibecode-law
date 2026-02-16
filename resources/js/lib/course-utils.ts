const experienceLevelColors: Record<number, string> = {
    1: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
    2: 'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300',
    3: 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
    4: 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300',
};

export function getExperienceLevelColor(level: number): string {
    return (
        experienceLevelColors[level] ??
        'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-200'
    );
}

export function formatDuration(
    seconds: number | null | undefined,
): string | null {
    if (!seconds || seconds <= 0) {
        return null;
    }

    const HOUR_IN_SECONDS = 3600;

    if (seconds < HOUR_IN_SECONDS) {
        const minutes = Math.ceil(seconds / 60);
        const roundedMinutes = Math.ceil(minutes / 5) * 5;
        return `${roundedMinutes} min`;
    } else {
        const hours = Math.ceil(seconds / HOUR_IN_SECONDS);
        return `${hours} ${hours === 1 ? 'hr' : 'hrs'}`;
    }
}
