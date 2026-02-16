import { Badge } from '@/components/ui/badge';

interface CourseSkillsTagsProps {
    tags: App.Http.Resources.Course.CourseTagResource[];
}

export function CourseSkillsTags({ tags }: CourseSkillsTagsProps) {
    return (
        <div className="mt-8">
            <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                Skills You'll Gain
            </h2>
            <div className="mt-4 flex flex-wrap gap-2">
                {tags.map((tag) => (
                    <Badge key={tag.id} variant="secondary" size="sm">
                        {tag.name}
                    </Badge>
                ))}
            </div>
        </div>
    );
}
