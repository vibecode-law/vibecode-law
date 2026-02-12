import {
    TableOfContents,
    type NavigationItem,
} from '@/components/content/table-of-contents';

interface ContentLayoutProps {
    content: string;
    navigation: NavigationItem[];
    articleClassName?: string;
}

export function ContentLayout({
    content,
    navigation,
    articleClassName = 'legal-content',
}: ContentLayoutProps) {
    return (
        <section className="bg-white py-12 dark:bg-neutral-950">
            <div className="mx-auto max-w-6xl px-8 lg:px-4">
                <div className="flex lg:gap-12">
                    <article
                        className={`${articleClassName} max-w-4xl min-w-0 flex-1 lg:border-r lg:pr-12`}
                        dangerouslySetInnerHTML={{ __html: content }}
                    />
                    <TableOfContents navigation={navigation} />
                </div>
            </div>
        </section>
    );
}
