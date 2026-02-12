import HeadingSmall from '@/components/heading/heading-small';
import { Button } from '@/components/ui/button';
import { InfoBox } from '@/components/ui/info-box';
import { SubmitButton } from '@/components/ui/submit-button';
import UserFormFields from '@/components/user/admin/user-form-fields';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, store } from '@/routes/staff/users';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, Mail } from 'lucide-react';

interface TeamType {
    value: number;
    label: string;
}

interface UsersCreateProps {
    roles: string[];
    teamTypes: TeamType[];
}

export default function UsersCreate({ roles, teamTypes }: UsersCreateProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title="Create User" />

            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to users
                        </Link>
                    </Button>
                </div>

                <HeadingSmall
                    title="Create User"
                    description="Create a new user account. They will receive an email invitation to set their password."
                />

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={store.url()}
                        method="post"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <InfoBox
                                    variant="info"
                                    icon={<Mail className="size-4" />}
                                    className="mb-6"
                                >
                                    The user will receive an email with
                                    instructions to set their password and
                                    update their profile.
                                </InfoBox>

                                <UserFormFields
                                    roles={roles}
                                    teamTypes={teamTypes}
                                    processing={processing}
                                    errors={errors}
                                    mode="create"
                                />

                                <div className="mt-6 flex items-center justify-end gap-3 border-t pt-6 dark:border-neutral-800">
                                    <Button
                                        variant="outline"
                                        type="button"
                                        asChild
                                    >
                                        <Link href={index.url()}>Cancel</Link>
                                    </Button>
                                    <SubmitButton
                                        processing={processing}
                                        processingLabel="Creating..."
                                    >
                                        Create User
                                    </SubmitButton>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </StaffAreaLayout>
    );
}
