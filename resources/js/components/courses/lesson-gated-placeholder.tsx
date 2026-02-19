import LinkedinAuthRedirectController from '@/actions/App/Http/Controllers/Auth/LinkedinAuthRedirectController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { SubmitButton } from '@/components/ui/submit-button';
import { store } from '@/routes/login';
import { Form } from '@inertiajs/react';
import { Lock, Mail } from 'lucide-react';

interface LessonGatedPlaceholderProps {
    thumbnailUrl?: string | null;
}

export function LessonGatedPlaceholder({
    thumbnailUrl,
}: LessonGatedPlaceholderProps) {
    return (
        <div className="relative mb-8 overflow-hidden rounded-xl bg-neutral-900">
            {thumbnailUrl && (
                <img
                    src={thumbnailUrl}
                    alt=""
                    className="absolute inset-0 size-full object-cover opacity-20 blur-sm"
                />
            )}
            <div className="relative flex aspect-video w-full flex-col items-center justify-center gap-4 text-white">
                <div className="rounded-full bg-neutral-800 p-4">
                    <Lock className="size-8 text-neutral-400" />
                </div>
                <h3 className="text-xl font-semibold">Log in to watch</h3>
                <p className="max-w-sm text-center text-sm text-neutral-400">
                    Sign in to access this lesson and track your progress.
                </p>
                <div className="mt-2 flex flex-col items-center gap-3 sm:flex-row">
                    <a
                        href={LinkedinAuthRedirectController.url()}
                        className="inline-block cursor-pointer hover:brightness-90"
                    >
                        <img
                            src="/static/sign-in-with-linkedin.png"
                            alt="Log in with LinkedIn"
                        />
                    </a>

                    <Dialog>
                        <DialogTrigger asChild>
                            <Button
                                variant="outline"
                                className="border-neutral-700 bg-neutral-800 text-white hover:bg-neutral-700 hover:text-white"
                            >
                                <Mail className="size-4" />
                                Log in with email
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="sm:max-w-md">
                            <DialogHeader>
                                <DialogTitle>Log in with email</DialogTitle>
                                <DialogDescription>
                                    Enter your email and password to access this
                                    lesson.
                                </DialogDescription>
                            </DialogHeader>
                            <Form
                                {...store.form()}
                                resetOnSuccess={['password']}
                                className="flex flex-col gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <FormField
                                            label="Email address"
                                            htmlFor="gated-email"
                                            error={errors.email}
                                        >
                                            <Input
                                                id="gated-email"
                                                type="email"
                                                name="email"
                                                required
                                                autoComplete="email"
                                                placeholder="email@example.com"
                                            />
                                        </FormField>
                                        <FormField
                                            label="Password"
                                            htmlFor="gated-password"
                                            error={errors.password}
                                        >
                                            <Input
                                                id="gated-password"
                                                type="password"
                                                name="password"
                                                required
                                                autoComplete="current-password"
                                                placeholder="Password"
                                            />
                                        </FormField>
                                        <SubmitButton
                                            className="w-full"
                                            processing={processing}
                                        >
                                            Log in
                                        </SubmitButton>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </div>
    );
}
