import { ShowcaseStatusBadge } from '@/components/showcase/showcase-status-badge';
import { ThumbnailImage } from '@/components/ui/thumbnail-image';
import { Link } from '@inertiajs/react';
import { ArrowUp, Eye, Pencil } from 'lucide-react';
import { type ReactNode } from 'react';

export function ShowcaseUserInfo({
    user,
}: {
    user: { first_name: string; last_name: string };
}) {
    return (
        <p className="text-xs text-neutral-500 dark:text-neutral-400">
            by {user.first_name} {user.last_name}
        </p>
    );
}

export function ShowcaseRejectionReason({ reason }: { reason: string }) {
    return (
        <p className="text-sm text-red-600 dark:text-red-400">
            Rejection reason: {reason}
        </p>
    );
}

export function ShowcaseStats({
    viewCount,
    upvotesCount,
}: {
    viewCount: number;
    upvotesCount: number;
}) {
    return (
        <div className="flex items-center gap-4 text-sm text-neutral-500 dark:text-neutral-300">
            <span className="flex items-center gap-1">
                <Eye className="size-4" />
                {viewCount}
            </span>
            <span className="flex items-center gap-1">
                <ArrowUp className="size-4" />
                {upvotesCount}
            </span>
        </div>
    );
}

interface ShowcaseListItemProps {
    showcase: App.Http.Resources.Showcase.ShowcaseResource;
    href: string;
    metaSlot?: ReactNode;
    trailingSlot?: ReactNode;
    linkIcon?: 'edit' | 'view';
    actions?: ReactNode;
}

export function ShowcaseListItem({
    showcase,
    href,
    metaSlot,
    trailingSlot,
    linkIcon = 'view',
    actions,
}: ShowcaseListItemProps) {
    return (
        <div className="flex items-center gap-4 py-4">
            <Link
                href={href}
                className="flex min-w-0 flex-1 items-center gap-4"
                prefetch
            >
                <ThumbnailImage
                    url={showcase.thumbnail_url}
                    rectString={showcase.thumbnail_rect_string}
                    alt={showcase.title}
                    fallbackText={showcase.title}
                />
                <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                    <div className="flex items-center gap-2">
                        <h3 className="font-semibold text-neutral-900 dark:text-white">
                            {showcase.title}
                        </h3>
                        <ShowcaseStatusBadge
                            status={showcase.status}
                            size="sm"
                        />
                    </div>
                    <p className="truncate text-sm text-neutral-600 dark:text-neutral-300">
                        {showcase.tagline}
                    </p>
                    {metaSlot}
                </div>
            </Link>

            <div className="flex shrink-0 items-center gap-3">
                {trailingSlot}

                {actions}

                <Link
                    href={href}
                    className="rounded-md p-2 text-neutral-400 transition hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-800 dark:hover:text-neutral-300"
                >
                    {linkIcon === 'edit' ? (
                        <Pencil className="size-4" />
                    ) : (
                        <Eye className="size-4" />
                    )}
                </Link>
            </div>
        </div>
    );
}

interface DraftListItemProps {
    draft: App.Http.Resources.Showcase.ShowcaseDraftResource;
    href: string;
    metaSlot?: ReactNode;
    linkIcon?: 'edit' | 'view';
}

export function DraftListItem({
    draft,
    href,
    metaSlot,
    linkIcon = 'view',
}: DraftListItemProps) {
    return (
        <div className="flex items-center gap-4 py-4">
            <Link
                href={href}
                className="flex min-w-0 flex-1 items-center gap-4"
                prefetch
            >
                <ThumbnailImage
                    url={draft.thumbnail_url}
                    rectString={draft.thumbnail_rect_string}
                    alt={draft.title}
                    fallbackText={draft.title}
                />
                <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                    <div className="flex items-center gap-2">
                        <h3 className="font-semibold text-neutral-900 dark:text-white">
                            {draft.title}
                        </h3>
                        <ShowcaseStatusBadge status={draft.status} size="sm" />
                    </div>
                    <p className="truncate text-sm text-neutral-600 dark:text-neutral-300">
                        {draft.tagline}
                    </p>
                    <p className="text-xs text-neutral-500 dark:text-neutral-400">
                        Changes to: {draft.showcase_title}
                    </p>
                    {metaSlot}
                </div>
            </Link>

            <div className="flex shrink-0 items-center gap-3">
                <Link
                    href={href}
                    className="rounded-md p-2 text-neutral-400 transition hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-800 dark:hover:text-neutral-300"
                >
                    {linkIcon === 'edit' ? (
                        <Pencil className="size-4" />
                    ) : (
                        <Eye className="size-4" />
                    )}
                </Link>
            </div>
        </div>
    );
}
