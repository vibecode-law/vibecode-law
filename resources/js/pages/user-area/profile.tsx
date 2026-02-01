import EditProfileController from '@/actions/App/Http/Controllers/User/EditProfileController';
import { send } from '@/routes/verification';
import { type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { lazy, Suspense } from 'react';

import HeadingSmall from '@/components/heading/heading-small';
import { AvatarUpload } from '@/components/ui/avatar-upload';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { StatusMessage } from '@/components/ui/status-message';
import DeleteUser from '@/components/user/delete-user';
import UserAreaLayout from '@/layouts/user-area/layout';

const MarkdownEditor = lazy(() =>
    import('@/components/ui/markdown-editor').then((mod) => ({
        default: mod.MarkdownEditor,
    })),
);

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <UserAreaLayout>
            <Head title="Profile settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your personal details and email address"
                />

                <Form
                    {...EditProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="space-y-6"
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <div className="flex justify-center">
                                <AvatarUpload
                                    name="avatar"
                                    currentAvatarUrl={auth.user.avatar}
                                    fallbackName={`${auth.user.first_name} ${auth.user.last_name}`}
                                    allowRemove={true}
                                    error={errors.avatar}
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <FormField
                                    label="First name"
                                    htmlFor="first_name"
                                    error={errors.first_name}
                                >
                                    <Input
                                        id="first_name"
                                        defaultValue={auth.user.first_name}
                                        name="first_name"
                                        required
                                        autoComplete="given-name"
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
                                        defaultValue={auth.user.last_name}
                                        name="last_name"
                                        required
                                        autoComplete="family-name"
                                        placeholder="Last name"
                                    />
                                </FormField>
                            </div>

                            <FormField
                                label="Handle"
                                htmlFor="handle"
                                error={errors.handle}
                            >
                                <Input
                                    id="handle"
                                    defaultValue={auth.user.handle}
                                    name="handle"
                                    required
                                    placeholder="john-doe"
                                />
                            </FormField>

                            <FormField
                                label="Organisation"
                                htmlFor="organisation"
                                error={errors.organisation}
                                optional
                            >
                                <Input
                                    id="organisation"
                                    defaultValue={auth.user.organisation ?? ''}
                                    name="organisation"
                                    autoComplete="organization"
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
                                    defaultValue={auth.user.job_title ?? ''}
                                    name="job_title"
                                    autoComplete="organization-title"
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
                                    defaultValue={auth.user.linkedin_url ?? ''}
                                    name="linkedin_url"
                                    placeholder="https://www.linkedin.com/in/john-doe"
                                />
                            </FormField>

                            <FormField
                                label="Bio"
                                htmlFor="bio"
                                error={errors.bio}
                                optional
                            >
                                <Suspense
                                    fallback={
                                        <div className="h-50 w-full animate-pulse rounded-md bg-muted" />
                                    }
                                >
                                    <MarkdownEditor
                                        name="bio"
                                        defaultValue={auth.user.bio}
                                        placeholder="Tell us about yourself..."
                                    />
                                </Suspense>
                            </FormField>

                            <FormField
                                label="Email address"
                                htmlFor="email"
                                error={errors.email}
                            >
                                <Input
                                    id="email"
                                    type="email"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="Email address"
                                />
                            </FormField>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Click here to resend the
                                                verification email.
                                            </Link>
                                        </p>

                                        <StatusMessage
                                            message={
                                                status ===
                                                'verification-link-sent'
                                                    ? 'A new verification link has been sent to your email address.'
                                                    : undefined
                                            }
                                            className="mt-2 text-left"
                                        />
                                    </div>
                                )}

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="marketing_opt_out"
                                    name="marketing_opt_out"
                                    value="1"
                                    defaultChecked={
                                        auth.user.marketing_opt_out_at !== null
                                    }
                                />
                                <Label
                                    htmlFor="marketing_opt_out"
                                    className="text-sm font-normal text-muted-foreground"
                                >
                                    I do not wish to receive newsletters and
                                    marketing emails
                                </Label>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Save
                                </Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-neutral-600">
                                        Saved
                                    </p>
                                </Transition>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <div className="mt-12">
                <DeleteUser />
            </div>
        </UserAreaLayout>
    );
}
