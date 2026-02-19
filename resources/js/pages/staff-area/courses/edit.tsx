import CourseFormFields from '@/components/courses/staff-area/course-form-fields';
import PublishingSection from '@/components/courses/staff-area/publishing-section';
import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type ChecklistItem } from '@/components/ui/readiness-checklist';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { show as courseShow } from '@/routes/learn/courses';
import { index, publishDate, update } from '@/routes/staff/academy/courses';
import { index as lessonsIndex } from '@/routes/staff/academy/courses/lessons';
import { Form, Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, BookOpen, ExternalLink } from 'lucide-react';
import { useMemo } from 'react';

interface CoursesEditProps {
    course: App.Http.Resources.Course.CourseResource;
    experienceLevels: App.ValueObjects.FrontendEnum[];
    availableTags: App.Http.Resources.TagResource[];
}

export default function CoursesEdit({
    course,
    experienceLevels,
    availableTags,
}: CoursesEditProps) {
    const { errors: pageErrors } = usePage().props;
    const lessons = useMemo(
        () =>
            (course.lessons ??
                []) as App.Http.Resources.Course.LessonResource[],
        [course.lessons],
    );

    const publishForm = useForm({
        publish_date: course.publish_date ?? '',
    });

    const requiredChecks: ChecklistItem[] = useMemo(
        () => [
            { label: 'Title', completed: course.title.length > 0 },
            { label: 'Slug', completed: course.slug.length > 0 },
            {
                label: 'Tagline',
                completed: course.tagline !== null && course.tagline.length > 0,
            },
            {
                label: 'Description',
                completed:
                    course.description !== undefined &&
                    course.description !== null &&
                    course.description.length > 0,
            },
            {
                label: 'Learning objectives',
                completed:
                    course.learning_objectives !== undefined &&
                    course.learning_objectives !== null &&
                    course.learning_objectives.length > 0,
            },
            {
                label: 'Experience level',
                completed:
                    course.experience_level !== undefined &&
                    course.experience_level !== null,
            },
        ],
        [course],
    );

    const optionalChecks: ChecklistItem[] = useMemo(
        () => [
            {
                label: 'Thumbnail',
                completed: course.thumbnail_url !== null,
            },
            {
                label: 'Tags',
                completed: Array.isArray(course.tags) && course.tags.length > 0,
            },
        ],
        [course],
    );

    const lessonDateMatch = useMemo(() => {
        if (publishForm.data.publish_date === '') {
            return false;
        }

        return lessons.some(
            (lesson) => lesson.publish_date === publishForm.data.publish_date,
        );
    }, [lessons, publishForm.data.publish_date]);

    const lessonChecks: ChecklistItem[] = useMemo(
        () => [
            {
                label: 'At least one lesson with matching publish date',
                completed: lessons.length > 0 && lessonDateMatch,
            },
        ],
        [lessons, lessonDateMatch],
    );

    const allRequiredComplete = requiredChecks.every(
        (check) => check.completed === true,
    );

    const canPublish =
        publishForm.data.publish_date !== '' &&
        allRequiredComplete &&
        lessonDateMatch;

    function handlePublishSubmit(e: React.FormEvent) {
        e.preventDefault();
        publishForm.patch(publishDate.url({ course: course.slug }), {
            preserveScroll: true,
        });
    }

    function handleClearPublishDate() {
        publishForm.setData('publish_date', '');
        router.patch(
            publishDate.url({ course: course.slug }),
            { publish_date: '' },
            { preserveScroll: true },
        );
    }

    function handleAllowPreviewChange(checked: boolean) {
        router.patch(
            publishDate.url({ course: course.slug }),
            { allow_preview: checked },
            { preserveScroll: true },
        );
    }

    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Edit ${course.title}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to courses
                        </Link>
                    </Button>
                    <Button variant="ghost" size="sm" asChild>
                        <a
                            href={courseShow.url({ course: course.slug })}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <ExternalLink className="mr-1.5 size-4" />
                            Preview
                        </a>
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title={`Edit ${course.title}`}
                        description="Update course details and settings"
                    />
                    <div className="flex items-center gap-2">
                        {course.is_previewable === true && (
                            <Badge className="bg-green-500 text-white hover:bg-green-500">
                                Preview
                            </Badge>
                        )}
                        {course.is_scheduled === true && (
                            <Badge variant="secondary">Not Published</Badge>
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
                                    availableTags={availableTags}
                                    defaultTagIds={(
                                        (course.tags as
                                            | App.Http.Resources.TagResource[]
                                            | undefined) ?? []
                                    ).map((tag) => tag.id)}
                                    allowPreview={
                                        course.allow_preview === true ||
                                        (course.publish_date !== undefined &&
                                            course.publish_date !== null)
                                    }
                                    mode="edit"
                                    defaultValues={{
                                        title: course.title,
                                        slug: course.slug,
                                        tagline: course.tagline ?? undefined,
                                        description:
                                            course.description ?? undefined,
                                        learning_objectives:
                                            course.learning_objectives ?? null,
                                        experience_level:
                                            course.experience_level ?? null,
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

                <PublishingSection
                    publishDate={course.publish_date ?? null}
                    allowPreview={course.allow_preview === true}
                    entityLabel="course"
                    formPublishDate={publishForm.data.publish_date}
                    onFormPublishDateChange={(date) =>
                        publishForm.setData('publish_date', date)
                    }
                    onSubmit={handlePublishSubmit}
                    onClearPublishDate={handleClearPublishDate}
                    onAllowPreviewChange={handleAllowPreviewChange}
                    processing={publishForm.processing}
                    publishDateError={publishForm.errors.publish_date}
                    allowPreviewError={
                        typeof pageErrors.allow_preview === 'string'
                            ? pageErrors.allow_preview
                            : undefined
                    }
                    allRequiredComplete={allRequiredComplete}
                    canPublish={canPublish}
                    checkGroups={[
                        {
                            title: 'Required Fields',
                            items: requiredChecks,
                            variant: 'required',
                        },
                        {
                            title: 'Optional Fields',
                            items: optionalChecks,
                            variant: 'optional',
                        },
                        {
                            title: 'Lessons',
                            items: lessonChecks,
                            variant: 'required',
                        },
                    ]}
                />
            </div>
        </StaffAreaLayout>
    );
}
