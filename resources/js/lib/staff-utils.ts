export interface StaffSectionAccess {
    permission?: string;
    adminOnly?: boolean;
}

/**
 * Determine whether the current user can access a staff section based on its
 * permission/admin requirements.
 */
export function canAccessStaffSection(
    item: StaffSectionAccess,
    isAdmin: boolean,
    hasPermission: (permission: string) => boolean,
): boolean {
    if (item.adminOnly === true) {
        return isAdmin;
    }

    if (item.permission === undefined) {
        return true;
    }

    return hasPermission(item.permission);
}
