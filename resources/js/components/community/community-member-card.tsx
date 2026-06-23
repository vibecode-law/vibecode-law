import PublicProfileController from '@/actions/App/Http/Controllers/User/PublicProfileController';
import { LinkedInIcon } from '@/components/icons/linkedin-icon';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

interface CommunityMemberCardProps {
    user: App.Http.Resources.User.UserResource;
    showTeamRole?: boolean;
}

export function CommunityMemberCard({
    user,
    showTeamRole = false,
}: CommunityMemberCardProps) {
    const { transformImages } = usePage<SharedData>().props;

    const fullName = `${user.first_name} ${user.last_name}`;
    const initials = `${user.first_name.charAt(0)}${user.last_name.charAt(0)}`;

    const subtitle = showTeamRole
        ? user.team_role
        : user.job_title !== null && user.organisation !== null
          ? `${user.job_title} at ${user.organisation}`
          : (user.job_title ?? user.organisation);

    return (
        <div className="flex items-start gap-6 rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <Link
                href={PublicProfileController.url(user.handle)}
                className="shrink-0"
            >
                <Avatar className="size-24">
                    <AvatarImage
                        src={
                            user.avatar !== null
                                ? transformImages === true
                                    ? `${user.avatar}?w=100`
                                    : user.avatar
                                : undefined
                        }
                        alt={fullName}
                    />
                    <AvatarFallback>{initials}</AvatarFallback>
                </Avatar>
            </Link>
            <div className="flex h-full min-w-0 flex-1 flex-col py-1">
                <div className="flex-1">
                    <Link
                        href={PublicProfileController.url(user.handle)}
                        className="text-base font-medium text-neutral-900 hover:underline dark:text-white"
                    >
                        {fullName}
                    </Link>
                    {subtitle !== null && subtitle !== undefined && (
                        <div className="mt-0.5 text-sm text-neutral-500 dark:text-neutral-300">
                            {subtitle}
                        </div>
                    )}
                </div>
                {user.linkedin_url !== null && (
                    <Badge
                        variant="outline"
                        asChild
                        className="cursor-pointer px-2.5 py-1 text-xs"
                    >
                        <a
                            href={user.linkedin_url}
                            target="_blank"
                            rel="noopener"
                        >
                            <LinkedInIcon className="size-3.5" />
                            LinkedIn
                        </a>
                    </Badge>
                )}
            </div>
        </div>
    );
}
