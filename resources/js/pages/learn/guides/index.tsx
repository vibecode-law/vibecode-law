import { TabNav } from '@/components/navigation/tab-nav';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowRight,
    BookOpen,
    Lightbulb,
    Play,
    type LucideIcon,
} from 'lucide-react';

interface ResourceChild {
    name: string;
    slug: string;
    summary: string;
    icon: string;
    route: string;
}

interface GuidesIndexProps {
    title?: string;
    content?: string;
    children?: ResourceChild[];
}

const iconMap: Record<string, LucideIcon> = {
    lightbulb: Lightbulb,
    play: Play,
    'alert-triangle': AlertTriangle,
};

const colorMap: Record<
    string,
    { bg: string; icon: string; hover: string; border: string }
> = {
    lightbulb: {
        bg: 'bg-linear-to-br from-yellow-50 to-amber-50 dark:from-yellow-950/30 dark:to-amber-950/30',
        icon: 'text-yellow-600 dark:text-yellow-400',
        hover: 'group-hover:from-yellow-100 group-hover:to-amber-100 dark:group-hover:from-yellow-950/50 dark:group-hover:to-amber-950/50',
        border: 'border-yellow-200 dark:border-yellow-800/50',
    },
    play: {
        bg: 'bg-linear-to-br from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/30',
        icon: 'text-emerald-600 dark:text-emerald-400',
        hover: 'group-hover:from-emerald-100 group-hover:to-green-100 dark:group-hover:from-emerald-950/50 dark:group-hover:to-green-950/50',
        border: 'border-emerald-200 dark:border-emerald-800/50',
    },
    'alert-triangle': {
        bg: 'bg-linear-to-br from-red-50 to-orange-50 dark:from-red-950/30 dark:to-orange-950/30',
        icon: 'text-red-600 dark:text-red-400',
        hover: 'group-hover:from-red-100 group-hover:to-orange-100 dark:group-hover:from-red-950/50 dark:group-hover:to-orange-950/50',
        border: 'border-red-200 dark:border-red-800/50',
    },
    scale: {
        bg: 'bg-linear-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/30',
        icon: 'text-violet-600 dark:text-violet-400',
        hover: 'group-hover:from-violet-100 group-hover:to-purple-100 dark:group-hover:from-violet-950/50 dark:group-hover:to-purple-950/50',
        border: 'border-violet-200 dark:border-violet-800/50',
    },
};

export default function GuidesIndex({
    title = 'Guides',
    content = '<p>Documentation and guides to help you master vibecoding.</p>',
    children = [],
}: GuidesIndexProps) {
    const { name, appUrl } = usePage<SharedData>().props;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn' },
            ]}
        >
            <Head title={title}>
                <meta
                    head-key="description"
                    name="description"
                    content={content}
                />
                <meta head-key="og-type" property="og:type" content="article" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`${title} | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}/learn/guides`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={content}
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-white py-12 lg:py-20 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4 text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        VibeAcademy
                    </h1>
                    <p className="mx-auto mt-6 max-w-3xl text-lg text-neutral-600 dark:text-neutral-400">
                        Master the art of building with AI coding assistants.
                        Start with the foundations and progress to master skills
                        through structured, hands-on courses.
                    </p>
                    <p className="mt-8 flex flex-col items-center justify-center gap-2 text-sm text-neutral-600 md:flex-row md:text-base dark:text-neutral-400">
                        <span className="flex items-center justify-start gap-2">
                            <BookOpen className="size-5" />
                            All materials are free and self-paced.
                        </span>
                    </p>
                </div>
            </section>

            {/* Tab Navigation */}
            <section className="border-b border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <TabNav
                        items={[
                            { title: 'Courses', href: '/learn' },
                            { title: 'Guides', href: '/learn/guides' },
                        ]}
                        ariaLabel="Learn navigation"
                    />
                </div>
            </section>

            {/* Guides Gallery */}
            <section className="bg-white pt-8 pb-8 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    {children.length > 0 ? (
                        <div className="grid gap-4 sm:grid-cols-2">
                            {children.map((child) => {
                                const Icon = iconMap[child.icon] || Lightbulb;
                                const colors =
                                    colorMap[child.icon] || colorMap.lightbulb;

                                return (
                                    <Link
                                        key={child.slug}
                                        href={child.route}
                                        className={`group relative flex items-start gap-4 rounded-xl border p-6 transition-all duration-200 ${colors.border} bg-white hover:shadow-md dark:bg-neutral-900 dark:hover:bg-neutral-800/50`}
                                    >
                                        <div
                                            className={`flex size-12 shrink-0 items-center justify-center rounded-lg transition-all duration-200 ${colors.bg} ${colors.hover}`}
                                        >
                                            <Icon
                                                className={`size-6 ${colors.icon}`}
                                            />
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h3 className="flex items-center gap-2 font-semibold text-neutral-900 dark:text-neutral-100">
                                                {child.name}
                                                <ArrowRight className="size-4 opacity-0 transition-all duration-200 group-hover:translate-x-1 group-hover:opacity-100" />
                                            </h3>
                                            <p className="mt-1 text-sm leading-relaxed text-neutral-600 dark:text-neutral-400">
                                                {child.summary}
                                            </p>
                                        </div>
                                    </Link>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="rounded-lg border border-neutral-200 bg-neutral-50 p-8 text-center dark:border-neutral-800 dark:bg-neutral-900">
                            <Lightbulb className="mx-auto size-12 text-neutral-400" />
                            <p className="mt-4 text-sm text-neutral-600 dark:text-neutral-400">
                                Guides coming soon
                            </p>
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
