import HomeController from '@/actions/App/Http/Controllers/HomeController';
import ShowcaseIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseIndexController';
import ShowcaseMonthIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseMonthIndexController';
import ShowcasePracticeAreaIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcasePracticeAreaIndexController';
import { type BreadcrumbItem } from '@/components/navigation/breadcrumbs';
import { ProjectItem } from '@/components/showcase/showcase-item';
import { Pagination } from '@/components/ui/pagination';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

interface ActiveFilter {
    type: 'practice_area' | 'month';
    practiceArea?: App.Http.Resources.PracticeAreaResource;
    month?: string;
}

interface PaginatedShowcases {
    data: App.Http.Resources.Showcase.ShowcaseResource[];
    meta: {
        current_page: number;
        first_page_url: string;
        from: number | null;
        last_page: number;
        last_page_url: string;
        next_page_url: string | null;
        path: string;
        per_page: number;
        prev_page_url: string | null;
        to: number | null;
        total: number;
    };
}

interface AvailableFilters {
    months?: string[];
    practiceAreas?: App.Http.Resources.PracticeAreaResource[];
}

interface PublicIndexProps {
    showcases: PaginatedShowcases;
    availableFilters: AvailableFilters;
    activeFilter: ActiveFilter | null;
}

function formatMonth(month: string): string {
    const [year, monthNum] = month.split('-');
    const date = new Date(parseInt(year), parseInt(monthNum) - 1);
    return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
}

export default function PublicIndex({
    showcases,
    availableFilters,
    activeFilter,
}: PublicIndexProps) {
    const { appUrl, defaultMetaDescription } = usePage<SharedData>().props;
    const startRank =
        (showcases.meta.current_page - 1) * showcases.meta.per_page + 1;

    const getTitle = (): string => {
        if (activeFilter === null) {
            return 'All Showcases';
        }
        if (activeFilter.type === 'practice_area') {
            return activeFilter.practiceArea?.name ?? 'Showcases';
        }
        return activeFilter.month !== undefined
            ? formatMonth(activeFilter.month)
            : 'Showcases';
    };

    const getPageTitle = (): string => {
        return activeFilter !== null
            ? `Showcases - ${getTitle()}`
            : 'Showcases';
    };

    const getDescription = (): string => {
        if (activeFilter === null) {
            return defaultMetaDescription;
        }
        if (activeFilter.type === 'practice_area') {
            return `Legaltech projects in ${activeFilter.practiceArea?.name ?? 'this practice area'}, ranked by the community.`;
        }
        return `Legaltech projects from ${activeFilter.month !== undefined ? formatMonth(activeFilter.month) : 'this month'}, ranked by the community.`;
    };

    const getCurrentUrl = (): string => {
        if (activeFilter === null) {
            return `${appUrl}${ShowcaseIndexController.url()}`;
        }
        if (
            activeFilter.type === 'practice_area' &&
            activeFilter.practiceArea !== undefined
        ) {
            return `${appUrl}${ShowcasePracticeAreaIndexController.url({ practiceArea: activeFilter.practiceArea.slug })}`;
        }
        if (activeFilter.type === 'month' && activeFilter.month !== undefined) {
            return `${appUrl}${ShowcaseMonthIndexController.url({ month: activeFilter.month })}`;
        }
        return `${appUrl}${ShowcaseIndexController.url()}`;
    };

    const getBreadcrumbs = (): BreadcrumbItem[] => {
        const breadcrumbs: BreadcrumbItem[] = [
            { label: 'Home', href: HomeController.url() },
        ];

        if (activeFilter !== null) {
            breadcrumbs.push({
                label: 'Showcases',
                href: ShowcaseIndexController.url(),
            });
            breadcrumbs.push({ label: getTitle() });
        } else {
            breadcrumbs.push({ label: 'Showcases' });
        }

        return breadcrumbs;
    };

    return (
        <PublicLayout breadcrumbs={getBreadcrumbs()}>
            <Head title={getPageTitle()}>
                <meta
                    head-key="description"
                    name="description"
                    content={getDescription()}
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={getPageTitle()}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={getDescription()}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={getCurrentUrl()}
                />
            </Head>

            <section className="bg-white py-8 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="mb-6">
                        <h1 className="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            {getTitle()}
                        </h1>
                        {activeFilter !== null && (
                            <Link
                                href={ShowcaseIndexController.url()}
                                className="mt-2 inline-block text-sm text-blue-600 hover:underline dark:text-blue-400"
                            >
                                Clear filter
                            </Link>
                        )}
                    </div>

                    {availableFilters.practiceAreas !== undefined &&
                        availableFilters.practiceAreas.length > 0 && (
                            <div className="mb-8 flex flex-wrap gap-2">
                                {availableFilters.practiceAreas.map((pa) => (
                                    <Link
                                        key={pa.id}
                                        href={ShowcasePracticeAreaIndexController.url(
                                            {
                                                practiceArea: pa.slug,
                                            },
                                        )}
                                        className={`rounded-full px-3 py-1 text-sm transition ${
                                            activeFilter?.type ===
                                                'practice_area' &&
                                            activeFilter.practiceArea?.id ===
                                                pa.id
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700'
                                        }`}
                                    >
                                        {pa.name}
                                    </Link>
                                ))}
                            </div>
                        )}

                    {availableFilters.months !== undefined &&
                        availableFilters.months.length > 0 && (
                            <div className="mb-8 flex flex-wrap gap-2">
                                {availableFilters.months.map((m) => (
                                    <Link
                                        key={m}
                                        href={ShowcaseMonthIndexController.url({
                                            month: m,
                                        })}
                                        className={`rounded-full px-3 py-1 text-sm transition ${
                                            activeFilter?.type === 'month' &&
                                            activeFilter.month === m
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700'
                                        }`}
                                    >
                                        {formatMonth(m)}
                                    </Link>
                                ))}
                            </div>
                        )}

                    {showcases.data.length > 0 ? (
                        <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                            {showcases.data.map((showcase, idx) => (
                                <ProjectItem
                                    key={showcase.id}
                                    showcase={showcase}
                                    rank={startRank + idx}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="py-16 text-center">
                            <p className="text-neutral-500 dark:text-neutral-400">
                                No showcases found.
                            </p>
                        </div>
                    )}

                    <Pagination
                        meta={showcases.meta}
                        variant="simple"
                        preserveScroll
                        className="mt-8"
                    />
                </div>
            </section>
        </PublicLayout>
    );
}
