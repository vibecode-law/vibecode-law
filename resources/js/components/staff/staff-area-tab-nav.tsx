import { TabNav, type TabNavItem } from '@/components/navigation/tab-nav';
import { usePermissions } from '@/hooks/use-permissions';
import { index as challengesIndex } from '@/routes/staff/challenges';
import { index as coursesIndex } from '@/routes/staff/courses';
import { index as practiceAreasIndex } from '@/routes/staff/practice-areas';
import { index as pressCoverageIndex } from '@/routes/staff/press-coverage';
import { index as showcaseModerationIndex } from '@/routes/staff/showcase-moderation';
import { index as testimonialsIndex } from '@/routes/staff/testimonials';
import { index as usersIndex } from '@/routes/staff/users';
import { useMemo } from 'react';

interface StaffNavItem extends TabNavItem {
    permission?: string;
    adminOnly?: boolean;
}

const allStaffAreaNavItems: StaffNavItem[] = [
    {
        title: 'Showcase Moderation',
        href: showcaseModerationIndex().url,
    },
    {
        title: 'Practice Areas',
        href: practiceAreasIndex().url,
        permission: 'practice-area.view',
    },
    {
        title: 'Testimonials',
        href: testimonialsIndex().url,
        permission: 'testimonial.view',
    },
    {
        title: 'Press Coverage',
        href: pressCoverageIndex().url,
        permission: 'press-coverage.view',
    },
    {
        title: 'Courses',
        href: coursesIndex().url,
        adminOnly: true,
    },
    {
        title: 'Challenges',
        href: challengesIndex().url,
        adminOnly: true,
    },
    {
        title: 'Users',
        href: usersIndex().url,
        adminOnly: true,
    },
];

export function useStaffAreaNavItems(): TabNavItem[] {
    const { hasPermission, isAdmin } = usePermissions();

    return useMemo(
        () =>
            allStaffAreaNavItems.filter((item) => {
                if (item.adminOnly === true) {
                    return isAdmin;
                }

                if (item.permission === undefined) {
                    return true;
                }

                return hasPermission(item.permission);
            }),
        [hasPermission, isAdmin],
    );
}

export function StaffAreaTabNav() {
    const items = useStaffAreaNavItems();

    return <TabNav items={items} ariaLabel="Staff Area" />;
}
