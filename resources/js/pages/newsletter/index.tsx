import LegalShowController from '@/actions/App/Http/Controllers/Legal/LegalShowController';
import NewsletterSignupController from '@/actions/App/Http/Controllers/Newsletter/NewsletterSignupController';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';
import TextLink from '@/components/ui/text-link';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';
import { type SharedData } from '@/types';
import { Form, Head, usePage } from '@inertiajs/react';
import {
    ArrowRightIcon,
    CheckCircle2Icon,
    LoaderCircleIcon,
} from 'lucide-react';

interface PageProps {
    flash?: {
        newsletter_success?: string;
    };
    [key: string]: unknown;
}

export default function NewsletterIndex() {
    const { name, appUrl } = usePage<SharedData>().props;
    const { flash } = usePage<PageProps>().props;
    const successMessage = flash?.newsletter_success;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: home.url() },
                { label: 'Newsletter' },
            ]}
        >
            <Head title="Newsletter">
                <meta
                    head-key="description"
                    name="description"
                    content="Subscribe to the vibecode.law newsletter for the latest legal tech showcases, tools, and insights."
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`Newsletter | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Subscribe to the vibecode.law newsletter for the latest legal tech showcases, tools, and insights."
                />
            </Head>

            <section className="bg-white py-10 lg:py-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-2xl px-4 text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        Stay in the loop.
                    </h1>

                    <p className="mt-8 text-lg text-neutral-600 dark:text-neutral-400">
                        Hear about the community designing the future of law.
                        Get the latest vibecode.law news and showcases delivered
                        straight to your inbox.
                    </p>

                    <div className="mx-auto mt-8 max-w-sm">
                        {successMessage ? (
                            <Alert className="border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                                <CheckCircle2Icon className="size-4" />
                                <AlertTitle>You're subscribed!</AlertTitle>
                                <AlertDescription className="text-green-700 dark:text-green-300">
                                    {successMessage}
                                </AlertDescription>
                            </Alert>
                        ) : (
                            <>
                                <Form
                                    {...NewsletterSignupController.form()}
                                    resetOnSuccess
                                    options={{ preserveScroll: true }}
                                >
                                    {({ errors, processing }) => (
                                        <div className="flex flex-col gap-4">
                                            <div>
                                                <label
                                                    htmlFor="email"
                                                    className="mb-2 block text-left text-sm font-medium text-neutral-900 dark:text-white"
                                                >
                                                    Email
                                                </label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    name="email"
                                                    placeholder="Enter your email"
                                                    aria-invalid={
                                                        errors.email
                                                            ? true
                                                            : undefined
                                                    }
                                                    className="h-11 bg-white dark:bg-neutral-900"
                                                />
                                                <InputError
                                                    message={errors.email}
                                                    className="mt-1 text-left"
                                                />
                                            </div>

                                            <Button
                                                type="submit"
                                                disabled={processing}
                                                size="lg"
                                                className="w-full"
                                            >
                                                {processing ? (
                                                    <>
                                                        <LoaderCircleIcon className="size-4 animate-spin" />
                                                        Signing up...
                                                    </>
                                                ) : (
                                                    <>
                                                        Sign Me Up
                                                        <ArrowRightIcon className="size-4" />
                                                    </>
                                                )}
                                            </Button>
                                        </div>
                                    )}
                                </Form>

                                <p className="mt-4 text-sm text-neutral-500 dark:text-neutral-500">
                                    No spam. Unsubscribe at any time.
                                </p>

                                <p className="mt-2 text-sm text-neutral-500 dark:text-neutral-500">
                                    Read our{' '}
                                    <TextLink
                                        href={LegalShowController.url(
                                            'privacy-notice',
                                        )}
                                    >
                                        Privacy Notice
                                    </TextLink>
                                    .
                                </p>
                            </>
                        )}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
