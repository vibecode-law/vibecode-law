import LegalShowController from '@/actions/App/Http/Controllers/Legal/LegalShowController';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/react';

import { AvatarUpload } from '@/components/ui/avatar-upload';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input-error';
import { SubmitButton } from '@/components/ui/submit-button';
import TextLink from '@/components/ui/text-link';
import AuthLayout from '@/layouts/auth-layout';

export default function Register() {
    return (
        <AuthLayout
            title="Create an account"
            description={
                <>
                    By registering, you agree to our{' '}
                    <TextLink
                        href={LegalShowController.url('terms-of-service')}
                    >
                        Terms of Service
                    </TextLink>{' '}
                    and acknowledge our{' '}
                    <TextLink href={LegalShowController.url('privacy-notice')}>
                        Privacy Notice
                    </TextLink>
                    .
                </>
            }
        >
            <Head title="Register" />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="flex justify-center">
                                <div className="grid gap-2">
                                    <AvatarUpload
                                        name="avatar"
                                        fallbackName="Your Avatar"
                                    />
                                    <InputError
                                        message={errors.avatar}
                                        className="text-center"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <FormField
                                    label="First name"
                                    htmlFor="first_name"
                                    error={errors.first_name}
                                >
                                    <Input
                                        id="first_name"
                                        type="text"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="given-name"
                                        name="first_name"
                                        placeholder="First name"
                                    />
                                </FormField>

                                <FormField
                                    label="Last name"
                                    htmlFor="last_name"
                                    error={errors.last_name}
                                >
                                    <Input
                                        id="last_name"
                                        type="text"
                                        required
                                        tabIndex={2}
                                        autoComplete="family-name"
                                        name="last_name"
                                        placeholder="Last name"
                                    />
                                </FormField>
                            </div>

                            <FormField
                                label="Organisation"
                                htmlFor="organisation"
                                error={errors.organisation}
                                optional
                            >
                                <Input
                                    id="organisation"
                                    type="text"
                                    tabIndex={3}
                                    autoComplete="organization"
                                    name="organisation"
                                    placeholder="Organisation"
                                />
                            </FormField>

                            <FormField
                                label="Job title"
                                htmlFor="job_title"
                                error={errors.job_title}
                                optional
                            >
                                <Input
                                    id="job_title"
                                    type="text"
                                    tabIndex={4}
                                    autoComplete="organization-title"
                                    name="job_title"
                                    placeholder="Job title"
                                />
                            </FormField>

                            <FormField
                                label="LinkedIn URL"
                                htmlFor="linkedin_url"
                                error={errors.linkedin_url}
                                optional
                            >
                                <Input
                                    id="linkedin_url"
                                    type="url"
                                    tabIndex={5}
                                    name="linkedin_url"
                                    placeholder="https://www.linkedin.com/in/john-doe"
                                />
                            </FormField>

                            <FormField
                                label="Email address"
                                htmlFor="email"
                                error={errors.email}
                            >
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={6}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                            </FormField>

                            <FormField
                                label="Password"
                                htmlFor="password"
                                error={errors.password}
                            >
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={7}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Password"
                                />
                            </FormField>

                            <FormField
                                label="Confirm password"
                                htmlFor="password_confirmation"
                                error={errors.password_confirmation}
                            >
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    tabIndex={8}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                />
                            </FormField>

                            <SubmitButton
                                className="mt-2 w-full"
                                tabIndex={9}
                                processing={processing}
                                data-test="register-user-button"
                            >
                                Create account
                            </SubmitButton>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Already have an account?{' '}
                            <TextLink href={login()} tabIndex={10}>
                                Log in
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
