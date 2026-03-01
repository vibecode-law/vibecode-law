import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { cn } from '@/lib/utils';

const avatarColors = [
    'bg-rose-500',
    'bg-amber-500',
    'bg-emerald-500',
    'bg-sky-500',
    'bg-violet-500',
    'bg-pink-500',
    'bg-teal-500',
    'bg-orange-500',
];

function getAvatarColor(name: string): string {
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return avatarColors[Math.abs(hash) % avatarColors.length];
}

const VISIBLE_PARTICIPANTS = 5;

interface ParticipantsProps {
    participants: App.Http.Resources.User.UserResource[];
    transformImages: boolean;
    className?: string;
}

export function Participants({
    participants,
    transformImages,
    className,
}: ParticipantsProps) {
    const visible = participants.slice(0, VISIBLE_PARTICIPANTS);
    const remaining = participants.length - visible.length;

    const names = visible.map((p) => p.first_name);
    const nameList =
        remaining > 0
            ? names.join(', ')
            : names.length <= 2
              ? names.join(' and ')
              : `${names.slice(0, -1).join(', ')} and ${names[names.length - 1]}`;

    const suffix =
        remaining > 0
            ? ` and ${remaining} ${remaining === 1 ? 'other' : 'others'} are`
            : participants.length === 1
              ? ' is'
              : ' are';

    return (
        <div className={cn('pt-8', className)}>
            <div className="flex -space-x-2">
                {visible.map((participant) => {
                    const avatarSrc =
                        participant.avatar !== null
                            ? transformImages
                                ? `${participant.avatar}?w=100`
                                : participant.avatar
                            : undefined;

                    return (
                        <Avatar
                            key={participant.handle}
                            className="size-8 ring-2 ring-white dark:ring-neutral-950"
                        >
                            {avatarSrc ? (
                                <AvatarImage
                                    src={avatarSrc}
                                    alt={participant.first_name}
                                />
                            ) : null}
                            <AvatarFallback
                                className={cn(
                                    'text-xs font-semibold text-white',
                                    getAvatarColor(participant.first_name),
                                )}
                            >
                                {participant.first_name.charAt(0)}
                            </AvatarFallback>
                        </Avatar>
                    );
                })}
                {remaining > 0 && (
                    <div className="flex size-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-semibold text-neutral-600 ring-2 ring-white dark:bg-neutral-700 dark:text-neutral-300 dark:ring-neutral-950">
                        +{remaining}
                    </div>
                )}
            </div>
            <p className="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                {nameList}
                {suffix} tackling this challenge.
            </p>
        </div>
    );
}
