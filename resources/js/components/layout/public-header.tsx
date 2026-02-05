import AboutIndexController from '@/actions/App/Http/Controllers/About/AboutIndexController';
import HomeController from '@/actions/App/Http/Controllers/HomeController';
import NewsletterIndexController from '@/actions/App/Http/Controllers/Newsletter/NewsletterIndexController';
import ResourcesIndexController from '@/actions/App/Http/Controllers/Resources/ResourcesIndexController';
import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import WallOfLoveController from '@/actions/App/Http/Controllers/WallOfLove/WallOfLoveController';
import AppLogo from '@/components/logo/app-logo';
import { NewsletterSignup } from '@/components/newsletter/newsletter-signup';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { UserMenuContent } from '@/components/user/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { login } from '@/routes';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, Lock, Menu, Plus, X } from 'lucide-react';

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
                            href={WallOfLoveController.url()}
                            className="text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                        >
                            Wall of Love
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
                            className="w-full border-none bg-white p-0 sm:max-w-md dark:bg-neutral-950 [&>button]:hidden"
                            aria-describedby={undefined}
                        >
                            <SheetHeader className="flex-row items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-800">
                                <SheetTitle className="sr-only">
                                    Navigation menu
                                </SheetTitle>
                                <AppLogo />
                                <SheetClose asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-10 rounded-full bg-neutral-100 text-neutral-900 hover:bg-neutral-200 hover:text-neutral-900 dark:bg-neutral-800 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                                    >
                                        <X className="size-5" />
                                        <span className="sr-only">
                                            Close menu
                                        </span>
                                    </Button>
                                </SheetClose>
                            </SheetHeader>

                            <nav className="flex flex-col justify-between px-6 py-6">
                                <div className="flex flex-col">
                                    <SheetClose asChild>
                                        <Link
                                            href={HomeController.url()}
                                            className="group flex items-center justify-between py-4"
                                        >
                                            <div>
                                                <h3 className="text-2xl font-bold text-neutral-900 group-hover:text-primary dark:text-white">
                                                    Showcases
                                                </h3>
                                                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                    Explore others' projects
                                                </p>
                                            </div>
                                            <ArrowRight className="size-5 text-neutral-400 transition-transform group-hover:translate-x-1 group-hover:text-primary dark:text-neutral-500" />
                                        </Link>
                                    </SheetClose>

                                    <hr className="border-neutral-200 dark:border-neutral-800" />

                                    <SheetClose asChild>
                                        <Link
                                            href={ResourcesIndexController.url()}
                                            className="group flex items-center justify-between py-4"
                                        >
                                            <div>
                                                <h3 className="text-2xl font-bold text-neutral-900 group-hover:text-primary dark:text-white">
                                                    Resources
                                                </h3>
                                                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                    Learn to vibecode.
                                                </p>
                                            </div>
                                            <ArrowRight className="size-5 text-neutral-400 transition-transform group-hover:translate-x-1 group-hover:text-primary dark:text-neutral-500" />
                                        </Link>
                                    </SheetClose>

                                    <hr className="border-neutral-200 dark:border-neutral-800" />

                                    <SheetClose asChild>
                                        <Link
                                            href={ShowcaseCreateController.url()}
                                            className="group flex items-center justify-between py-4"
                                        >
                                            <div>
                                                <h3 className="text-2xl font-bold text-neutral-900 group-hover:text-primary dark:text-white">
                                                    Share Your Project
                                                </h3>
                                                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                    Share your work the
                                                    community.
                                                </p>
                                            </div>
                                            <ArrowRight className="size-5 text-neutral-400 transition-transform group-hover:translate-x-1 group-hover:text-primary dark:text-neutral-500" />
                                        </Link>
                                    </SheetClose>

                                    <hr className="border-neutral-200 dark:border-neutral-800" />

                                    <SheetClose asChild>
                                        <Link
                                            href={WallOfLoveController.url()}
                                            className="group flex items-center justify-between py-4"
                                        >
                                            <div>
                                                <h3 className="text-2xl font-bold text-neutral-900 group-hover:text-primary dark:text-white">
                                                    Wall of Love
                                                </h3>
                                                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                    Community testimonials and press
                                                </p>
                                            </div>
                                            <ArrowRight className="size-5 text-neutral-400 transition-transform group-hover:translate-x-1 group-hover:text-primary dark:text-neutral-500" />
                                        </Link>
                                    </SheetClose>

                                    <hr className="border-neutral-200 dark:border-neutral-800" />

                                    <SheetClose asChild>
                                        <Link
                                            href={AboutIndexController.url()}
                                            className="group flex items-center justify-between py-4"
                                        >
                                            <div>
                                                <h3 className="text-2xl font-bold text-neutral-900 group-hover:text-primary dark:text-white">
                                                    About
                                                </h3>
                                                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                    Find out more about us and
                                                    our community
                                                </p>
                                            </div>
                                            <ArrowRight className="size-5 text-neutral-400 transition-transform group-hover:translate-x-1 group-hover:text-primary dark:text-neutral-500" />
                                        </Link>
                                    </SheetClose>

                                    {!isAuthenticated && (
                                        <>
                                            <hr className="border-neutral-200 dark:border-neutral-800" />

                                            <SheetClose asChild>
                                                <Link
                                                    href={login()}
                                                    className="group flex items-center justify-between py-4"
                                                >
                                                    <div>
                                                        <h3 className="text-2xl font-bold text-neutral-900 group-hover:text-primary dark:text-white">
                                                            Sign In
                                                        </h3>
                                                        <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                                            Access your account
                                                        </p>
                                                    </div>
                                                    <ArrowRight className="size-5 text-neutral-400 transition-transform group-hover:translate-x-1 group-hover:text-primary dark:text-neutral-500" />
                                                </Link>
                                            </SheetClose>
                                        </>
                                    )}
                                </div>
                            </nav>

                            <div className="mt-auto border-t border-neutral-200 px-6 py-6 dark:border-neutral-800">
                                <h3 className="text-2xl font-bold text-neutral-900 dark:text-white">
                                    Subscribe
                                </h3>
                                <p className="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                    Hear about the community designing the
                                    future of law. Get the latest vibecode.law
                                    news and showcases delivered straight to
                                    your inbox.
                                </p>
                                <NewsletterSignup
                                    compact
                                    className="mt-3 max-w-none"
                                />
                            </div>
                        </SheetContent>
                    </Sheet>

                    {/* Subscribe button (desktop, only for non-authenticated users) */}
                    {!isAuthenticated && (
                        <Button
                            size="sm"
                            className="hidden sm:inline-flex"
                            asChild
                        >
                            <Link href={NewsletterIndexController.url()}>
                                Subscribe
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    )}

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
                                <DropdownMenuLabel className="sr-only">
                                    User menu
                                </DropdownMenuLabel>
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
