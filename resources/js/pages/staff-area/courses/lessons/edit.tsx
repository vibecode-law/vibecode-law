import LessonFormFields from '@/components/courses/lesson-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, update } from '@/routes/staff/courses/lessons';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface LessonsEditProps {
    course: App.Http.Resources.Course.CourseResource;
    lesson: App.Http.Resources.Course.LessonResource;
}

export default function LessonsEdit({ course, lesson }: LessonsEditProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Edit ${lesson.title} - ${course.title}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url({ course: course.slug })}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to lessons
                        </Link>
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title={`Edit ${lesson.title}`}
                        description={`Update lesson details for ${course.title}`}
                    />
                    <div className="flex items-center gap-2">
                        {lesson.gated === true && (
                            <Badge className="bg-purple-500 text-white hover:bg-purple-500">
                                Gated
                            </Badge>
                        )}
                    </div>
                </div>

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={update.url({
                            course: course.slug,
                            lesson: lesson.slug,
                        })}
                        method="patch"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <LessonFormFields
                                    processing={processing}
                                    errors={errors}
                                    mode="edit"
                                    defaultValues={{
                                        title: lesson.title,
                                        slug: lesson.slug,
                                        tagline: lesson.tagline,
                                        description: lesson.description,
                                        learning_objectives:
                                            lesson.learning_objectives ?? null,
                                        copy: lesson.copy ?? null,
                                        asset_id: lesson.asset_id ?? null,
                                        gated: lesson.gated,
                                        visible: lesson.visible,
                                        publish_date:
                                            lesson.publish_date ?? null,
                                        thumbnail_url:
                                            lesson.thumbnail_url ?? null,
                                        thumbnail_crops:
                                            lesson.thumbnail_crops ?? null,
                                    }}
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
                                        processingLabel="Saving..."
                                    >
                                        Save changes
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
