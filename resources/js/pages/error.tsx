import { home } from '@/routes';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CodeXml, RotateCw } from 'lucide-react';

interface ErrorPageProps {
    status: number;
}

const titles: Record<number, string> = {
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Forbidden',
};

const descriptions: Record<number, string> = {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
};

export default function ErrorPage({ status }: ErrorPageProps) {
    const title = titles[status] ?? 'Error';
    const description = descriptions[status] ?? 'An unexpected error occurred.';

    const handleGoBack = () => {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            router.visit(home());
        }
    };

    const handleRefresh = () => {
        window.location.reload();
    };

    return (
        <div className="flex min-h-svh flex-col bg-white dark:bg-neutral-950">
            <Head title={title} />

            <main className="flex flex-1 flex-col items-center justify-center px-4">
                <Link
                    href={home()}
                    className="mb-12 flex cursor-pointer items-center gap-2 transition-opacity hover:opacity-80"
                >
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <CodeXml className="h-6 w-6" aria-hidden="true" />
                    </div>
                    <span className="font-heading text-2xl font-bold tracking-tight">
                        vibecode
                        <span className="text-muted-foreground">.law</span>
                    </span>
                </Link>

                <p className="text-7xl font-bold text-neutral-200 dark:text-neutral-800">
                    {status}
                </p>
                <h1 className="mt-4 text-2xl font-bold tracking-tight text-neutral-900 sm:text-3xl dark:text-white">
                    {title}
                </h1>
                <p className="mt-2 max-w-md text-center text-neutral-600 dark:text-neutral-300">
                    {description}
                </p>

                <div className="mt-12 flex items-center gap-3">
                    <button
                        type="button"
                        onClick={handleGoBack}
                        className="inline-flex items-center gap-2 rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition-colors hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Go back
                    </button>
                    <button
                        type="button"
                        onClick={handleRefresh}
                        className="inline-flex items-center gap-2 rounded-md bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-100"
                    >
                        <RotateCw className="h-4 w-4" />
                        Refresh
                    </button>
                </div>
            </main>
        </div>
    );
}
