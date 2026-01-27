import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import { ThumbnailImage } from '@/components/ui/thumbnail-image';
import { Link } from '@inertiajs/react';
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
                />
                <div className="min-w-0 flex-1">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        <span className="text-neutral-500 dark:text-neutral-400">
                            {rank}.
                        </span>{' '}
                        {showcase.title}
                    </h3>
                    <p className="truncate text-sm text-neutral-600 dark:text-neutral-400">
                        {showcase.tagline}
                    </p>
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
