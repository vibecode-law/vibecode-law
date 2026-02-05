import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical } from 'lucide-react';
import { ReactNode } from 'react';

interface SortableItemProps {
    id: number;
    children: ReactNode;
}

export function SortableItem({ id, children }: SortableItemProps) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div ref={setNodeRef} style={style} className="relative">
            <div className="flex items-center gap-2">
                <button
                    {...attributes}
                    {...listeners}
                    className="cursor-grab touch-none p-2 text-neutral-400 hover:text-neutral-600 active:cursor-grabbing dark:hover:text-neutral-300"
                    aria-label="Drag to reorder"
                >
                    <GripVertical className="size-5" />
                </button>
                <div className="flex-1">{children}</div>
            </div>
        </div>
    );
}
