import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Ban, Check, MoreVertical, Pencil, Star, Trash2 } from 'lucide-react';
import { type ReactNode } from 'react';

interface UserListItemProps {
    user: App.Http.Resources.User.AdminUserResource;
    onEdit: () => void;
    onDelete: () => void;
    onToggleSubmissions: () => void;
    renderPasswordResetButton: () => ReactNode;
}

export function UserListItem({
    user,
    onEdit,
    onDelete,
    onToggleSubmissions,
    renderPasswordResetButton,
}: UserListItemProps) {
    const { transformImages } = usePage<SharedData>().props;
    const initials = `${user.first_name[0]}${user.last_name[0]}`.toUpperCase();
    const isBlocked = user.blocked_from_submissions_at !== null;

    return (
        <div className="flex items-center gap-4 py-4">
            <Avatar className="size-10">
                {user.avatar !== null && (
                    <AvatarImage
                        src={
                            transformImages === true
                                ? `${user.avatar}?w=100`
                                : user.avatar
                        }
                    />
                )}
                <AvatarFallback>{initials}</AvatarFallback>
            </Avatar>

            <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        {user.first_name} {user.last_name}
                    </h3>
                    {user.is_superadmin === true && (
                        <Badge className="gap-1 bg-amber-500 text-white hover:bg-amber-500">
                            <Star className="size-3" />
                            Admin
                        </Badge>
                    )}
                    {user.roles.length > 0 &&
                        user.roles.map((role) => (
                            <Badge
                                key={role}
                                className="bg-blue-500 text-white hover:bg-blue-500"
                            >
                                {role}
                            </Badge>
                        ))}
                    {isBlocked === true && (
                        <Badge variant="destructive" className="gap-1">
                            <Ban className="size-3" />
                            Blocked
                        </Badge>
                    )}
                </div>
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    {user.email}
                    {user.organisation !== null && (
                        <span className="ml-2 text-neutral-400 dark:text-neutral-500">
                            &middot; {user.organisation}
                        </span>
                    )}
                </p>
                <p className="mt-0.5 text-xs text-neutral-400 dark:text-neutral-500">
                    {user.showcases_count !== undefined && (
                        <span>
                            {user.showcases_count}{' '}
                            {user.showcases_count === 1
                                ? 'showcase'
                                : 'showcases'}
                        </span>
                    )}
                    {user.showcases_count !== undefined && (
                        <span className="mx-1.5">&middot;</span>
                    )}
                    <span>
                        Joined{' '}
                        {new Date(user.created_at).toLocaleDateString(
                            undefined,
                            {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                            },
                        )}
                    </span>
                </p>
            </div>

            <div className="flex shrink-0 items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={onEdit}
                    className="gap-1.5"
                >
                    <Pencil className="size-4" />
                    Edit
                </Button>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm">
                            <MoreVertical className="size-4" />
                            <span className="sr-only">More actions</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={onToggleSubmissions}>
                            {isBlocked === true ? (
                                <>
                                    <Check className="mr-2 size-4" />
                                    Unblock from submissions
                                </>
                            ) : (
                                <>
                                    <Ban className="mr-2 size-4" />
                                    Block from submissions
                                </>
                            )}
                        </DropdownMenuItem>
                        {renderPasswordResetButton()}
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            onClick={onDelete}
                            className="text-red-600 focus:text-red-600 dark:text-red-400 dark:focus:text-red-400"
                        >
                            <Trash2 className="mr-2 size-4" />
                            Delete user
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    );
}
