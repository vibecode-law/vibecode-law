import Heading from '@/components/heading/heading';
import { TabNav, type TabNavItem } from '@/components/navigation/tab-nav';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { type PropsWithChildren } from 'react';

interface TabNavLayoutProps extends PropsWithChildren {
    title: string;
    items: TabNavItem[];
    ariaLabel: string;
    fullWidth?: boolean;
}

export default function TabNavLayout({
    title,
    items,
    ariaLabel,
    children,
    fullWidth = false,
}: TabNavLayoutProps) {
    return (
        <PublicLayout>
            <div className="bg-white py-8 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <Heading title={title} />

                    <TabNav items={items} ariaLabel={ariaLabel} />

                    <div
                        className={cn(
                            'mt-8',
                            fullWidth === true
                                ? 'mx-auto max-w-5xl'
                                : 'max-w-2xl',
                        )}
                    >
                        {children}
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}
