import { Badge } from '@/components/ui/badge';

interface TagListProps {
    tags: App.Http.Resources.TagResource[];
}

export function TagList({ tags }: TagListProps) {
    return (
        <div className="flex flex-wrap gap-1.5">
            {tags.map((tag) => (
                <Badge key={tag.id} variant="secondary" size="sm">
                    {tag.name}
                </Badge>
            ))}
        </div>
    );
}
