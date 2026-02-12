import HomeController from '@/actions/App/Http/Controllers/HomeController';
import PublicProfileController from '@/actions/App/Http/Controllers/User/PublicProfileController';
import { LinkedInIcon } from '@/components/icons/linkedin-icon';
import { ProjectItem } from '@/components/showcase/showcase-item';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import PublicLayout from '@/layouts/public-layout';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface UserShowProps {
    user: App.Http.Resources.User.UserResource;
    showcases: App.Http.Resources.Showcase.ShowcaseResource[];
}

export default function UserShow({ user, showcases }: UserShowProps) {
    const { appUrl, transformImages } = usePage<SharedData>().props;

    const fullName = `${user.first_name} ${user.last_name}`;
    const initials = `${user.first_name.charAt(0)}${user.last_name.charAt(0)}`;

    const jobInfo =
        user.job_title !== null && user.organisation !== null
            ? `${user.job_title} at ${user.organisation}`
            : (user.job_title ?? user.organisation);

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: HomeController.url() },
                { label: 'Profiles' },
                { label: fullName },
            ]}
        >
            <Head title={fullName}>
                <meta
                    head-key="description"
                    name="description"
                    content={`View ${fullName}'s profile and showcases.`}
                />
                <meta head-key="og-type" property="og:type" content="profile" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={fullName}
                />
                {user.avatar !== null && (
                    <meta
                        head-key="og-image"
                        property="og:image"
                        content={user.avatar}
                    />
                )}
                <meta
                    head-key="og-description"
                    property="og:description"
                    content={`View ${fullName}'s profile and showcases.`}
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${PublicProfileController.url(user.handle)}`}
                />
            </Head>

            <section className="bg-white py-12 dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl px-4">
                    <div className="mb-8 flex items-start gap-6">
                        <Avatar className="size-24 shrink-0">
                            <AvatarImage
                                src={
                                    user.avatar !== null
                                        ? transformImages === true
                                            ? `${user.avatar}?w=160`
                                            : user.avatar
                                        : undefined
                                }
                                alt={fullName}
                            />
                            <AvatarFallback className="text-2xl">
                                {initials}
                            </AvatarFallback>
                        </Avatar>
                        <div className="space-y-3">
                            <div>
                                <h1 className="text-2xl font-bold text-neutral-900 dark:text-white">
                                    {fullName}
                                </h1>
                                {jobInfo !== null && (
                                    <p className="text-neutral-500 dark:text-neutral-400">
                                        {jobInfo}
                                    </p>
                                )}
                            </div>
                            {user.bio_html !== null &&
                                user.bio_html !== undefined && (
                                    <div
                                        className="rich-text-content"
                                        dangerouslySetInnerHTML={{
                                            __html: user.bio_html,
                                        }}
                                    />
                                )}
                            {user.linkedin_url !== null && (
                                <Badge
                                    variant="outline"
                                    asChild
                                    className="cursor-pointer px-3 py-1.5 text-sm"
                                >
                                    <a
                                        href={user.linkedin_url}
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <LinkedInIcon className="size-4" />
                                        LinkedIn
                                    </a>
                                </Badge>
                            )}
                        </div>
                    </div>
                    <div className="border-t border-neutral-100 pt-8 dark:border-neutral-800">
                        <h2 className="mb-4 text-xl font-semibold text-neutral-900 dark:text-white">
                            Showcases
                        </h2>
                        {showcases.length > 0 ? (
                            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {showcases.map((showcase, idx) => (
                                    <ProjectItem
                                        key={showcase.id}
                                        showcase={showcase}
                                        rank={idx + 1}
                                    />
                                ))}
                            </div>
                        ) : (
                            <div className="py-8 text-center">
                                <p className="text-neutral-500 dark:text-neutral-400">
                                    No showcases yet.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
