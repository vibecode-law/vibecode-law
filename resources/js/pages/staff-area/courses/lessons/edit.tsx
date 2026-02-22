import LessonFormFields from '@/components/courses/staff-area/lesson-form-fields';
import LessonVideoHostSection from '@/components/courses/staff-area/lesson-video-host-section';
import PublishingSection from '@/components/courses/staff-area/publishing-section';
import HeadingSmall from '@/components/heading/heading-small';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type ChecklistItem } from '@/components/ui/readiness-checklist';
import { Spinner } from '@/components/ui/spinner';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { show as lessonShow } from '@/routes/learn/courses/lessons';
import {
    allowPreview,
    generateCopywriter,
    index,
    publishDate,
    update,
} from '@/routes/staff/academy/courses/lessons';
import { Form, Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, ExternalLink, Sparkles } from 'lucide-react';
import { useMemo, useState } from 'react';

interface LessonsEditProps {
    course: App.Http.Resources.Course.CourseResource;
    lesson: App.Http.Resources.Course.LessonResource & {
        instructors?: App.Http.Resources.User.UserResource[];
    };
    availableTags: App.Http.Resources.TagResource[];
}

export default function LessonsEdit({
    course,
    lesson,
    availableTags,
}: LessonsEditProps) {
    const { errors: pageErrors } = usePage().props;

    const publishForm = useForm({
        publish_date: lesson.publish_date ?? '',
    });

    const isSyncedWithVideoHost =
        lesson.asset_id !== undefined &&
        lesson.asset_id !== null &&
        lesson.asset_id.length > 0 &&
        lesson.playback_id !== undefined &&
        lesson.playback_id !== null &&
        lesson.playback_id.length > 0 &&
        lesson.duration_seconds !== undefined &&
        lesson.duration_seconds !== null;

    const previewRequiredChecks: ChecklistItem[] = useMemo(
        () => [
            { label: 'Title', completed: lesson.title.length > 0 },
            { label: 'Slug', completed: lesson.slug.length > 0 },
            {
                label: 'Tagline',
                completed: lesson.tagline !== null && lesson.tagline.length > 0,
            },
        ],
        [lesson],
    );

    const publishRequiredChecks: ChecklistItem[] = useMemo(
        () => [
            ...previewRequiredChecks,
            {
                label: 'Description',
                completed:
                    lesson.description !== undefined &&
                    lesson.description !== null &&
                    lesson.description.length > 0,
            },
            {
                label: 'Learning objectives',
                completed:
                    lesson.learning_objectives !== undefined &&
                    lesson.learning_objectives !== null &&
                    lesson.learning_objectives.length > 0,
            },
            {
                label: 'Synced with video host',
                completed: isSyncedWithVideoHost,
            },
            {
                label: 'Parsed transcript lines',
                completed: lesson.has_transcript_lines === true,
            },
        ],
        [previewRequiredChecks, lesson, isSyncedWithVideoHost],
    );

    const canEnablePreview = previewRequiredChecks.every(
        (check) => check.completed === true,
    );

    const optionalChecks: ChecklistItem[] = useMemo(
        () => [
            {
                label: 'Copy',
                completed:
                    lesson.copy !== undefined &&
                    lesson.copy !== null &&
                    lesson.copy.length > 0,
            },
            {
                label: 'Instructors',
                completed:
                    Array.isArray(lesson.instructors) &&
                    lesson.instructors.length > 0,
            },
            {
                label: 'Thumbnail',
                completed: lesson.thumbnail_url !== null,
            },
            {
                label: 'Tags',
                completed: Array.isArray(lesson.tags) && lesson.tags.length > 0,
            },
        ],
        [lesson],
    );

    const allRequiredComplete = publishRequiredChecks.every(
        (check) => check.completed === true,
    );

    const canPublish =
        publishForm.data.publish_date !== '' && allRequiredComplete;

    function handlePublishSubmit(e: React.FormEvent) {
        e.preventDefault();
        publishForm.patch(
            publishDate.url({
                course: course.slug,
                lesson: lesson.slug,
            }),
            { preserveScroll: true },
        );
    }

    function handleClearPublishDate() {
        publishForm.setData('publish_date', '');
        router.patch(
            publishDate.url({
                course: course.slug,
                lesson: lesson.slug,
            }),
            { publish_date: '' },
            { preserveScroll: true },
        );
    }

    function handleAllowPreviewChange(checked: boolean) {
        router.patch(
            allowPreview.url({
                course: course.slug,
                lesson: lesson.slug,
            }),
            { allow_preview: checked },
            { preserveScroll: true },
        );
    }

    const [isGenerating, setIsGenerating] = useState(false);
    const [generateDialogOpen, setGenerateDialogOpen] = useState(false);
    const [formKey, setFormKey] = useState(0);
    const hasTxtTranscript = lesson.has_txt_transcript === true;

    function handleGenerateCopywriter() {
        setIsGenerating(true);
        setGenerateDialogOpen(false);
        router.post(
            generateCopywriter.url({
                course: course.slug,
                lesson: lesson.slug,
            }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setFormKey((prev) => prev + 1);
                },
                onFinish: () => {
                    setIsGenerating(false);
                },
            },
        );
    }

    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Edit ${lesson.title} - ${course.title}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url({ course: course.slug })}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to lessons
                        </Link>
                    </Button>
                    <Button variant="ghost" size="sm" asChild>
                        <a
                            href={lessonShow.url({
                                course: course.slug,
                                lesson: lesson.slug,
                            })}
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
                        title={`Edit ${lesson.title}`}
                        description={`Update lesson details for ${course.title}`}
                    />
                    <div className="flex items-center gap-2">
                        {lesson.is_previewable === true && (
                            <Badge className="bg-green-500 text-white hover:bg-green-500">
                                Preview
                            </Badge>
                        )}
                        {lesson.is_scheduled === true && (
                            <Badge variant="secondary">Not Published</Badge>
                        )}
                        {lesson.gated === true && (
                            <Badge className="bg-purple-500 text-white hover:bg-purple-500">
                                Gated
                            </Badge>
                        )}
                    </div>
                </div>

                <div className="relative rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    {isGenerating === true && (
                        <div className="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 rounded-lg bg-white/80 dark:bg-neutral-900/80">
                            <Spinner className="size-8 text-neutral-500" />
                            <p className="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                                Generating content from transcript...
                            </p>
                            <p className="text-xs text-neutral-400 dark:text-neutral-500">
                                This may take up to a minute
                            </p>
                        </div>
                    )}
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
                                    key={formKey}
                                    processing={processing}
                                    errors={errors}
                                    availableTags={availableTags}
                                    defaultTagIds={(
                                        (lesson.tags as
                                            | App.Http.Resources.TagResource[]
                                            | undefined) ?? []
                                    ).map((tag) => tag.id)}
                                    isPublished={
                                        lesson.allow_preview === true ||
                                        (lesson.publish_date !== undefined &&
                                            lesson.publish_date !== null)
                                    }
                                    mode="edit"
                                    defaultValues={{
                                        title: lesson.title,
                                        slug: lesson.slug,
                                        tagline: lesson.tagline,
                                        description: lesson.description,
                                        learning_objectives:
                                            lesson.learning_objectives ?? null,
                                        copy: lesson.copy ?? null,
                                        gated: lesson.gated,
                                        thumbnail_url:
                                            lesson.thumbnail_url ?? null,
                                        thumbnail_crops:
                                            lesson.thumbnail_crops ?? null,
                                        instructors: (
                                            lesson.instructors ?? []
                                        ).map((u) => ({
                                            id: u.id!,
                                            first_name: u.first_name,
                                            last_name: u.last_name,
                                            job_title: u.job_title,
                                            organisation: u.organisation,
                                        })),
                                    }}
                                />

                                <div className="mt-6 flex items-center justify-between border-t pt-6 dark:border-neutral-800">
                                    <AlertDialog
                                        open={generateDialogOpen}
                                        onOpenChange={setGenerateDialogOpen}
                                    >
                                        <AlertDialogTrigger asChild>
                                            <Button
                                                variant="outline"
                                                type="button"
                                                disabled={
                                                    !hasTxtTranscript ||
                                                    isGenerating
                                                }
                                            >
                                                <Sparkles className="mr-1.5 size-4" />
                                                {isGenerating
                                                    ? 'Generating...'
                                                    : 'Generate content'}
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>
                                                    Generate content from
                                                    transcript
                                                </AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This will use AI to generate
                                                    content from the lesson
                                                    transcript and overwrite the
                                                    following fields:
                                                    <strong className="mt-2 block">
                                                        Tagline, Description,
                                                        Learning Objectives,
                                                        Copy, and Tags
                                                    </strong>
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel
                                                    disabled={isGenerating}
                                                >
                                                    Cancel
                                                </AlertDialogCancel>
                                                <AlertDialogAction
                                                    onClick={
                                                        handleGenerateCopywriter
                                                    }
                                                    disabled={isGenerating}
                                                >
                                                    {isGenerating
                                                        ? 'Generating...'
                                                        : 'Generate'}
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>

                                    <div className="flex items-center gap-3">
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
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <LessonVideoHostSection
                    courseSlug={course.slug}
                    lessonSlug={lesson.slug}
                    assetId={lesson.asset_id ?? null}
                    playbackId={lesson.playback_id ?? null}
                    durationSeconds={lesson.duration_seconds ?? null}
                    hasVttTranscript={lesson.has_vtt_transcript === true}
                    hasTxtTranscript={lesson.has_txt_transcript === true}
                    hasTranscriptLines={lesson.has_transcript_lines === true}
                />

                <PublishingSection
                    publishDate={lesson.publish_date ?? null}
                    allowPreview={lesson.allow_preview === true}
                    entityLabel="lesson"
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
                    canEnablePreview={canEnablePreview}
                    canPublish={canPublish}
                    checkGroups={[
                        {
                            title: 'Preview Requirements',
                            items: previewRequiredChecks,
                            variant: 'required',
                        },
                        {
                            title: 'Publish Requirements',
                            items: publishRequiredChecks,
                            variant: 'required',
                        },
                        {
                            title: 'Optional Fields',
                            items: optionalChecks,
                            variant: 'optional',
                        },
                    ]}
                />
            </div>
        </StaffAreaLayout>
    );
}
