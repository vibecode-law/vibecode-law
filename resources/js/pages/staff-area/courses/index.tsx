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
import { create, edit, reorder } from '@/routes/staff/courses';
import { index as lessonsIndex } from '@/routes/staff/courses/lessons';
import { Head, Link, router } from '@inertiajs/react';
import { BookOpen, Pencil, Plus } from 'lucide-react';
import { useEffect, useState } from 'react';

interface CoursesIndexProps {
    courses: App.Http.Resources.Course.CourseResource[];
}

export default function CoursesIndex({ courses }: CoursesIndexProps) {
    const [localCourses, setLocalCourses] = useState(courses);

    useEffect(() => {
        setLocalCourses(courses);
    }, [courses]);

    const handleReorder = (
        reorderedItems: App.Http.Resources.Course.CourseResource[],
    ) => {
        setLocalCourses(reorderedItems);

        router.post(
            reorder.url(),
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
            <Head title="Courses" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Courses"
                        description="Drag and drop to reorder courses"
                    />
                    <Button asChild>
                        <Link href={create.url()}>
                            <Plus className="mr-1.5 size-4" />
                            Create Course
                        </Link>
                    </Button>
                </div>

                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Courses</ListCardTitle>
                        <Badge variant="secondary">
                            {localCourses.length}{' '}
                            {localCourses.length === 1 ? 'course' : 'courses'}
                        </Badge>
                    </ListCardHeader>

                    {localCourses.length > 0 ? (
                        <ListCardContent>
                            <SortableList
                                items={localCourses}
                                onReorder={handleReorder}
                                orderKey="order"
                            >
                                {(course) => (
                                    <SortableItem
                                        key={course.id}
                                        id={course.id}
                                    >
                                        <div className="flex items-center gap-4 py-4">
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                                                        {course.title}
                                                    </h3>
                                                    {course.visible ===
                                                        true && (
                                                        <Badge className="bg-green-500 text-white hover:bg-green-500">
                                                            Visible
                                                        </Badge>
                                                    )}
                                                    {course.is_featured ===
                                                        true && (
                                                        <Badge className="bg-amber-500 text-white hover:bg-amber-500">
                                                            Featured
                                                        </Badge>
                                                    )}
                                                </div>
                                                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                                    {course.tagline}
                                                </p>
                                                <p className="mt-0.5 text-xs text-neutral-400 dark:text-neutral-500">
                                                    {course.lessons_count !==
                                                        undefined && (
                                                        <span>
                                                            {
                                                                course.lessons_count
                                                            }{' '}
                                                            {course.lessons_count ===
                                                            1
                                                                ? 'lesson'
                                                                : 'lessons'}
                                                        </span>
                                                    )}
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
                                                        href={lessonsIndex.url({
                                                            course: course.slug,
                                                        })}
                                                    >
                                                        <BookOpen className="size-4" />
                                                        Manage Lessons
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="gap-1.5"
                                                    asChild
                                                >
                                                    <Link
                                                        href={edit.url({
                                                            course: course.slug,
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
                        <ListCardEmpty>No courses found</ListCardEmpty>
                    )}
                </ListCard>
            </div>
        </StaffAreaLayout>
    );
}
