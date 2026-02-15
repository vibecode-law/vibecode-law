import HomeController from '@/actions/App/Http/Controllers/HomeController';
import GuideShowController from '@/actions/App/Http/Controllers/Learn/GuideShowController';
import HowItWorksController from '@/actions/App/Http/Controllers/Showcase/Help/HowItWorksController';
import ShowcaseCreateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseCreateController';
import ShowcaseIndexController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseIndexController';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    CircleCheckBig,
    CircleHelp,
    PlayCircle,
    Share2,
    Upload,
} from 'lucide-react';

interface Step {
    number: number;
    title: string;
    description: string;
    icon: React.ReactNode;
    iconBg: string;
    iconColor: string;
}

interface Faq {
    question: string;
    answer: string;
}

const steps: Step[] = [
    {
        number: 1,
        title: 'Submit',
        description:
            "It's free and takes 2 minutes. Just share your project details.",
        icon: <Upload className="size-6" />,
        iconBg: 'bg-sky-100 dark:bg-sky-950/50 border border-sky-200 dark:border-sky-800',
        iconColor: 'text-sky-600 dark:text-sky-400',
    },
    {
        number: 2,
        title: 'Approve',
        description:
            'We approve your submission manually (just to avoid spam).',
        icon: <CircleCheckBig className="size-6" />,
        iconBg: 'bg-emerald-100 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800',
        iconColor: 'text-emerald-600 dark:text-emerald-400',
    },
    {
        number: 3,
        title: 'Share',
        description:
            'Share your creation on LinkedIn and watch the upvotes roll in.',
        icon: <Share2 className="size-6" />,
        iconBg: 'bg-violet-100 dark:bg-violet-950/50 border border-violet-200 dark:border-violet-800',
        iconColor: 'text-violet-600 dark:text-violet-400',
    },
];

const faqs: Faq[] = [
    {
        question: 'Does it cost anything?',
        answer: "No, it's free. The whole platform is free and open source.",
    },
    {
        question: 'What happens to my intellectual property?',
        answer: "This is completely up to you. Please share as much or as little as you like. We don't require projects to be open source. We don't take any licence beyond as is necessary to display the content you provide.",
    },
    {
        question: 'How do I register?',
        answer: 'You can create an account with LinkedIn or email/password.',
    },
    {
        question: 'Is there a prize?',
        answer: "Right now it's all about bragging rights. Maybe a digital trophy. If you have ideas to prizes we are all ears!",
    },
    {
        question: 'Does it matter if my app is released?',
        answer: 'Only to make sure they are not spam and align with Community Guidelines. Do your own due diligence.',
    },
    {
        question: 'How does upvoting work?',
        answer: "Just register and click the upvote button. You have unlimited upvotes (one per project) so let's spread the encouragement!",
    },
    {
        question: 'How can I get in touch?',
        answer: 'Contact <a href="mailto:hello@vibecode.law">hello@vibecode.law</a>. Keep in mind we are all volunteers so response times may vary.',
    },
];

export default function HowItWorks() {
    const { name, appUrl } = usePage<SharedData>().props;

    return (
        <PublicLayout
            breadcrumbs={[
                { label: 'Home', href: HomeController.url() },
                { label: 'Showcases', href: ShowcaseIndexController.url() },
                { label: 'How It Works' },
            ]}
        >
            <Head title="How It Works">
                <meta
                    head-key="description"
                    name="description"
                    content="Learn how to submit your legaltech project to vibecode.law. It's free, quick, and easy."
                />
                <meta head-key="og-type" property="og:type" content="website" />
                <meta
                    head-key="og-title"
                    property="og:title"
                    content={`How It Works | ${name}`}
                />
                <meta
                    head-key="og-image"
                    property="og:image"
                    content={`${appUrl}/static/og-text-logo.png`}
                />
                <meta
                    head-key="og-description"
                    property="og:description"
                    content="Learn how to submit your legaltech project to vibecode.law. It's free, quick, and easy."
                />
                <meta
                    head-key="og-url"
                    property="og:url"
                    content={`${appUrl}${HowItWorksController.url()}`}
                />
            </Head>

            {/* Hero Section */}
            <section className="bg-white py-16 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4 text-center">
                    <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl dark:text-white">
                        How it works
                    </h1>

                    <p className="mt-4 text-lg text-neutral-500 dark:text-neutral-400">
                        Posting your first showcase is easy
                    </p>
                </div>
            </section>

            {/* Steps Section */}
            <section className="bg-white pb-12 dark:bg-neutral-950">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="grid gap-6 md:grid-cols-3">
                        {steps.map((step) => (
                            <div
                                key={step.number}
                                className="rounded-xl border border-neutral-200 bg-white p-6 text-center dark:border-neutral-800 dark:bg-neutral-900"
                            >
                                <div
                                    className={cn(
                                        'mx-auto mb-5 flex size-14 items-center justify-center rounded-xl',
                                        step.iconBg,
                                    )}
                                >
                                    <span className={step.iconColor}>
                                        {step.icon}
                                    </span>
                                </div>
                                <h3 className="mb-2 text-lg font-semibold text-neutral-900 dark:text-white">
                                    {step.number}. {step.title}
                                </h3>
                                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                    {step.description}
                                </p>
                            </div>
                        ))}
                    </div>
                    <p className="mt-4 flex flex-col items-center justify-center gap-2 text-sm text-neutral-600 md:flex-row md:text-base dark:text-neutral-400">
                        <span className="flex items-center justify-start gap-2">
                            <PlayCircle className="size-5" />
                            Don&apos;t have a project yet?{' '}
                        </span>
                        <Link
                            href={GuideShowController.url('start-vibecoding')}
                            className="font-bold underline underline-offset-4 transition-colors hover:text-neutral-900 dark:hover:text-white"
                        >
                            Make a demo app in three minutes.
                        </Link>
                    </p>
                </div>
            </section>

            {/* CTA Section */}
            <section className="bg-white pb-12 dark:bg-neutral-950">
                <div className="mx-auto max-w-4xl px-4 text-center">
                    <Button asChild size="xl">
                        <Link href={ShowcaseCreateController.url()}>
                            Submit Your Project
                        </Link>
                    </Button>
                </div>
            </section>

            {/* FAQs Section */}
            <section className="bg-white pb-20 dark:bg-neutral-950">
                <div className="mx-auto max-w-5xl px-4">
                    <div className="mb-6 flex items-center gap-3 border-b border-neutral-200 pb-4 dark:border-neutral-800">
                        <div className="flex size-9 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/50">
                            <CircleHelp className="size-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h2 className="text-xl font-semibold text-neutral-900 dark:text-white">
                            FAQs
                        </h2>
                    </div>

                    <div className="space-y-6">
                        {faqs.map((faq, index) => (
                            <div key={index}>
                                <h3 className="font-semibold text-neutral-900 dark:text-white">
                                    {faq.question}
                                </h3>
                                <p
                                    className="mt-1 text-neutral-600 dark:text-neutral-400 [&_a]:font-medium [&_a]:text-neutral-900 [&_a]:underline [&_a]:underline-offset-4 hover:[&_a]:text-neutral-700 dark:[&_a]:text-white dark:hover:[&_a]:text-neutral-300"
                                    dangerouslySetInnerHTML={{
                                        __html: faq.answer,
                                    }}
                                />
                            </div>
                        ))}
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
