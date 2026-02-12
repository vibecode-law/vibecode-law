import { PublicFooter } from '@/components/layout/public-footer';
import { PublicHeader } from '@/components/layout/public-header';
import {
    Breadcrumbs,
    type BreadcrumbItem,
} from '@/components/navigation/breadcrumbs';
import { SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { type PropsWithChildren, type ReactNode } from 'react';

interface PublicLayoutProps extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
    breadcrumbActions?: ReactNode;
}

export default function PublicLayout({
    children,
    breadcrumbs,
    breadcrumbActions,
}: PublicLayoutProps) {
    const { name, appUrl, defaultMetaDescription } =
        usePage<SharedData>().props;

    return (
        <div className="flex min-h-svh flex-col bg-white dark:bg-neutral-950">
            <Head title="Home">
                <meta
                    head-key="description"
                    name="description"
                    content={defaultMetaDescription}
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta head-key="og-title" property="og:title" content={name} />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={defaultMetaDescription}
                />
            </Head>

            <PublicHeader />
            <main className="flex-1">
                {breadcrumbs !== undefined && breadcrumbs.length > 0 && (
                    <div className="mx-auto max-w-6xl px-4 pt-8">
                        <div className="-mb-3 flex flex-col gap-y-4 md:flex-row md:items-center md:justify-between">
                            <Breadcrumbs items={breadcrumbs} />
                            {breadcrumbActions}
                        </div>
                    </div>
                )}
                {children}
            </main>
            <PublicFooter />
        </div>
    );
}
