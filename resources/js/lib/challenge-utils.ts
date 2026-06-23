export type ChallengeStatus = 'in_progress' | 'upcoming' | 'ended';

export const CHALLENGE_VISIBILITY = {
    Public: 1,
    InviteToSubmit: 2,
    InviteToViewAndSubmit: 3,
} as const satisfies Record<string, App.Enums.ChallengeVisibility>;

export function getChallengeStatus(
    startsAt: string | null,
    endsAt: string | null,
): ChallengeStatus {
    const now = new Date();

    if (startsAt === null && endsAt === null) {
        return 'in_progress';
    }

    const startDate = startsAt ? new Date(startsAt) : null;
    const endDate = endsAt ? new Date(endsAt) : null;

    if (startDate && now < startDate) {
        return 'upcoming';
    }

    if (endDate && now > endDate) {
        return 'ended';
    }

    return 'in_progress';
}

function getZonedTimeParts(
    date: Date,
    timeZone: string,
): { hour: number; minute: number } {
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone,
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).formatToParts(date);

    const lookup = (type: Intl.DateTimeFormatPartTypes) =>
        parseInt(parts.find((part) => part.type === type)?.value ?? '0', 10);

    let hour = lookup('hour');
    if (hour === 24) {
        hour = 0;
    }

    return { hour, minute: lookup('minute') };
}

/**
 * Format a challenge open/close moment in the challenge's timezone. The time is
 * only shown when it isn't the default whole-day boundary — midnight for the
 * start, 23:59 for the end. The timezone itself is shown separately, once.
 */
export function formatChallengeMoment(
    iso: string,
    timeZone: string,
    boundary: 'start' | 'end',
): string {
    const date = new Date(iso);

    const datePart = date.toLocaleDateString('en-GB', {
        timeZone,
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    const { hour, minute } = getZonedTimeParts(date, timeZone);

    const isWholeDayBoundary =
        boundary === 'start'
            ? hour === 0 && minute === 0
            : hour === 23 && minute === 59;

    if (isWholeDayBoundary) {
        return datePart;
    }

    const timePart = date.toLocaleTimeString('en-GB', {
        timeZone,
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });

    return `${datePart}, ${timePart}`;
}

const timezoneLabelCache = new Map<string, string>();

function timezoneOffsetString(timeZone: string): string {
    try {
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone,
            timeZoneName: 'longOffset',
        }).formatToParts(new Date());

        const raw =
            parts.find((part) => part.type === 'timeZoneName')?.value ?? 'GMT';

        // Safari/Node return a bare "GMT" for zero-offset zones.
        return raw === 'GMT' ? 'GMT+00:00' : raw;
    } catch {
        return 'GMT+00:00';
    }
}

function timezoneCity(timeZone: string): string {
    return (timeZone.split('/').pop() ?? timeZone).replace(/_/g, ' ');
}

const timezoneOffsetCache = new Map<string, number>();

/** The current offset of a timezone in minutes, e.g. 60 for "GMT+01:00". */
export function getTimezoneOffsetMinutes(timeZone: string): number {
    const cached = timezoneOffsetCache.get(timeZone);

    if (cached !== undefined) {
        return cached;
    }

    const match = /GMT([+-])(\d{2}):(\d{2})/.exec(
        timezoneOffsetString(timeZone),
    );

    const offset =
        match === null
            ? 0
            : (match[1] === '-' ? -1 : 1) *
              (Number(match[2]) * 60 + Number(match[3]));

    timezoneOffsetCache.set(timeZone, offset);

    return offset;
}

/** A friendly timezone label, e.g. "(GMT+01:00) London". */
export function formatTimezoneLabel(timeZone: string): string {
    const cached = timezoneLabelCache.get(timeZone);

    if (cached !== undefined) {
        return cached;
    }

    const label = `(${timezoneOffsetString(timeZone)}) ${timezoneCity(timeZone)}`;
    timezoneLabelCache.set(timeZone, label);

    return label;
}

export function getStatusLabel(status: ChallengeStatus): string {
    switch (status) {
        case 'in_progress':
            return 'In Progress';
        case 'upcoming':
            return 'Upcoming';
        case 'ended':
            return 'Ended';
    }
}

export function getTimeInfo(
    startsAt: string | null,
    endsAt: string | null,
): string | null {
    const now = new Date();

    if (endsAt) {
        const endDate = new Date(endsAt);
        const daysUntilEnd = Math.ceil(
            (endDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24),
        );

        if (daysUntilEnd > 0 && daysUntilEnd <= 1) {
            return 'Closes Soon';
        }

        if (daysUntilEnd > 7 && daysUntilEnd <= 30) {
            return `${daysUntilEnd} days left`;
        }
    }

    if (startsAt) {
        const startDate = new Date(startsAt);
        if (now < startDate) {
            const daysUntilStart = Math.ceil(
                (startDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24),
            );
            if (daysUntilStart <= 7) {
                return `Starts in ${daysUntilStart} day${daysUntilStart === 1 ? '' : 's'}`;
            }
        }
    }

    return null;
}
