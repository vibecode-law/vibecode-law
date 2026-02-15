import CourseFormFields from '@/components/courses/course-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, store } from '@/routes/staff/courses';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface CoursesCreateProps {
    experienceLevels: App.ValueObjects.FrontendEnum[];
}

export default function CoursesCreate({
    experienceLevels,
}: CoursesCreateProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title="Create Course" />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to courses
                        </Link>
                    </Button>
                </div>

                <HeadingSmall
                    title="Create Course"
                    description="Create a new course for the academy."
                />

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={store.url()}
                        method="post"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <CourseFormFields
                                    processing={processing}
                                    errors={errors}
                                    experienceLevels={experienceLevels}
                                    mode="create"
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
                                        processingLabel="Creating..."
                                    >
                                        Create Course
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
