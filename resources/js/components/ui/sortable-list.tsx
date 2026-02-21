import {
    DndContext,
    DragEndEvent,
    KeyboardSensor,
    PointerSensor,
    closestCenter,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { ReactNode } from 'react';

interface SortableListProps<T extends { id: number; [key: string]: unknown }> {
    items: T[];
    onReorder: (items: T[]) => void;
    children: (item: T) => ReactNode;
    orderKey?: string;
}

export function SortableList<T extends { id: number; [key: string]: unknown }>({
    items,
    onReorder,
    children,
    orderKey = 'display_order',
}: SortableListProps<T>) {
    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        }),
    );

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (over === null || active.id === over.id) {
            return;
        }

        const oldIndex = items.findIndex((item) => item.id === active.id);
        const newIndex = items.findIndex((item) => item.id === over.id);

        if (oldIndex === -1 || newIndex === -1) {
            return;
        }

        // Create a new array with the item moved
        const reorderedItems = [...items];
        const [movedItem] = reorderedItems.splice(oldIndex, 1);
        reorderedItems.splice(newIndex, 0, movedItem);

        // Update order for all items
        const updatedItems = reorderedItems.map((item, index) => ({
            ...item,
            [orderKey]: index,
        }));

        onReorder(updatedItems);
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragEnd={handleDragEnd}
        >
            <SortableContext
                items={items.map((item) => item.id)}
                strategy={verticalListSortingStrategy}
            >
                <div className="divide-y divide-neutral-200 dark:divide-neutral-800">
                    {items.map((item) => children(item))}
                </div>
            </SortableContext>
        </DndContext>
    );
}
