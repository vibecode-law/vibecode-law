import { TabNav, type TabNavItem } from '@/components/navigation/tab-nav';
import { useActiveUrl } from '@/hooks/use-active-url';
import { usePermissions } from '@/hooks/use-permissions';
import { index as coursesIndex } from '@/routes/staff/academy/courses';
import { index as challengesIndex } from '@/routes/staff/challenges';
import { index as practiceAreasIndex } from '@/routes/staff/metadata/practice-areas';
import { index as pressCoverageIndex } from '@/routes/staff/press-coverage';
import { index as showcaseModerationIndex } from '@/routes/staff/showcase-moderation';
import { index as testimonialsIndex } from '@/routes/staff/testimonials';
import { index as usersIndex } from '@/routes/staff/users';
import { useMemo } from 'react';

interface StaffNavItem extends TabNavItem {
    permission?: string;
    adminOnly?: boolean;
}

export function useStaffAreaNavItems(): TabNavItem[] {
    const { hasPermission, isAdmin } = usePermissions();
    const { currentUrl } = useActiveUrl();

    return useMemo(() => {
        const allStaffAreaNavItems: StaffNavItem[] = [
            {
                title: 'Showcase Moderation',
                href: showcaseModerationIndex().url,
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
                title: 'Metadata',
                href: practiceAreasIndex().url,
                isActive: currentUrl.startsWith('/staff/metadata'),
                adminOnly: true,
            },
            {
                title: 'Academy',
                href: coursesIndex().url,
                isActive: currentUrl.startsWith('/staff/academy'),
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

        return allStaffAreaNavItems.filter((item) => {
            if (item.adminOnly === true) {
                return isAdmin;
            }

            if (item.permission === undefined) {
                return true;
            }

            return hasPermission(item.permission);
        });
    }, [hasPermission, isAdmin, currentUrl]);
}

export function StaffAreaTabNav() {
    const items = useStaffAreaNavItems();

    return <TabNav items={items} ariaLabel="Staff Area" />;
}
