import AboutIndexController from '@/actions/App/Http/Controllers/About/AboutIndexController';
import ResourcesIndexController from '@/actions/App/Http/Controllers/Resources/ResourcesIndexController';
import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import AppLogo from '@/components/logo/app-logo';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { UserMenuContent } from '@/components/user/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { login } from '@/routes';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { VisuallyHidden } from '@radix-ui/react-visually-hidden';
import { Lock, Menu, Plus } from 'lucide-react';

export function PublicHeader() {
    const page = usePage<SharedData>();
    const { auth, transformImages } = page.props;
    const getInitials = useInitials();
    const isAuthenticated = auth?.user !== undefined && auth?.user !== null;

    return (
        <header className="border-b border-neutral-100 bg-white dark:border-neutral-900 dark:bg-neutral-950">
            <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
                <AppLogo />

                <nav className="flex items-center gap-2 lg:gap-4">
                    {/* Desktop navigation */}
                    <div className="hidden items-center gap-6 sm:flex">
                        <Link
                            href={ResourcesIndexController.url()}
                            className="text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                        >
                            Resources
                        </Link>
                        <Link
                            href={AboutIndexController.url()}
                            className="text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                        >
                            About
                        </Link>
                    </div>

                    {/* Desktop actions */}
                    <div className="hidden items-center gap-4 sm:flex">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={ShowcaseCreateController.url()}>
                                <Plus className="size-4" />
                                Share Project
                            </Link>
                        </Button>
                    </div>

                    {/* Mobile hamburger menu */}
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="sm"
                                className="sm:hidden"
                            >
                                <Menu className="size-5" />
                                <span className="sr-only">Open menu</span>
                            </Button>
                        </SheetTrigger>
                        <SheetContent
                            side="right"
                            className="w-72 dark:bg-neutral-950"
                            aria-describedby={undefined}
                        >
                            <VisuallyHidden>
                                <SheetTitle>Navigation menu</SheetTitle>
                            </VisuallyHidden>
                            <nav className="flex flex-col gap-4 p-4">
                                <SheetClose asChild>
                                    <Link
                                        href={ResourcesIndexController.url()}
                                        className="text-base font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                    >
                                        Resources
                                    </Link>
                                </SheetClose>
                                <SheetClose asChild>
                                    <Link
                                        href={AboutIndexController.url()}
                                        className="text-base font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                    >
                                        About
                                    </Link>
                                </SheetClose>
                                <hr className="border-neutral-200 dark:border-neutral-800" />
                                <SheetClose asChild>
                                    <Link
                                        href={ShowcaseCreateController.url()}
                                        className="text-base font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                    >
                                        Submit Project
                                    </Link>
                                </SheetClose>
                            </nav>
                        </SheetContent>
                    </Sheet>

                    {/* User avatar or login button (all screens) */}
                    {isAuthenticated ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="size-9 rounded-full p-0"
                                >
                                    <Avatar className="size-8">
                                        <AvatarImage
                                            src={
                                                auth.user.avatar !== null
                                                    ? transformImages === true
                                                        ? `${auth.user.avatar}?w=100`
                                                        : auth.user.avatar
                                                    : undefined
                                            }
                                            alt={`${auth.user.first_name} ${auth.user.last_name}`}
                                        />
                                        <AvatarFallback className="bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white">
                                            {getInitials(
                                                `${auth.user.first_name} ${auth.user.last_name}`,
                                            )}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align="end">
                                <UserMenuContent user={auth.user} />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-9"
                            asChild
                        >
                            <Link href={login()}>
                                <Lock className="size-5" />
                                <span className="sr-only">Sign in</span>
                            </Link>
                        </Button>
                    )}
                </nav>
            </div>
        </header>
    );
}
