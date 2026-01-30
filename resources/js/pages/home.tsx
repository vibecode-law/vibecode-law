import HomeController from '@/actions/App/Http/Controllers/HomeController';
import HowItWorksController from '@/actions/App/Http/Controllers/Showcase/Help/HowItWorksController';
import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import { ProjectItem } from '@/components/showcase/showcase-item';
import { ProjectMonthSection } from '@/components/showcase/showcase-month-section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Megaphone } from 'lucide-react';

interface HomeProps {
    showcasesByMonth?: Record<
        string,
        App.Http.Resources.Showcase.ShowcaseResource[]
    >;
    featuredShowcases?: App.Http.Resources.Showcase.ShowcaseResource[];
}

export default function Home({
    showcasesByMonth,
    featuredShowcases,
}: HomeProps) {
    const { name, appUrl, defaultMetaDescription } =
        usePage<SharedData>().props;
    const isPreLaunch = featuredShowcases !== undefined;
    const months = showcasesByMonth ? Object.keys(showcasesByMonth) : [];

    return (
        <PublicLayout>
            <Head title="Home">
                <meta
                    head-key="description"
                    name="description"
                    content={defaultMetaDescription}
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta head-key="og-title" property="og:title" content={name} />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={defaultMetaDescription}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${HomeController.url()}`}
                />
            </Head>

            {/* Hero Section */}
            <section className="bg bg-white py-10 lg:py-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl px-4 text-center">
                    <Badge asChild variant="neutral" size="sm" className="mb-6">
                        <a
                            href="https://www.artificiallawyer.com/2026/01/26/vibecode-law-launches-an-open-platform-for-diy-ai-tools/"
                            target="_blank"
                            rel="noopener"
                        >
                            <Megaphone />
                            As featured on Artificial Lawyer
                        </a>
                    </Badge>
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        Learn. Share. Discover.
                    </h1>
                    <div className="mx-auto mt-8 max-w-4xl space-y-4 text-lg text-neutral-600 dark:text-neutral-400">
                        {isPreLaunch ? (
                            <p>
                                We're an open platform for apps built by legal
                                professionals. Learn to build, share what you've
                                made, and get discovered. Now in preview.
                            </p>
                        ) : (
                            <p className="mt-4 text-lg text-neutral-600 dark:text-neutral-400">
                                The latest legaltech projects, ranked by the
                                community.
                            </p>
                        )}

                        <div className="flex flex-col items-center justify-center gap-4 pt-4 md:flex-row">
                            <Button asChild size="lg">
                                <Link href={ShowcaseCreateController.url()}>
                                    Share Your Project
                                </Link>
                            </Button>
                            <Button asChild size="lg" variant="outline">
                                <Link href={HowItWorksController.url()}>
                                    How does it work?
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            {/* Projects */}
            <section className="bg-white dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl px-4">
                    {isPreLaunch ? (
                        featuredShowcases.length > 0 ? (
                            <div className="py-6">
                                <div className="mb-4 flex items-center gap-4">
                                    <h2 className="text-2xl font-semibold text-neutral-900 dark:text-white">
                                        Preview Projects
                                    </h2>
                                    <div className="h-px flex-1 bg-border/60 dark:border-neutral-800"></div>
                                </div>
                                <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                    {featuredShowcases.map(
                                        (showcase, index) => (
                                            <ProjectItem
                                                key={showcase.id}
                                                showcase={showcase}
                                                rank={index + 1}
                                            />
                                        ),
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="py-16 text-center">
                                <p className="text-neutral-500 dark:text-neutral-400">
                                    No projects yet. Be the first to submit one!
                                </p>
                            </div>
                        )
                    ) : months.length > 0 ? (
                        months.map((month) => (
                            <ProjectMonthSection
                                key={month}
                                month={month}
                                showcases={showcasesByMonth![month]}
                            />
                        ))
                    ) : (
                        <div className="py-16 text-center">
                            <p className="text-neutral-500 dark:text-neutral-400">
                                No projects yet. Be the first to submit one!
                            </p>
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
