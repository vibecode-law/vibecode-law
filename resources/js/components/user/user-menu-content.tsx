import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user/user-info';
import { Appearance, useAppearance } from '@/hooks/use-appearance';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { usePermissions } from '@/hooks/use-permissions';
import { logout } from '@/routes';
import { index as staffIndex } from '@/routes/staff';
import { edit } from '@/routes/user-area/profile';
import { index as myShowcases } from '@/routes/user-area/showcases';
import { Link, router } from '@inertiajs/react';
import {
    Images,
    LogOut,
    Monitor,
    Moon,
    Shield,
    SquareUser,
    Sun,
} from 'lucide-react';

interface UserMenuContentProps {
    user: App.Http.Resources.User.PrivateUserResource;
}

const appearanceOptions: {
    value: Appearance;
    icon: typeof Sun;
    label: string;
}[] = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
];

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();
    const { appearance, updateAppearance } = useAppearance();
    const { hasPermission } = usePermissions();

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    const currentIcon =
        appearance === 'dark' ? Moon : appearance === 'light' ? Sun : Monitor;
    const canAccessStaffArea = hasPermission('staff.access');

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={myShowcases.url()}
                        prefetch
                        onClick={cleanup}
                    >
                        <Images className="mr-2" />
                        My Showcases
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={edit()}
                        prefetch
                        onClick={cleanup}
                    >
                        <SquareUser className="mr-2" />
                        Profile
                    </Link>
                </DropdownMenuItem>
                {canAccessStaffArea === true && (
                    <>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link
                                className="block w-full cursor-pointer"
                                href={staffIndex.url()}
                                prefetch
                                onClick={cleanup}
                            >
                                <Shield className="mr-2" />
                                Staff Area
                            </Link>
                        </DropdownMenuItem>
                    </>
                )}
                <DropdownMenuSub>
                    <DropdownMenuSubTrigger>
                        {(() => {
                            const Icon = currentIcon;
                            return <Icon className="mr-2" />;
                        })()}
                        Appearance
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
                        {appearanceOptions.map(
                            ({ value, icon: Icon, label }) => (
                                <DropdownMenuItem
                                    key={value}
                                    onClick={() => updateAppearance(value)}
                                >
                                    <Icon className="mr-2" />
                                    {label}
                                    {appearance === value && (
                                        <span className="ml-auto text-xs">
                                            ✓
                                        </span>
                                    )}
                                </DropdownMenuItem>
                            ),
                        )}
                    </DropdownMenuSubContent>
                </DropdownMenuSub>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer"
                    href={logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    Log out
                </Link>
            </DropdownMenuItem>
        </>
    );
}
