import AppearanceToggleDropdown from '@/components/appearance/appearance-dropdown';
import { GitHubIcon } from '@/components/icons/github-icon';
import { LinkedInIcon } from '@/components/icons/linkedin-icon';
import AppLogo from '@/components/logo/app-logo';
import { NewsletterSignup } from '@/components/newsletter/newsletter-signup';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function PublicFooter() {
    const { legalPages, auth } = usePage<SharedData>().props;
    const currentYear = new Date().getFullYear();
    const isAuthenticated = auth?.user !== undefined && auth?.user !== null;

    return (
        <footer className="border-t border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <div className="mx-auto max-w-6xl px-4 py-12">
                <div
                    className={`grid grid-cols-1 gap-8 sm:grid-cols-2 ${isAuthenticated ? 'lg:grid-cols-3' : 'lg:grid-cols-[2fr_2fr_2fr_3fr]'}`}
                >
                    {/* Platform Column */}
                    <div>
                        <h3 className="mb-4 text-xs font-semibold tracking-wide text-neutral-900 uppercase dark:text-white">
                            Platform
                        </h3>
                        <ul className="space-y-3">
                            <li>
                                <Link
                                    href="/learn/guides"
                                    className="text-sm text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                >
                                    Learn
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/showcase"
                                    className="text-sm text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                >
                                    Showcases
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href="/showcase/create"
                                    className="text-sm text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                >
                                    Share Your Project
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Terms */}
                    <div>
                        <h3 className="mb-4 text-xs font-semibold tracking-wide text-neutral-900 uppercase dark:text-white">
                            Terms
                        </h3>
                        <ul className="space-y-3">
                            {legalPages.map((page) => (
                                <li key={page.route}>
                                    <Link
                                        href={page.route}
                                        className="text-sm text-neutral-600 transition-colors hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                                    >
                                        {page.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Brand Column */}
                    <div className="order-first space-y-4 sm:order-0 lg:order-first">
                        <AppLogo />
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            Learn. Share. Discover.
                        </p>
                        <div className="flex gap-3">
                            <a
                                href="https://www.linkedin.com/company/vibecode-law"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-primary transition-colors hover:text-primary/80"
                                aria-label="LinkedIn"
                            >
                                <LinkedInIcon className="size-5" />
                            </a>
                            <a
                                href="https://github.com/vibecode-law"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-primary transition-colors hover:text-primary/80"
                                aria-label="GitHub"
                            >
                                <GitHubIcon className="size-5" />
                            </a>
                        </div>
                    </div>

                    {/* Newsletter Column */}
                    {!isAuthenticated && (
                        <div>
                            <h3 className="mb-4 text-xs font-semibold tracking-wide text-neutral-900 uppercase dark:text-white">
                                Stay Updated
                            </h3>
                            <p className="mb-4 text-sm text-neutral-600 dark:text-neutral-400">
                                Keep up to date with the latest vibecode.law
                                news and showcases by signing up to our
                                newsletter.
                            </p>
                            <NewsletterSignup compact />
                        </div>
                    )}
                </div>
            </div>

            {/* Bottom Bar */}
            <div className="border-t border-neutral-200 dark:border-neutral-800">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-4 sm:flex-row">
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        &copy; {currentYear} vibecode.law. All rights reserved.
                        Built for the legal community.
                    </p>
                    <div className="flex items-center gap-4">
                        <AppearanceToggleDropdown />
                    </div>
                </div>
            </div>
        </footer>
    );
}
