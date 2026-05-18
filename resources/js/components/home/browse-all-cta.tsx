import ShowcaseIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseIndexController';
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

export function BrowseAllCta() {
    return (
        <div className="mt-8 mb-12 rounded-xl border border-neutral-200 bg-neutral-50 px-6 py-10 text-center dark:border-neutral-800 dark:bg-neutral-900/50">
            <h4 className="text-xl font-semibold text-neutral-900 dark:text-white">
                Explore every project
            </h4>
            <p className="mx-auto mt-2 max-w-md text-sm text-neutral-600 dark:text-neutral-400">
                Browse the full showcase archive by month, practice area and
                more.
            </p>
            <Link
                href={ShowcaseIndexController.url()}
                className="group mt-6 inline-flex items-center gap-2 rounded-full bg-linear-to-r from-brand to-brand/70 px-7 py-3 text-base font-semibold text-white shadow-md transition-all hover:to-brand hover:shadow-lg focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 focus-visible:outline-none dark:focus-visible:ring-offset-neutral-950"
            >
                Browse all projects
                <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
            </Link>
        </div>
    );
}
