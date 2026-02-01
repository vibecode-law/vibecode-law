import { LinkedInIcon } from '@/components/icons/linkedin-icon';
import { RichTextContent } from '@/components/showcase/rich-text-content';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

interface CreatorSectionProps {
    user: NonNullable<App.Http.Resources.Showcase.ShowcaseResource['user']>;
}

export function CreatorSection({ user }: CreatorSectionProps) {
    const { transformImages } = usePage<SharedData>().props;
    const fullName = `${user.first_name} ${user.last_name}`;
    const initials = `${user.first_name.charAt(0)}${user.last_name.charAt(0)}`;

    const jobInfo =
        user.job_title !== null && user.organisation !== null
            ? `${user.job_title} at ${user.organisation}`
            : (user.job_title ?? user.organisation);

    return (
        <section className="mb-8">
            <h2 className="mb-5 text-xl font-semibold text-neutral-900 dark:text-white">
                About the Creator
            </h2>
            <div className="flex items-start gap-6">
                <Avatar className="size-14 shrink-0">
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
                    <AvatarFallback className="text-lg">
                        {initials}
                    </AvatarFallback>
                </Avatar>
                <div className="space-y-4">
                    <div>
                        <div className="text-lg font-medium text-neutral-900 dark:text-white">
                            {fullName}
                        </div>
                        {jobInfo !== null && (
                            <div className="text-sm text-neutral-500 dark:text-neutral-400">
                                {jobInfo}
                            </div>
                        )}
                    </div>
                    {user.bio_html !== null && (
                        <RichTextContent
                            html={user.bio_html ?? ''}
                            className="rich-text-content"
                        />
                    )}
                    {user.linkedin_url !== null && (
                        <Badge
                            variant="outline"
                            asChild
                            className="mt-1 cursor-pointer px-3 py-1.5 text-sm"
                        >
                            <a
                                href={user.linkedin_url}
                                target="_blank"
                                rel="noopener"
                            >
                                <LinkedInIcon className="size-4" />
                                LinkedIn
                            </a>
                        </Badge>
                    )}
                </div>
            </div>
        </section>
    );
}
