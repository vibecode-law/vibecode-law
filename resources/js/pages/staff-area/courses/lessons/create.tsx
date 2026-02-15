import LessonFormFields from '@/components/courses/lesson-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, store } from '@/routes/staff/courses/lessons';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface LessonsCreateProps {
    course: App.Http.Resources.Course.CourseResource;
}

export default function LessonsCreate({ course }: LessonsCreateProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Create Lesson - ${course.title}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url({ course: course.slug })}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to lessons
                        </Link>
                    </Button>
                </div>

                <HeadingSmall
                    title="Create Lesson"
                    description={`Add a new lesson to ${course.title}.`}
                />

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={store.url({ course: course.slug })}
                        method="post"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <LessonFormFields
                                    processing={processing}
                                    errors={errors}
                                    mode="create"
                                />

                                <div className="mt-6 flex items-center justify-end gap-3 border-t pt-6 dark:border-neutral-800">
                                    <Button
                                        variant="outline"
                                        type="button"
                                        asChild
                                    >
                                        <Link
                                            href={index.url({
                                                course: course.slug,
                                            })}
                                        >
                                            Cancel
                                        </Link>
                                    </Button>
                                    <SubmitButton
                                        processing={processing}
                                        processingLabel="Creating..."
                                    >
                                        Create Lesson
                                    </SubmitButton>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </StaffAreaLayout>
    );
}
