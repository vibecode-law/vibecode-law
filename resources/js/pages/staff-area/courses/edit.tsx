import CourseFormFields from '@/components/courses/course-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, update } from '@/routes/staff/courses';
import { index as lessonsIndex } from '@/routes/staff/courses/lessons';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, BookOpen } from 'lucide-react';

interface CoursesEditProps {
    course: App.Http.Resources.Course.CourseResource;
    experienceLevels: App.ValueObjects.FrontendEnum[];
}

export default function CoursesEdit({
    course,
    experienceLevels,
}: CoursesEditProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Edit ${course.title}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to courses
                        </Link>
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title={`Edit ${course.title}`}
                        description="Update course details and settings"
                    />
                    <div className="flex items-center gap-2">
                        {course.visible === true && (
                            <Badge className="bg-green-500 text-white hover:bg-green-500">
                                Visible
                            </Badge>
                        )}
                        {course.is_featured === true && (
                            <Badge className="bg-amber-500 text-white hover:bg-amber-500">
                                Featured
                            </Badge>
                        )}
                    </div>
                </div>

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={update.url({ course: course.slug })}
                        method="patch"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <CourseFormFields
                                    processing={processing}
                                    errors={errors}
                                    experienceLevels={experienceLevels}
                                    mode="edit"
                                    defaultValues={{
                                        title: course.title,
                                        slug: course.slug,
                                        tagline: course.tagline,
                                        description: course.description,
                                        learning_objectives:
                                            course.learning_objectives ?? null,
                                        experience_level:
                                            course.experience_level ?? null,
                                        publish_date:
                                            course.publish_date ?? null,
                                        user:
                                            course.user?.id !== undefined
                                                ? {
                                                      id: course.user.id,
                                                      name: `${course.user.first_name} ${course.user.last_name}`,
                                                      job_title:
                                                          course.user.job_title,
                                                      organisation:
                                                          course.user
                                                              .organisation,
                                                  }
                                                : null,
                                        visible: course.visible,
                                        is_featured: course.is_featured,
                                        thumbnail_url:
                                            course.thumbnail_url ?? null,
                                        thumbnail_crops:
                                            course.thumbnail_crops ?? null,
                                    }}
                                />

                                <div className="mt-6 flex items-center justify-end gap-3 border-t pt-6 dark:border-neutral-800">
                                    <Button
                                        variant="outline"
                                        type="button"
                                        asChild
                                    >
                                        <Link href={index.url()}>Cancel</Link>
                                    </Button>
                                    <SubmitButton
                                        processing={processing}
                                        processingLabel="Saving..."
                                    >
                                        Save changes
                                    </SubmitButton>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="font-semibold text-neutral-900 dark:text-white">
                                Lessons
                            </h3>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                {course.lessons_count !== undefined
                                    ? `${course.lessons_count} ${course.lessons_count === 1 ? 'lesson' : 'lessons'}`
                                    : 'No lessons'}
                            </p>
                        </div>
                        <Button variant="outline" asChild>
                            <Link
                                href={lessonsIndex.url({
                                    course: course.slug,
                                })}
                            >
                                <BookOpen className="mr-1.5 size-4" />
                                Manage Lessons
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </StaffAreaLayout>
    );
}
