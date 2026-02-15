import CourseIndexController from '@/actions/App/Http/Controllers/Course/Public/CourseIndexController';
import CourseShowController from '@/actions/App/Http/Controllers/Course/Public/CourseShowController';
import LessonShowController from '@/actions/App/Http/Controllers/Course/Public/LessonShowController';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/react';

interface LessonShowProps {
    lesson: App.Http.Resources.Course.LessonResource;
    course: App.Http.Resources.Course.CourseResource & {
        lessons?: App.Http.Resources.Course.LessonResource[];
    };
}

export default function LessonShow({ lesson, course }: LessonShowProps) {
    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Courses', href: CourseIndexController.url() },
                {
                    label: course.title,
                    href: CourseShowController.url({
                        course: course.slug,
                    }),
                },
                { label: lesson.title },
            ]}
        >
            <Head title={`${lesson.title} - ${course.title}`} />

            <section className="py-10 lg:py-16">
                <div className="mx-auto max-w-6xl px-4 lg:grid lg:grid-cols-4 lg:gap-8">
                    {/* Sidebar navigation */}
                    <aside className="hidden lg:block">
                        <h3 className="font-semibold text-neutral-900 dark:text-white">
                            {course.title}
                        </h3>
                        <nav className="mt-4 space-y-1">
                            {course.lessons?.map((navLesson) => (
                                <Link
                                    key={navLesson.id}
                                    href={LessonShowController.url({
                                        course: course.slug,
                                        lesson: navLesson.slug,
                                    })}
                                    className={`block rounded-md px-3 py-2 text-sm ${
                                        navLesson.id === lesson.id
                                            ? 'bg-neutral-100 font-semibold text-neutral-900 dark:bg-neutral-800 dark:text-white'
                                            : 'text-neutral-600 hover:bg-neutral-50 dark:text-neutral-400 dark:hover:bg-neutral-900'
                                    }`}
                                >
                                    {navLesson.title}
                                </Link>
                            ))}
                        </nav>
                    </aside>

                    {/* Main content */}
                    <div className="lg:col-span-3">
                        <h1 className="text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            {lesson.title}
                        </h1>
                        <p className="mt-2 text-lg text-neutral-600 dark:text-neutral-400">
                            {lesson.tagline}
                        </p>

                        {lesson.description_html && (
                            <div
                                className="prose dark:prose-invert mt-8 max-w-none"
                                dangerouslySetInnerHTML={{
                                    __html: lesson.description_html,
                                }}
                            />
                        )}

                        {lesson.copy_html && (
                            <div
                                className="prose dark:prose-invert mt-8 max-w-none"
                                dangerouslySetInnerHTML={{
                                    __html: lesson.copy_html,
                                }}
                            />
                        )}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
