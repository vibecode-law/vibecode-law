import HomeController from '@/actions/App/Http/Controllers/HomeController';
import ResourcesShowController from '@/actions/App/Http/Controllers/Resources/ResourcesShowController';
import HowItWorksController from '@/actions/App/Http/Controllers/Showcase/Help/HowItWorksController';
import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import { ProjectMonthSection } from '@/components/showcase/showcase-month-section';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { PlayCircle } from 'lucide-react';

interface HomeProps {
    showcasesByMonth?: Record<
        string,
        App.Http.Resources.Showcase.ShowcaseResource[]
    >;
}

export default function Home({ showcasesByMonth }: HomeProps) {
    const { name, appUrl, defaultMetaDescription } =
        usePage<SharedData>().props;

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
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl lg:text-6xl dark:text-white">
                        Learn. Share. Discover.
                    </h1>
                    <div className="mx-auto mt-8 max-w-4xl space-y-6 text-lg text-neutral-600 dark:text-neutral-400">
                        <p className="text-lg text-neutral-600 sm:text-xl dark:text-neutral-400">
                            An open platform for legal professionals building
                            with AI.
                        </p>

                        <div className="flex flex-col items-center justify-center gap-4 py-2 md:flex-row">
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
                        <p className="flex flex-col items-center justify-center gap-2 text-sm text-neutral-600 md:flex-row md:text-base dark:text-neutral-400">
                            <span className="flex items-center justify-start gap-2">
                                <PlayCircle className="size-5" />
                                New to building?{' '}
                            </span>
                            <Link
                                href={ResourcesShowController.url(
                                    'start-vibecoding',
                                )}
                                className="font-bold underline underline-offset-4 transition-colors hover:text-neutral-900 dark:hover:text-white"
                            >
                                Make a demo app in three minutes.
                            </Link>
                        </p>
                    </div>
                </div>
            </section>

            {/* Projects */}
            <section className="bg-white dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl px-4">
                    {months.length > 0 ? (
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
