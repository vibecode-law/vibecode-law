export type ChallengeStatus = 'in_progress' | 'upcoming' | 'ended';

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
