import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import UserFormFields from '@/components/user/admin/user-form-fields';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, update } from '@/routes/staff/users';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, Ban, Star } from 'lucide-react';

interface TeamType {
    value: number;
    label: string;
}

interface UsersEditProps {
    user: App.Http.Resources.User.AdminUserResource;
    roles: string[];
    teamTypes: TeamType[];
}

export default function UsersEdit({ user, roles, teamTypes }: UsersEditProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Edit ${user.first_name} ${user.last_name}`} />

            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to users
                        </Link>
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title={`Edit ${user.first_name} ${user.last_name}`}
                        description="Update user details and roles"
                    />
                    <div className="flex items-center gap-2">
                        {user.is_admin === true && (
                            <Badge className="gap-1 bg-amber-500 text-white hover:bg-amber-600">
                                <Star className="size-3" />
                                Admin
                            </Badge>
                        )}
                        {user.blocked_from_submissions_at !== null && (
                            <Badge variant="destructive" className="gap-1">
                                <Ban className="size-3" />
                                Blocked
                            </Badge>
                        )}
                    </div>
                </div>

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={update.url({ user: user.handle })}
                        method="patch"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <UserFormFields
                                    roles={roles}
                                    teamTypes={teamTypes}
                                    processing={processing}
                                    errors={errors}
                                    mode="edit"
                                    defaultValues={{
                                        first_name: user.first_name,
                                        last_name: user.last_name,
                                        handle: user.handle,
                                        email: user.email,
                                        organisation: user.organisation,
                                        job_title: user.job_title,
                                        linkedin_url: user.linkedin_url,
                                        bio: user.bio,
                                        team_type: user.team_type,
                                        team_role: user.team_role,
                                        roles: user.roles,
                                        avatar: user.avatar,
                                        marketing_opt_out:
                                            user.marketing_opt_out_at !== null,
                                    }}
                                />

                                <div className="mt-6 flex items-center justify-between border-t pt-6 dark:border-neutral-800">
                                    <div className="text-sm text-neutral-500 dark:text-neutral-400">
                                        {user.showcases_count !== undefined && (
                                            <span>
                                                {user.showcases_count}{' '}
                                                {user.showcases_count === 1
                                                    ? 'showcase'
                                                    : 'showcases'}
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex gap-3">
                                        <Button
                                            variant="outline"
                                            type="button"
                                            asChild
                                        >
                                            <Link href={index.url()}>
                                                Cancel
                                            </Link>
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Saving...'
                                                : 'Save changes'}
                                        </Button>
                                    </div>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </StaffAreaLayout>
    );
}
