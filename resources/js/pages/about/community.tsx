import AboutIndexController from '@/actions/App/Http/Controllers/About/AboutIndexController';
import CommunityController from '@/actions/App/Http/Controllers/About/CommunityController';
import { CommunityMemberCard } from '@/components/community/community-member-card';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface CommunityProps {
    title: string;
    coreTeam: App.Http.Resources.User.UserResource[];
    collaborators: App.Http.Resources.User.UserResource[];
}

export default function Community({
    title,
    coreTeam,
    collaborators,
}: CommunityProps) {
    const { name, appUrl } = usePage<SharedData>().props;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'About', href: AboutIndexController.url() },
                { label: title },
            ]}
        >
            <Head title={title}>
                <meta
                    head-key="description"
                    name="description"
                    content="Meet the community that makes vibecode.law possible."
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
                    content={`${appUrl}${CommunityController.url()}`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Meet the community that makes vibecode.law possible."
                />
            </Head>

            <section className="bg-white py-12 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <h1 className="mb-2 text-3xl font-bold text-neutral-900 dark:text-white">
                        {title}
                    </h1>
                    <p className="mb-10 text-neutral-600 dark:text-neutral-400">
                        Meet the community that makes vibecode.law possible.
                    </p>

                    {coreTeam.length > 0 && (
                        <div className="mb-12">
                            <h2 className="mb-5 text-xl font-semibold text-neutral-900 dark:text-white">
                                Core Team
                            </h2>
                            <p className="mb-5 text-sm text-neutral-500 dark:text-neutral-400">
                                The people leading the charge on building our
                                community.
                            </p>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {coreTeam.map((user) => (
                                    <CommunityMemberCard
                                        key={user.handle}
                                        user={user}
                                    />
                                ))}
                            </div>
                        </div>
                    )}

                    {collaborators.length > 0 && (
                        <div className="mb-12">
                            <h2 className="mb-5 text-xl font-semibold text-neutral-900 dark:text-white">
                                Collaborators
                            </h2>
                            <p className="mb-5 text-sm text-neutral-500 dark:text-neutral-400">
                                The people that keep things running, whether
                                that is by moderating, maintaining the codebase,
                                or otherwise.
                            </p>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {collaborators.map((user) => (
                                    <CommunityMemberCard
                                        key={user.handle}
                                        user={user}
                                    />
                                ))}
                            </div>
                        </div>
                    )}

                    {coreTeam.length === 0 && collaborators.length === 0 && (
                        <div className="py-12 text-center">
                            <p className="text-neutral-500 dark:text-neutral-400">
                                No community members to display yet.
                            </p>
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
