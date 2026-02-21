import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    ListCard,
    ListCardContent,
    ListCardEmpty,
    ListCardHeader,
    ListCardTitle,
} from '@/components/ui/list-card';
import { SortableItem } from '@/components/ui/sortable-item';
import { SortableList } from '@/components/ui/sortable-list';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { edit as courseEdit } from '@/routes/staff/academy/courses';
import { create, edit, reorder } from '@/routes/staff/academy/courses/lessons';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Pencil, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

interface LessonsIndexProps {
    course: App.Http.Resources.Course.CourseResource;
    lessons: App.Http.Resources.Course.LessonResource[];
}

export default function LessonsIndex({ course, lessons }: LessonsIndexProps) {
    const [localLessons, setLocalLessons] = useState(lessons);

    useEffect(() => {
        setLocalLessons(lessons);
    }, [lessons]);

    const handleReorder = (
        reorderedItems: App.Http.Resources.Course.LessonResource[],
    ) => {
        setLocalLessons(reorderedItems);

        router.post(
            reorder.url({ course: course.slug }),
            {
                items: reorderedItems.map((item) => ({
                    id: item.id,
                    order: item.order,
                })),
            },
            {
                preserveScroll: true,
                preserveState: true,
                only: [],
            },
        );
    };

    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Lessons - ${course.title}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={courseEdit.url({ course: course.slug })}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to {course.title}
                        </Link>
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title={`Lessons for ${course.title}`}
                        description="Drag and drop to reorder lessons"
                    />
                    <Button asChild>
                        <Link href={create.url({ course: course.slug })}>
                            <Plus className="mr-1.5 size-4" />
                            Create Lesson
                        </Link>
                    </Button>
                </div>

                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Lessons</ListCardTitle>
                        <Badge variant="secondary">
                            {localLessons.length}{' '}
                            {localLessons.length === 1 ? 'lesson' : 'lessons'}
                        </Badge>
                    </ListCardHeader>

                    {localLessons.length > 0 ? (
                        <ListCardContent>
                            <SortableList
                                items={localLessons}
                                onReorder={handleReorder}
                                orderKey="order"
                            >
                                {(lesson) => (
                                    <SortableItem
                                        key={lesson.id}
                                        id={lesson.id}
                                    >
                                        <div className="flex items-center gap-4 py-4">
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                                                        {lesson.title}
                                                    </h3>
                                                    {lesson.is_previewable ===
                                                        true && (
                                                        <Badge className="bg-green-500 text-white hover:bg-green-500">
                                                            Preview
                                                        </Badge>
                                                    )}
                                                    {lesson.is_scheduled ===
                                                        true && (
                                                        <Badge variant="secondary">
                                                            Not Published
                                                        </Badge>
                                                    )}
                                                    {lesson.gated === true && (
                                                        <Badge className="bg-purple-500 text-white hover:bg-purple-500">
                                                            Gated
                                                        </Badge>
                                                    )}
                                                </div>
                                                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                                    {lesson.tagline}
                                                </p>
                                            </div>

                                            <div className="flex shrink-0 items-center gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="gap-1.5"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit.url({
                                                            course: course.slug,
                                                            lesson: lesson.slug,
                                                        })}
                                                    >
                                                        <Pencil className="size-4" />
                                                        Edit
                                                    </Link>
                                                </Button>
                                            </div>
                                        </div>
                                    </SortableItem>
                                )}
                            </SortableList>
                        </ListCardContent>
                    ) : (
                        <ListCardEmpty>
                            No lessons found for this course
                        </ListCardEmpty>
                    )}
                </ListCard>
            </div>
        </StaffAreaLayout>
    );
}
