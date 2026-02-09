import { ProjectItem } from './showcase-item';

interface ProjectMonthSectionProps {
    month: string;
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
}

function formatMonth(month: string): string {
    const [year, monthNum] = month.split('-');
    const date = new Date(parseInt(year), parseInt(monthNum) - 1);
    return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
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
        </section>
    );
}
