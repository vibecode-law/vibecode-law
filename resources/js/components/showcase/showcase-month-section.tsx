import ShowcaseMonthIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseMonthIndexController';
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { ProjectItem } from './showcase-item';

interface ProjectMonthSectionProps {
    month: string;
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
}

function formatMonth(month: string): string {
    const [year, monthNum] = month.split('-');
    const date = new Date(parseInt(year), parseInt(monthNum) - 1);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
}

export function ProjectMonthSection({
    month,
    showcases,
}: ProjectMonthSectionProps) {
    return (
        <section className="py-6">
            <div className="mb-4 flex items-center gap-4">
                <h2 className="text-2xl font-semibold text-neutral-900 dark:text-white">
                    {formatMonth(month)}
                </h2>
                <div className="h-px flex-1 bg-border/60 dark:border-neutral-800"></div>
            </div>
            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                {showcases.map((showcase, index) => (
                    <ProjectItem
                        key={showcase.id}
                        showcase={showcase}
                        rank={index + 1}
                    />
                ))}
            </div>
            <Link
                href={ShowcaseMonthIndexController.url(month)}
                className="mt-2 flex w-full items-center justify-center gap-1 py-2 text-sm text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
            >
                <span>See all</span>
                <ArrowRight className="size-4" />
            </Link>
        </section>
    );
}
