import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import { ThumbnailImage } from '@/components/ui/thumbnail-image';
import { Link } from '@inertiajs/react';
import { Eye, User } from 'lucide-react';
import { UpvoteButton } from './upvote-button';

interface ProjectItemProps {
    showcase: App.Http.Resources.Showcase.ShowcaseResource;
    rank: number;
}

export function ProjectItem({ showcase, rank }: ProjectItemProps) {
    return (
        <div className="flex items-center gap-4 px-2 py-4 transition-transform duration-200 ease-out hover:scale-[1.01]">
            <Link
                href={ShowcaseShowController.url({ showcase: showcase })}
                className="flex min-w-0 flex-1 items-center gap-4"
                prefetch
            >
                <ThumbnailImage
                    url={showcase.thumbnail_url}
                    rectString={showcase.thumbnail_rect_string}
                    alt={showcase.title}
                    fallbackText={showcase.title}
                    className="size-14 lg:size-20"
                />
                <div className="min-w-0 flex-1 space-y-0.5 lg:space-y-1">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        <span className="text-neutral-500 dark:text-neutral-400">
                            {rank}.
                        </span>{' '}
                        {showcase.title}
                    </h3>
                    <p className="truncate text-sm text-neutral-700 dark:text-neutral-500">
                        {showcase.tagline}
                    </p>
                    <div className="flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-400">
                        {showcase.user !== null &&
                            showcase.user !== undefined && (
                                <span className="hidden items-center gap-1 lg:inline-flex">
                                    <User className="size-3" />
                                    {showcase.user.first_name}{' '}
                                    {showcase.user.last_name}
                                </span>
                            )}
                        {showcase.user !== null &&
                            showcase.user !== undefined &&
                            showcase.view_count !== null &&
                            showcase.view_count !== undefined && (
                                <span className="hidden text-neutral-300 lg:inline dark:text-neutral-600">
                                    &middot;
                                </span>
                            )}
                        {showcase.view_count !== null &&
                            showcase.view_count !== undefined && (
                                <span className="inline-flex items-center gap-1">
                                    <Eye className="size-3" />
                                    {showcase.view_count.toLocaleString()}
                                </span>
                            )}
                    </div>
                </div>
            </Link>
            <UpvoteButton
                showcaseSlug={showcase.slug}
                upvotesCount={showcase.upvotes_count ?? 0}
                hasUpvoted={showcase.has_upvoted ?? false}
            />
        </div>
    );
}
