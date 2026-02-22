import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import { CourseCard } from '@/components/courses/course-card';
import { GuideCard, type GuideItem } from '@/components/courses/guide-card';
import { TabNav } from '@/components/navigation/tab-nav';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Lightbulb, Users } from 'lucide-react';
import { useState } from 'react';

interface CourseIndexProps {
    courses?: App.Http.Resources.Course.CourseResource[];
    courseProgress?: Record<
        number,
        {
            progressPercentage: number;
        }
    >;
    guides?: GuideItem[];
    totalEnrolledUsers?: number;
}

export default function CourseIndex({
    courses: propCourses,
    courseProgress = {},
    guides = [],
    totalEnrolledUsers = 0,
}: CourseIndexProps) {
    const { name, appUrl } = usePage<SharedData>().props;
    const [activeTab, setActiveTab] = useState<'courses' | 'guides'>('courses');

    const allCourses = propCourses ?? [];
    const featuredCourses = allCourses.filter((c) => c.is_featured === true);
    const displayedCourses = allCourses.filter((c) => c.is_featured !== true);

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn' },
            ]}
        >
            <Head title="Learn">
                <meta
                    head-key="description"
                    name="description"
                    content="Master vibecoding with structured courses from foundation to professional. Build legal tech faster with AI-assisted development."
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`Learn | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${LearnIndexController.url()}`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Master vibecoding with structured courses from foundation to professional. Build legal tech faster with AI-assisted development."
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-white py-10 lg:py-16 dark:bg-neutral-950">
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
                            <Users className="size-5" />
                            Join {totalEnrolledUsers.toLocaleString()} others
                            who are learning to build.
                        </span>
                    </p>
                </div>
            </section>

            {/* Tab Navigation */}
            <section className="border-b border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <TabNav
                        items={[
                            {
                                title: 'Courses',
                                onClick: () => setActiveTab('courses'),
                                isActive: activeTab === 'courses',
                            },
                            {
                                title: 'Guides',
                                onClick: () => setActiveTab('guides'),
                                isActive: activeTab === 'guides',
                            },
                        ]}
                        ariaLabel="Learn navigation"
                    />
                </div>
            </section>

            {/* Courses Content */}
            {activeTab === 'courses' && (
                <section className="bg-white pt-8 pb-8 dark:bg-neutral-950">
                    <div className="mx-auto max-w-6xl px-4">
                        <div className="grid gap-6 sm:grid-cols-2">
                            {featuredCourses.map((course) => (
                                <CourseCard
                                    key={course.id}
                                    course={course}
                                    progress={courseProgress[course.id]}
                                />
                            ))}
                            {displayedCourses.map((course) => (
                                <CourseCard
                                    key={course.id}
                                    course={course}
                                    progress={courseProgress[course.id]}
                                />
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Guides Content */}
            {activeTab === 'guides' && (
                <section className="bg-white pt-8 pb-8 dark:bg-neutral-950">
                    <div className="mx-auto max-w-6xl px-4">
                        {guides.length > 0 ? (
                            <div className="grid gap-4 sm:grid-cols-2">
                                {guides.map((guide) => (
                                    <GuideCard key={guide.slug} guide={guide} />
                                ))}
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
            )}
        </PublicLayout>
    );
}
