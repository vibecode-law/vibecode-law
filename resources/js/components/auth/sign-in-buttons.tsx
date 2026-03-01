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
import TextLink from '@/components/ui/text-link';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { Form } from '@inertiajs/react';
import { Mail } from 'lucide-react';

interface SignInButtonsProps {
    description: string;
    idPrefix?: string;
}

export function SignInButtons({
    description,
    idPrefix = 'sign-in',
}: SignInButtonsProps) {
    return (
        <div className="mt-2 flex flex-col items-center gap-3 sm:flex-row">
            <a
                href={LinkedinAuthRedirectController.url()}
                className="inline-block cursor-pointer hover:brightness-90"
            >
                <img
                    src="/static/sign-in-with-linkedin.png"
                    alt="Sign in with LinkedIn"
                />
            </a>

            <Dialog>
                <DialogTrigger asChild>
                    <Button
                        variant="outline"
                        className="border-neutral-700 bg-neutral-800 text-white hover:bg-neutral-700 hover:text-white"
                    >
                        <Mail className="size-4" />
                        Sign in with email
                    </Button>
                </DialogTrigger>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Sign in with email</DialogTitle>
                        <DialogDescription>{description}</DialogDescription>
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
                                    htmlFor={`${idPrefix}-email`}
                                    error={errors.email}
                                >
                                    <Input
                                        id={`${idPrefix}-email`}
                                        type="email"
                                        name="email"
                                        required
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                    />
                                </FormField>
                                <FormField
                                    label="Password"
                                    htmlFor={`${idPrefix}-password`}
                                    error={errors.password}
                                >
                                    <Input
                                        id={`${idPrefix}-password`}
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
                                    Sign in
                                </SubmitButton>
                                <p className="text-center text-sm text-muted-foreground">
                                    Don&apos;t have an account?{' '}
                                    <TextLink href={register.url()}>
                                        Sign up
                                    </TextLink>
                                </p>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    );
}
