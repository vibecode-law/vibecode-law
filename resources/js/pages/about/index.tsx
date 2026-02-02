import AboutIndexController from '@/actions/App/Http/Controllers/About/AboutIndexController';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Heart,
    Mail,
    Rocket,
    Scale,
    Shield,
    type LucideIcon,
} from 'lucide-react';

interface AboutChild {
    name: string;
    slug: string;
    summary: string;
    icon: string;
    route: string;
}

interface AboutIndexProps {
    title: string;
    content: string;
    children: AboutChild[];
}

const iconMap: Record<string, LucideIcon> = {
    rocket: Rocket,
    shield: Shield,
    scale: Scale,
    heart: Heart,
    mail: Mail,
};

const colorMap: Record<
    string,
    { bg: string; icon: string; hover: string; border: string }
> = {
    rocket: {
        bg: 'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/30',
        icon: 'text-amber-600 dark:text-amber-400',
        hover: 'group-hover:from-amber-100 group-hover:to-orange-100 dark:group-hover:from-amber-950/50 dark:group-hover:to-orange-950/50',
        border: 'border-amber-200 dark:border-amber-800/50',
    },
    shield: {
        bg: 'bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30',
        icon: 'text-blue-600 dark:text-blue-400',
        hover: 'group-hover:from-blue-100 group-hover:to-indigo-100 dark:group-hover:from-blue-950/50 dark:group-hover:to-indigo-950/50',
        border: 'border-blue-200 dark:border-blue-800/50',
    },
    heart: {
        bg: 'bg-gradient-to-br from-rose-50 to-pink-50 dark:from-rose-950/30 dark:to-pink-950/30',
        icon: 'text-rose-600 dark:text-rose-400',
        hover: 'group-hover:from-rose-100 group-hover:to-pink-100 dark:group-hover:from-rose-950/50 dark:group-hover:to-pink-950/50',
        border: 'border-rose-200 dark:border-rose-800/50',
    },
    mail: {
        bg: 'bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30',
        icon: 'text-emerald-600 dark:text-emerald-400',
        hover: 'group-hover:from-emerald-100 group-hover:to-teal-100 dark:group-hover:from-emerald-950/50 dark:group-hover:to-teal-950/50',
        border: 'border-emerald-200 dark:border-emerald-800/50',
    },
};

export default function AboutIndex({
    title,
    content,
    children,
}: AboutIndexProps) {
    const { name, appUrl, defaultMetaDescription } =
        usePage<SharedData>().props;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'About' },
            ]}
        >
            <Head title={title}>
                <meta
                    head-key="description"
                    name="description"
                    content={defaultMetaDescription}
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
                    content={`${appUrl}${AboutIndexController.url()}`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={defaultMetaDescription}
                />
            </Head>

            <section className="bg-white py-12 dark:bg-neutral-950">
                <div className="mx-auto max-w-3xl px-4">
                    <article
                        className="legal-content"
                        dangerouslySetInnerHTML={{ __html: content }}
                    />

                    {children.length > 0 && (
                        <nav className="mt-12">
                            <h2 className="mb-6 text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                                Learn More
                            </h2>
                            <div className="grid gap-4 sm:grid-cols-1">
                                {children.map((child) => {
                                    const Icon = iconMap[child.icon] || Rocket;
                                    const colors =
                                        colorMap[child.icon] || colorMap.rocket;

                                    return (
                                        <Link
                                            key={child.slug}
                                            href={child.route}
                                            className={`group relative flex items-start gap-4 rounded-xl border p-5 transition-all duration-200 ${colors.border} bg-white hover:shadow-md dark:bg-neutral-900 dark:hover:bg-neutral-800/50`}
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
                        </nav>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
