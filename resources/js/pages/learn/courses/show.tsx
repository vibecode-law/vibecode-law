import CourseIndexController from '@/actions/App/Http/Controllers/Course/Public/CourseIndexController';
import LessonShowController from '@/actions/App/Http/Controllers/Course/Public/LessonShowController';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';

interface CourseShowProps {
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
        tags?: App.Http.Resources.Course.CourseTagResource[];
    };
}

export default function CourseShow({ course }: CourseShowProps) {
    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Courses', href: CourseIndexController.url() },
                { label: course.title },
            ]}
        >
            <Head title={course.title} />

            <section className="py-10 lg:py-16">
                <div className="mx-auto max-w-6xl px-4">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 dark:text-white">
                        {course.title}
                    </h1>
                    <p className="mt-4 text-lg text-neutral-600 dark:text-neutral-400">
                        {course.tagline}
                    </p>

                    {course.description_html && (
                        <div
                            className="prose dark:prose-invert mt-8 max-w-none"
                            dangerouslySetInnerHTML={{
                                __html: course.description_html,
                            }}
                        />
                    )}

                    {course.lessons && course.lessons.length > 0 && (
                        <div className="mt-12">
                            <h2 className="text-2xl font-bold text-neutral-900 dark:text-white">
                                Lessons
                            </h2>
                            <div className="mt-4 divide-y divide-neutral-200 dark:divide-neutral-800">
                                {course.lessons.map((lesson) => (
                                    <Link
                                        key={lesson.id}
                                        href={LessonShowController.url({
                                            course: course.slug,
                                            lesson: lesson.slug,
                                        })}
                                        className="block py-4 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900"
                                    >
                                        <h3 className="font-semibold text-neutral-900 dark:text-white">
                                            {lesson.title}
                                        </h3>
                                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                            {lesson.tagline}
                                        </p>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
