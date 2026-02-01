import { AvatarUpload } from '@/components/ui/avatar-upload';
import { Checkbox } from '@/components/ui/checkbox';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { Mail, Shield, Users } from 'lucide-react';

interface TeamType {
    value: number;
    label: string;
}

interface UserFormFieldsProps {
    roles: string[];
    teamTypes: TeamType[];
    processing: boolean;
    errors: Record<string, string>;
    defaultValues?: {
        first_name?: string;
        last_name?: string;
        handle?: string;
        email?: string;
        organisation?: string | null;
        job_title?: string | null;
        linkedin_url?: string | null;
        bio?: string | null;
        team_type?: number | null;
        team_role?: string | null;
        roles?: string[];
        avatar?: string | null;
        marketing_opt_out?: boolean;
    };
    mode: 'create' | 'edit';
}

export default function UserFormFields({
    roles,
    teamTypes,
    processing,
    errors,
    defaultValues,
    mode,
}: UserFormFieldsProps) {
    const isCreate = mode === 'create';

    const fallbackName =
        defaultValues?.first_name && defaultValues?.last_name
            ? `${defaultValues.first_name} ${defaultValues.last_name}`
            : 'User';

    return (
        <div className="space-y-6">
            <div className="flex justify-center">
                <AvatarUpload
                    name="avatar"
                    currentAvatarUrl={defaultValues?.avatar}
                    fallbackName={fallbackName}
                    allowRemove={isCreate === false}
                    error={errors.avatar}
                />
            </div>

            <Separator />

            <div className="grid gap-4 sm:grid-cols-2">
                <FormField
                    label="First name"
                    htmlFor="first_name"
                    error={errors.first_name}
                >
                    <Input
                        id="first_name"
                        name="first_name"
                        defaultValue={defaultValues?.first_name}
                        disabled={processing}
                        aria-invalid={
                            errors.first_name !== undefined ? true : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Last name"
                    htmlFor="last_name"
                    error={errors.last_name}
                >
                    <Input
                        id="last_name"
                        name="last_name"
                        defaultValue={defaultValues?.last_name}
                        disabled={processing}
                        aria-invalid={
                            errors.last_name !== undefined ? true : undefined
                        }
                    />
                </FormField>
            </div>

            <FormField
                label="Handle"
                htmlFor="handle"
                error={errors.handle}
                optional={isCreate}
            >
                <Input
                    id="handle"
                    name="handle"
                    defaultValue={defaultValues?.handle}
                    disabled={processing}
                    placeholder={
                        isCreate === true
                            ? 'john-doe (auto-generated if empty)'
                            : 'john-doe'
                    }
                    aria-invalid={
                        errors.handle !== undefined ? true : undefined
                    }
                />
            </FormField>

            <FormField label="Email" htmlFor="email" error={errors.email}>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    defaultValue={defaultValues?.email}
                    disabled={processing}
                    aria-invalid={errors.email !== undefined ? true : undefined}
                />
            </FormField>

            <div className="grid gap-4 sm:grid-cols-2">
                <FormField
                    label="Organisation"
                    htmlFor="organisation"
                    error={errors.organisation}
                    optional={isCreate}
                >
                    <Input
                        id="organisation"
                        name="organisation"
                        defaultValue={defaultValues?.organisation ?? ''}
                        disabled={processing}
                        aria-invalid={
                            errors.organisation !== undefined ? true : undefined
                        }
                    />
                </FormField>

                <FormField
                    label="Job title"
                    htmlFor="job_title"
                    error={errors.job_title}
                    optional={isCreate}
                >
                    <Input
                        id="job_title"
                        name="job_title"
                        defaultValue={defaultValues?.job_title ?? ''}
                        disabled={processing}
                        aria-invalid={
                            errors.job_title !== undefined ? true : undefined
                        }
                    />
                </FormField>
            </div>

            <FormField
                label="LinkedIn URL"
                htmlFor="linkedin_url"
                error={errors.linkedin_url}
                optional={isCreate}
            >
                <Input
                    id="linkedin_url"
                    name="linkedin_url"
                    type="url"
                    defaultValue={defaultValues?.linkedin_url ?? ''}
                    placeholder="https://linkedin.com/in/..."
                    disabled={processing}
                    aria-invalid={
                        errors.linkedin_url !== undefined ? true : undefined
                    }
                />
            </FormField>

            <FormField
                label="Bio"
                htmlFor="bio"
                error={errors.bio}
                optional={isCreate}
            >
                <Textarea
                    id="bio"
                    name="bio"
                    rows={4}
                    defaultValue={defaultValues?.bio ?? ''}
                    disabled={processing}
                    aria-invalid={errors.bio !== undefined ? true : undefined}
                />
            </FormField>

            <Separator />

            <div className="space-y-4">
                <div className="flex items-center gap-2">
                    <Users className="size-4 text-neutral-500" />
                    <Label className="text-base font-medium">
                        Team Membership{' '}
                        {isCreate === true && (
                            <span className="text-neutral-400">(optional)</span>
                        )}
                    </Label>
                </div>
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    Add this user to the team page.
                </p>

                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField
                        label="Team Type"
                        htmlFor="team_type"
                        error={errors.team_type}
                    >
                        <select
                            id="team_type"
                            name="team_type"
                            defaultValue={defaultValues?.team_type ?? ''}
                            disabled={processing}
                            aria-invalid={
                                errors.team_type !== undefined
                                    ? true
                                    : undefined
                            }
                            className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40"
                        >
                            <option value="">Not a team member</option>
                            {teamTypes.map((type) => (
                                <option key={type.value} value={type.value}>
                                    {type.label}
                                </option>
                            ))}
                        </select>
                    </FormField>

                    <FormField
                        label="Team Role"
                        htmlFor="team_role"
                        error={errors.team_role}
                    >
                        <Input
                            id="team_role"
                            name="team_role"
                            defaultValue={defaultValues?.team_role ?? ''}
                            placeholder="e.g. Lead Developer"
                            disabled={processing}
                            aria-invalid={
                                errors.team_role !== undefined
                                    ? true
                                    : undefined
                            }
                        />
                    </FormField>
                </div>
            </div>

            {roles.length > 0 && (
                <>
                    <Separator />

                    <div className="space-y-4">
                        <div className="flex items-center gap-2">
                            <Shield className="size-4 text-neutral-500" />
                            <Label className="text-base font-medium">
                                Roles{' '}
                                {isCreate === true && (
                                    <span className="text-neutral-400">
                                        (optional)
                                    </span>
                                )}
                            </Label>
                        </div>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Assign roles to control user permissions.
                            {isCreate === false &&
                                ' Admin status is managed separately.'}
                        </p>
                        <div className="space-y-2">
                            {roles.map((role) => (
                                <label
                                    key={role}
                                    className="flex cursor-pointer items-center gap-3 rounded-md border p-3 hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-800/50"
                                >
                                    <Checkbox
                                        name="roles[]"
                                        value={role}
                                        defaultChecked={defaultValues?.roles?.includes(
                                            role,
                                        )}
                                        disabled={processing}
                                    />
                                    <span className="font-medium">{role}</span>
                                </label>
                            ))}
                        </div>
                    </div>
                </>
            )}

            <Separator />

            <div className="space-y-4">
                <div className="flex items-center gap-2">
                    <Mail className="size-4 text-neutral-500" />
                    <Label className="text-base font-medium">
                        Marketing Preferences
                    </Label>
                </div>
                <label className="flex cursor-pointer items-center gap-3 rounded-md border p-3 hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-800/50">
                    <Checkbox
                        name="marketing_opt_out"
                        value="1"
                        defaultChecked={defaultValues?.marketing_opt_out}
                        disabled={processing}
                    />
                    <div>
                        <span className="font-medium">
                            Opt out of marketing emails
                        </span>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            User will not receive marketing newsletters.
                        </p>
                    </div>
                </label>
            </div>
        </div>
    );
}
