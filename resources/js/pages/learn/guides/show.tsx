import GuideShowController from '@/actions/App/Http/Controllers/Learn/GuideShowController';
import LearnIndexController from '@/actions/App/Http/Controllers/Learn/LearnIndexController';
import { ContentLayout } from '@/components/content/content-layout';
import { type NavigationItem } from '@/components/content/table-of-contents';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface GuidesShowProps {
    title: string;
    slug: string;
    content: string;
    navigation: NavigationItem[];
}

export default function GuidesShow({
    title,
    slug,
    content,
    navigation,
}: GuidesShowProps) {
    const { name, appUrl, defaultMetaDescription } =
        usePage<SharedData>().props;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Learn', href: LearnIndexController.url() },
                { label: title },
            ]}
        >
            <Head title={title}>
                <meta
                    head-key="description"
                    name="description"
                    content={defaultMetaDescription}
                />
                <meta head-key="og-type" property="og:type" content="article" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`${title} | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${GuideShowController.url(slug)}`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={defaultMetaDescription}
                />
            </Head>

            <ContentLayout content={content} navigation={navigation} />
        </PublicLayout>
    );
}
