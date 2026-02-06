import WallOfLoveController from '@/actions/App/Http/Controllers/WallOfLove/WallOfLoveController';
import { AvatarFallback } from '@/components/ui/avatar-fallback';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { ExternalLink, Quote } from 'lucide-react';

interface WallOfLoveProps {
    testimonials: App.Http.Resources.TestimonialResource[];
    pressCoverage: App.Http.Resources.PressCoverageResource[];
}

export default function WallOfLove({
    testimonials,
    pressCoverage,
}: WallOfLoveProps) {
    const { name, appUrl } = usePage<SharedData>().props;

    return (
        <PublicLayout>
            <Head title="Wall of Love">
                <meta
                    head-key="description"
                    name="description"
                    content="Community testimonials and press coverage for vibecode.law"
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`Wall of Love - ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Community testimonials and press coverage for vibecode.law"
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${WallOfLoveController.url()}`}
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-white py-16 lg:py-24 dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl px-4 text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        Wall of Love
                    </h1>
                    <p className="mx-auto mt-6 max-w-2xl text-lg text-neutral-600 dark:text-neutral-400">
                        What the community and press are saying about
                        vibecode.law
                    </p>
                </div>
            </section>

            {/* Testimonials Section */}
            {testimonials.length > 0 && (
                <section className="border-t border-neutral-200 bg-neutral-50 py-16 dark:border-neutral-800 dark:bg-neutral-900">
                    <div className="mx-auto max-w-5xl px-4">
                        <div className="mb-10 text-center">
                            <h2 className="text-3xl font-bold text-neutral-900 dark:text-white">
                                Community Testimonials
                            </h2>
                            <p className="mt-3 text-neutral-600 dark:text-neutral-400">
                                Hear from our community members
                            </p>
                        </div>

                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {testimonials.map((testimonial) => (
                                <div
                                    key={testimonial.id}
                                    className="relative rounded-lg border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-950"
                                >
                                    <Quote className="mb-4 size-6 text-amber-500" />

                                    <p className="mb-6 text-neutral-700 dark:text-neutral-300">
                                        "{testimonial.content}"
                                    </p>

                                    <div className="flex items-center gap-3">
                                        <AvatarFallback
                                            name={
                                                testimonial.display_name ??
                                                'Anonymous'
                                            }
                                            imageUrl={testimonial.avatar}
                                            size="sm"
                                            shape="circle"
                                        />
                                        <div className="min-w-0 flex-1">
                                            {testimonial.display_name && (
                                                <p className="font-semibold text-neutral-900 dark:text-white">
                                                    {testimonial.display_name}
                                                </p>
                                            )}
                                            {(testimonial.display_job_title ||
                                                testimonial.display_organisation) && (
                                                <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                                    {
                                                        testimonial.display_job_title
                                                    }
                                                    {testimonial.display_job_title &&
                                                        testimonial.display_organisation &&
                                                        ' at '}
                                                    {
                                                        testimonial.display_organisation
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Press Coverage Section */}
            {pressCoverage.length > 0 && (
                <section className="border-t border-neutral-200 bg-white py-16 dark:border-neutral-800 dark:bg-neutral-950">
                    <div className="mx-auto max-w-5xl px-4">
                        <div className="mb-10 text-center">
                            <h2 className="text-3xl font-bold text-neutral-900 dark:text-white">
                                Press Coverage
                            </h2>
                            <p className="mt-3 text-neutral-600 dark:text-neutral-400">
                                Featured in leading publications
                            </p>
                        </div>

                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {pressCoverage.map((article) => (
                                <a
                                    key={article.id}
                                    href={article.url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="group relative rounded-lg border border-neutral-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-neutral-700 dark:bg-neutral-950"
                                >
                                    <div className="mb-4 overflow-hidden rounded-md">
                                        {article.thumbnail_url ? (
                                            <img
                                                src={article.thumbnail_url}
                                                alt={article.title}
                                                className="aspect-square w-full object-cover transition-transform group-hover:scale-105"
                                            />
                                        ) : (
                                            <div className="flex aspect-square w-full items-center justify-center rounded bg-gradient-to-br from-amber-400 to-amber-600 text-3xl font-bold text-white">
                                                {article.publication_name
                                                    .split(/\s+/)
                                                    .slice(0, 2)
                                                    .map((word) => word[0])
                                                    .join('')
                                                    .toUpperCase()}
                                            </div>
                                        )}
                                    </div>

                                    <div className="mb-2 flex items-center gap-2">
                                        <Badge variant="outline" size="sm">
                                            {article.publication_name}
                                        </Badge>
                                        <span className="text-xs text-neutral-500 dark:text-neutral-400">
                                            {article.publication_date}
                                        </span>
                                    </div>

                                    <h3 className="mb-2 font-semibold text-neutral-900 group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-500">
                                        {article.title}
                                        <ExternalLink className="ml-1 inline size-4" />
                                    </h3>

                                    {article.excerpt && (
                                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                            {article.excerpt}
                                        </p>
                                    )}
                                </a>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Media CTA Section */}
            <section className="border-t border-neutral-200 bg-neutral-50 py-16 dark:border-neutral-800 dark:bg-neutral-900">
                <div className="mx-auto max-w-3xl px-4 text-center">
                    <h2 className="text-2xl font-bold text-neutral-900 dark:text-white">
                        Want to feature vibecode.law?
                    </h2>
                    <p className="mt-3 text-neutral-600 dark:text-neutral-400">
                        We'd love to hear from media outlets and journalists
                        interested in covering our platform.
                    </p>
                    <div className="mt-6">
                        <Button asChild size="lg">
                            <a href="mailto:press@vibecode.law">Get in Touch</a>
                        </Button>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
