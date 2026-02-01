import CompleteProfileController from '@/actions/App/Http/Controllers/Auth/CompleteProfileController';
import LegalShowController from '@/actions/App/Http/Controllers/Legal/LegalShowController';
import { Checkbox } from '@/components/ui/checkbox';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SubmitButton } from '@/components/ui/submit-button';
import TextLink from '@/components/ui/text-link';
import { Textarea } from '@/components/ui/textarea';
import AuthLayout from '@/layouts/auth-layout';
import { Form, Head, Link } from '@inertiajs/react';

interface Props {
    intended?: string;
}

export default function CompleteProfile({ intended }: Props) {
    return (
        <AuthLayout
            title="Complete your profile"
            description="Add some additional details to your profile. You can skip this and update later."
            showLinkedinLogin={false}
        >
            <Head title="Complete your profile" />

            <Form
                {...CompleteProfileController.store.form()}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        {intended && (
                            <input
                                type="hidden"
                                name="intended"
                                value={intended}
                            />
                        )}
                        <div className="grid gap-6">
                            <FormField
                                label="Job title"
                                htmlFor="job_title"
                                error={errors.job_title}
                                optional
                            >
                                <Input
                                    id="job_title"
                                    type="text"
                                    tabIndex={1}
                                    autoFocus
                                    autoComplete="organization-title"
                                    name="job_title"
                                    placeholder="Job title"
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
                                    type="text"
                                    tabIndex={2}
                                    autoComplete="organization"
                                    name="organisation"
                                    placeholder="Organisation"
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
                                    tabIndex={3}
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
                                <Textarea
                                    id="bio"
                                    name="bio"
                                    tabIndex={4}
                                    placeholder="Tell us about yourself..."
                                    rows={4}
                                />
                            </FormField>

                            <div className="flex items-center space-x-3">
                                <Checkbox
                                    id="marketing_opt_out"
                                    name="marketing_opt_out"
                                    value="1"
                                    tabIndex={5}
                                />
                                <Label
                                    htmlFor="marketing_opt_out"
                                    className="text-sm font-normal text-muted-foreground"
                                >
                                    I do not wish to receive newsletters and
                                    marketing emails
                                </Label>
                            </div>

                            <div className="mt-2 flex gap-3">
                                <Link
                                    href={CompleteProfileController.skip()}
                                    method="post"
                                    as="button"
                                    data={{ intended }}
                                    tabIndex={7}
                                    className="inline-flex h-9 flex-1 items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-xs transition-[color,box-shadow] hover:bg-accent hover:text-accent-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                >
                                    Skip
                                </Link>
                                <SubmitButton
                                    className="flex-1"
                                    tabIndex={6}
                                    processing={processing}
                                >
                                    Save
                                </SubmitButton>
                            </div>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            <p>
                                By logging in, you agree to our{' '}
                                <TextLink
                                    href={LegalShowController.url(
                                        'terms-of-service',
                                    )}
                                >
                                    Terms of Service
                                </TextLink>{' '}
                                and acknowledge our{' '}
                                <TextLink
                                    href={LegalShowController.url(
                                        'privacy-notice',
                                    )}
                                >
                                    Privacy Notice
                                </TextLink>
                                .
                            </p>
                        </div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
