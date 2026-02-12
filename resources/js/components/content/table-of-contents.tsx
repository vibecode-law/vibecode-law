import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { ListIcon } from 'lucide-react';
import { useState } from 'react';

export interface NavigationItem {
    text: string;
    slug: string;
    level: number;
    children: NavigationItem[];
}

interface TableOfContentsProps {
    navigation: NavigationItem[];
    title?: string;
}

function NavigationItemComponent({
    item,
    onItemClick,
}: {
    item: NavigationItem;
    onItemClick?: () => void;
}) {
    const [isOpen, setIsOpen] = useState(false);
    const hasChildren = item.children.length > 0;

    if (!hasChildren) {
        return (
            <li>
                <a
                    href={`#${item.slug}`}
                    onClick={onItemClick}
                    className="block py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    {item.text}
                </a>
            </li>
        );
    }

    return (
        <li>
            <Collapsible open={isOpen} onOpenChange={setIsOpen}>
                <CollapsibleTrigger asChild>
                    <button
                        type="button"
                        className="block w-full py-1.5 text-left text-sm text-muted-foreground transition-colors hover:text-foreground"
                        onClick={() => setIsOpen(!isOpen)}
                    >
                        {item.text}
                    </button>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <ul className="mt-0.5 space-y-0.5 border-l pl-3">
                        <li>
                            <a
                                href={`#${item.slug}`}
                                onClick={onItemClick}
                                className="block py-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                            >
                                {item.text}
                            </a>
                        </li>
                        {item.children.map((child) => (
                            <NavigationItemComponent
                                key={child.slug}
                                item={child}
                                onItemClick={onItemClick}
                            />
                        ))}
                    </ul>
                </CollapsibleContent>
            </Collapsible>
        </li>
    );
}

function NavigationList({
    items,
    onItemClick,
}: {
    items: NavigationItem[];
    onItemClick?: () => void;
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <ul className="space-y-0.5">
            {items.map((item) => (
                <NavigationItemComponent
                    key={item.slug}
                    item={item}
                    onItemClick={onItemClick}
                />
            ))}
        </ul>
    );
}

function DesktopTableOfContents({ navigation, title }: TableOfContentsProps) {
    return (
        <nav className="sticky top-8">
            <h2 className="mb-4 text-sm font-semibold text-foreground">
                {title}
            </h2>
            <NavigationList items={navigation} />
        </nav>
    );
}

function MobileTableOfContents({ navigation, title }: TableOfContentsProps) {
    const [open, setOpen] = useState(false);

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
                <Button
                    variant="outline"
                    size="icon"
                    className="fixed right-6 bottom-6 z-40 size-12 rounded-full shadow-lg"
                    aria-label="Open table of contents"
                >
                    <ListIcon className="size-5" />
                </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-80 overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>{title}</SheetTitle>
                    <SheetDescription className="sr-only">
                        Navigate to different sections of this page
                    </SheetDescription>
                </SheetHeader>
                <div className="px-4 pb-4">
                    <NavigationList
                        items={navigation}
                        onItemClick={() => setOpen(false)}
                    />
                </div>
            </SheetContent>
        </Sheet>
    );
}

export function TableOfContents({
    navigation,
    title = 'Contents',
}: TableOfContentsProps) {
    if (navigation.length === 0) {
        return null;
    }

    return (
        <>
            {/* Desktop: Sticky sidebar */}
            <aside className="hidden w-64 shrink-0 lg:block xl:w-70 2xl:w-76">
                <DesktopTableOfContents navigation={navigation} title={title} />
            </aside>

            {/* Mobile: Floating button with sheet */}
            <div className="lg:hidden">
                <MobileTableOfContents navigation={navigation} title={title} />
            </div>
        </>
    );
}
