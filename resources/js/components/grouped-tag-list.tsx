import { Badge } from '@/components/ui/badge';
import { useMemo } from 'react';

interface GroupedTagListProps {
    tags: App.Http.Resources.TagResource[];
}

export function GroupedTagList({ tags }: GroupedTagListProps) {
    const groupedTags = useMemo(() => {
        const groups = new Map<string, App.Http.Resources.TagResource[]>();

        for (const tag of tags) {
            const label = tag.type.label;
            const existing = groups.get(label);

            if (existing) {
                existing.push(tag);
            } else {
                groups.set(label, [tag]);
            }
        }

        return groups;
    }, [tags]);

    return (
        <div className="space-y-5">
            {[...groupedTags.entries()].map(([typeLabel, typeTags]) => (
                <div key={typeLabel}>
                    <p className="mb-2 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                        {typeLabel}
                    </p>
                    <div className="flex flex-wrap gap-1.5">
                        {typeTags.map((tag) => (
                            <Badge key={tag.id} variant="secondary" size="sm">
                                {tag.name}
                            </Badge>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}
