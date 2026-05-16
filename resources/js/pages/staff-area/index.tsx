import HeadingSmall from '@/components/heading/heading-small';
import { usePermissions } from '@/hooks/use-permissions';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import {
    canAccessStaffSection,
    type StaffSectionAccess,
} from '@/lib/staff-utils';
import { index as coursesIndex } from '@/routes/staff/academy/courses';
import { index as challengesIndex } from '@/routes/staff/challenges';
import { index as practiceAreasIndex } from '@/routes/staff/metadata/practice-areas';
import { index as pressCoverageIndex } from '@/routes/staff/press-coverage';
import { index as settingsIndex } from '@/routes/staff/settings';
import { index as showcaseModerationIndex } from '@/routes/staff/showcase-moderation';
import { index as testimonialsIndex } from '@/routes/staff/testimonials';
import { index as usersIndex } from '@/routes/staff/users';
import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';

interface StaffSection extends StaffSectionAccess {
    title: string;
    description: string;
    href: string;
}

export default function StaffIndex() {
    const { hasPermission, isAdmin } = usePermissions();

    const sections = useMemo<StaffSection[]>(() => {
        const all: StaffSection[] = [
            {
                title: 'Showcase Moderation',
                description: 'Review and moderate showcase submissions.',
                href: showcaseModerationIndex().url,
                permission: 'showcase.approve-reject',
            },
            {
                title: 'Testimonials',
                description: 'Manage testimonials displayed on the site.',
                href: testimonialsIndex().url,
                permission: 'testimonial.view',
            },
            {
                title: 'Press Coverage',
                description: 'Manage press coverage entries.',
                href: pressCoverageIndex().url,
                permission: 'press-coverage.view',
            },
            {
                title: 'Academy',
                description: 'Manage courses and lessons.',
                href: coursesIndex().url,
                permission: 'course.view',
            },
            {
                title: 'Challenges',
                description: 'Manage challenges and invite codes.',
                href: challengesIndex().url,
                permission: 'challenge.view',
            },
            {
                title: 'Metadata',
                description: 'Manage practice areas and tags.',
                href: practiceAreasIndex().url,
                adminOnly: true,
            },
            {
                title: 'Users',
                description: 'Manage user accounts and roles.',
                href: usersIndex().url,
                adminOnly: true,
            },
            {
                title: 'Settings',
                description: 'Manage site-wide settings.',
                href: settingsIndex().url,
                adminOnly: true,
            },
        ];

        return all.filter((item) =>
            canAccessStaffSection(item, isAdmin, hasPermission),
        );
    }, [hasPermission, isAdmin]);

    return (
        <StaffAreaLayout fullWidth>
            <Head title="Staff Area" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Staff Area"
                    description="Choose a section to manage."
                />

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {sections.map((section) => (
                        <Link
                            key={section.href}
                            href={section.href}
                            className="block rounded-lg border border-neutral-200 bg-white p-5 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-neutral-700 dark:hover:bg-neutral-800"
                        >
                            <h3 className="font-semibold">{section.title}</h3>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {section.description}
                            </p>
                        </Link>
                    ))}
                </div>
            </div>
        </StaffAreaLayout>
    );
}
