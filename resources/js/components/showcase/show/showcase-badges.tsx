import { LinkedInIcon } from '@/components/icons/linkedin-icon';
import { RedirectModal } from '@/components/showcase/redirect-modal';
import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Globe, Video } from 'lucide-react';
import { useState } from 'react';

interface ShowcaseBadgesProps {
    user?: App.Http.Resources.User.UserResource | null;
    sourceStatus: App.ValueObjects.FrontendEnum;
    sourceUrl: string | null;
    url: string | null;
    videoUrl: string | null;
}

export function ShowcaseBadges({
    user,
    sourceStatus,
    sourceUrl,
    url,
    videoUrl,
}: ShowcaseBadgesProps) {
    const { transformImages } = usePage<SharedData>().props;
    const hasSourceUrl = sourceUrl !== null;
    const fullName =
        user !== null && user !== undefined
            ? `${user.first_name} ${user.last_name}`
            : null;

    const [isModalOpen, setIsModalOpen] = useState(false);
    const [modalUrl, setModalUrl] = useState('');

    const handleExternalLinkClick = (linkUrl: string) => {
        setModalUrl(linkUrl);
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setModalUrl('');
    };

    const userBadgeContent = user !== null && user !== undefined && (
        <>
            {user.avatar && user.linkedin_url === null && (
                <Avatar className="size-5">
                    <AvatarImage
                        src={
                            user.avatar !== null
                                ? transformImages === true
                                    ? `${user.avatar}?w=100`
                                    : user.avatar
                                : undefined
                        }
                        alt={fullName ?? undefined}
                    />
                </Avatar>
            )}
            {user.linkedin_url !== null && <LinkedInIcon className="size-4" />}
            {fullName}
        </>
    );

    return (
        <>
            <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div className="flex flex-wrap items-center gap-2">
                    {/* User Badge */}
                    {user !== null &&
                        user !== undefined &&
                        (user.linkedin_url !== null ? (
                            <Badge
                                variant="outline"
                                asChild
                                className="cursor-pointer px-3 py-1.5 text-sm"
                            >
                                <a
                                    href={user.linkedin_url}
                                    target="_blank"
                                    rel="noopener"
                                >
                                    {userBadgeContent}
                                </a>
                            </Badge>
                        ) : (
                            <Badge
                                variant="outline"
                                className="px-3 py-1.5 text-sm"
                            >
                                {userBadgeContent}
                            </Badge>
                        ))}

                    {/* GitHub Badge */}
                    {hasSourceUrl === true && (
                        <Badge
                            variant="outline"
                            className="cursor-pointer px-3 py-1.5 text-sm"
                            onClick={() => handleExternalLinkClick(sourceUrl)}
                        >
                            <svg
                                viewBox="0 0 24 24"
                                className="size-4"
                                fill="currentColor"
                            >
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                            GitHub
                        </Badge>
                    )}

                    {/* Website Badge */}
                    {url !== null && url !== '' && (
                        <Badge
                            variant="outline"
                            className="cursor-pointer px-3 py-1.5 text-sm"
                            onClick={() => handleExternalLinkClick(url)}
                        >
                            <Globe className="size-4" />
                            Website
                        </Badge>
                    )}

                    {/* Video Badge */}
                    {videoUrl !== null && videoUrl !== '' && (
                        <Badge
                            variant="outline"
                            className="cursor-pointer px-3 py-1.5 text-sm"
                            onClick={() => handleExternalLinkClick(videoUrl)}
                        >
                            <Video className="size-4" />
                            Video
                        </Badge>
                    )}
                </div>

                {/* Source Status Badge */}
                {hasSourceUrl === true && (
                    <span className="dark:bg-primary-900/30 dark:text-primary-foreground-400 inline-flex h-8 items-center justify-center rounded-full bg-primary px-3 text-xs font-medium text-primary-foreground">
                        {sourceStatus.label}
                    </span>
                )}
            </div>

            <RedirectModal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                url={modalUrl}
            />
        </>
    );
}
