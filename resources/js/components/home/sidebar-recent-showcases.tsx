import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import { ThumbnailImage } from '@/components/ui/thumbnail-image';
import { Link } from '@inertiajs/react';
import { TrendingUp } from 'lucide-react';

interface SidebarRecentShowcasesProps {
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
}

export function SidebarRecentShowcases({
    showcases,
}: SidebarRecentShowcasesProps) {
    if (showcases.length === 0) {
        return null;
    }

    return (
        <div>
            <h3 className="mb-2 flex items-center gap-1.5 text-sm font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                <TrendingUp className="size-4" />
                Recently Added
            </h3>
            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                {showcases.map((showcase) => (
                    <Link
                        key={showcase.id}
                        href={ShowcaseShowController.url({
                            showcase: showcase.slug,
                        })}
                        className="flex items-center gap-4 py-4 transition-transform duration-200 ease-out hover:scale-[1.01]"
                    >
                        <ThumbnailImage
                            url={showcase.thumbnail_url}
                            fallbackText={showcase.title}
                            alt={showcase.title}
                            rectString={showcase.thumbnail_rect_string}
                        />
                        <div className="min-w-0 flex-1">
                            <div className="truncate font-medium text-neutral-900 dark:text-white">
                                {showcase.title}
                            </div>
                            {showcase.user && (
                                <div className="mt-0.5 truncate text-sm text-neutral-500 dark:text-neutral-400">
                                    {showcase.user.first_name}{' '}
                                    {showcase.user.last_name}
                                </div>
                            )}
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
}
