import NewsletterSignupController from '@/actions/App/Http/Controllers/Newsletter/NewsletterSignupController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';
import { cn } from '@/lib/utils';
import { Form, usePage } from '@inertiajs/react';
import {
    ArrowRightIcon,
    CheckCircle2Icon,
    LoaderCircleIcon,
} from 'lucide-react';

interface NewsletterSignupProps {
    className?: string;
    compact?: boolean;
}

interface PageProps {
    flash?: {
        newsletter_success?: string;
    };
    [key: string]: unknown;
}

export function NewsletterSignup({
    className,
    compact = false,
}: NewsletterSignupProps) {
    const { flash } = usePage<PageProps>().props;
    const successMessage = flash?.newsletter_success;

    if (successMessage) {
        return (
            <div
                className={cn(
                    'flex items-center gap-2 text-sm text-green-600 dark:text-green-400',
                    className,
                )}
            >
                <CheckCircle2Icon className="size-4 shrink-0" />
                <span>{successMessage}</span>
            </div>
        );
    }

    return (
        <Form
            {...NewsletterSignupController.form()}
            resetOnSuccess
            options={{ preserveScroll: true }}
            className={cn('flex w-full max-w-sm gap-2', className)}
        >
            {({ errors, processing }) => (
                <div className="flex w-full flex-col gap-1">
                    <div className="flex">
                        <Input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            aria-label="Email address"
                            aria-invalid={errors.email ? true : undefined}
                            className="h-9 flex-1 rounded-r-none border-r-0 bg-white focus-visible:z-10 dark:bg-neutral-900"
                        />
                        <Button
                            type="submit"
                            disabled={processing}
                            size={compact ? 'icon' : 'default'}
                            className={cn(
                                'shrink-0 rounded-l-none',
                                compact && 'size-9',
                            )}
                            aria-label={compact ? 'Subscribe' : undefined}
                        >
                            {compact ? (
                                processing ? (
                                    <LoaderCircleIcon className="size-4 animate-spin" />
                                ) : (
                                    <ArrowRightIcon className="size-4" />
                                )
                            ) : processing ? (
                                'Signing up...'
                            ) : (
                                'Subscribe'
                            )}
                        </Button>
                    </div>
                    <InputError message={errors.email} />
                </div>
            )}
        </Form>
    );
}
