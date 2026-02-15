import CourseShowController from '@/actions/App/Http/Controllers/Course/Public/CourseShowController';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';

interface CourseIndexProps {
    courses: App.Http.Resources.Course.CourseResource[];
}

export default function CourseIndex({ courses }: CourseIndexProps) {
    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Courses' },
            ]}
        >
            <Head title="Courses" />

            <section className="py-10 lg:py-16">
                <div className="mx-auto max-w-6xl px-4">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 dark:text-white">
                        Courses
                    </h1>
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {courses.map((course) => (
                            <Link
                                key={course.id}
                                href={CourseShowController.url({
                                    course: course.slug,
                                })}
                                className="rounded-lg border border-neutral-200 p-6 transition-colors hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900"
                            >
                                <h2 className="text-lg font-semibold text-neutral-900 dark:text-white">
                                    {course.title}
                                </h2>
                                <p className="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                                    {course.tagline}
                                </p>
                            </Link>
                        ))}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
