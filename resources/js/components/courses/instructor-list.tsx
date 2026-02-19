import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

interface InstructorListProps {
    instructors: {
        first_name: string;
        last_name: string;
        avatar: string | null;
        job_title: string | null;
        organisation: string | null;
    }[];
}

export function InstructorList({ instructors }: InstructorListProps) {
    const { transformImages } = usePage<SharedData>().props;

    if (instructors.length === 0) {
        return null;
    }

    return (
        <div>
            <p className="mb-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                {instructors.length === 1 ? 'Instructor' : 'Instructors'}
            </p>
            <div className="space-y-4">
                {instructors.map((user) => {
                    const avatarSrc =
                        user.avatar !== null
                            ? transformImages === true
                                ? `${user.avatar}?w=256`
                                : user.avatar
                            : undefined;

                    return (
                        <div
                            key={`${user.first_name}-${user.last_name}`}
                            className="flex items-start gap-3"
                        >
                            <Avatar className="size-12">
                                {avatarSrc ? (
                                    <AvatarImage
                                        src={avatarSrc}
                                        alt={`${user.first_name} ${user.last_name}`}
                                    />
                                ) : null}
                                <AvatarFallback className="bg-neutral-100 text-lg font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                                    {user.first_name.charAt(0)}
                                </AvatarFallback>
                            </Avatar>
                            <div className="min-w-0 flex-1">
                                <p className="font-semibold text-neutral-900 dark:text-white">
                                    {user.first_name} {user.last_name}
                                </p>
                                {user.job_title && (
                                    <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                        {user.job_title}
                                    </p>
                                )}
                                {user.organisation && (
                                    <p className="text-sm text-neutral-500 dark:text-neutral-500">
                                        {user.organisation}
                                    </p>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
