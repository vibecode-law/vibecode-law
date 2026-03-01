import AcceptInviteCodeController from '@/actions/App/Http/Controllers/Challenge/AcceptInviteCodeController';
import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import {
    ListCard,
    ListCardContent,
    ListCardEmpty,
    ListCardHeader,
    ListCardTitle,
} from '@/components/ui/list-card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { edit } from '@/routes/staff/challenges';
import { store, toggle } from '@/routes/staff/challenges/invite-codes';
import { type SharedData } from '@/types';
import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Check, Copy, X } from 'lucide-react';
import { useState } from 'react';

interface InviteCodesIndexProps {
    challenge: Pick<
        App.Http.Resources.Challenge.ChallengeResource,
        'id' | 'slug' | 'title' | 'visibility'
    >;
    inviteCodes: App.Http.Resources.Challenge.ChallengeInviteCodeResource[];
    scopeOptions: App.ValueObjects.FrontendEnum[];
}

function CopyButton({ text }: { text: string }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(text);
        } catch {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={() => void handleCopy()}
            className="h-7 gap-1 px-2"
        >
            {copied === true ? (
                <Check className="size-3.5 text-green-500" />
            ) : (
                <Copy className="size-3.5" />
            )}
            {copied === true ? 'Copied' : 'Copy'}
        </Button>
    );
}

export default function InviteCodesIndex({
    challenge,
    inviteCodes,
    scopeOptions,
}: InviteCodesIndexProps) {
    const { appUrl } = usePage<SharedData>().props;
    const [scope, setScope] = useState('2');

    const getInviteUrl = (code: string) =>
        `${appUrl}${AcceptInviteCodeController.url({ code })}`;

    const handleToggle = (
        inviteCode: App.Http.Resources.Challenge.ChallengeInviteCodeResource,
    ) => {
        router.post(
            toggle.url({
                challenge: challenge.slug,
                inviteCode: inviteCode.id,
            }),
            {},
            { preserveScroll: true },
        );
    };

    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Invite Codes - ${challenge.title}`} />

            <div className="space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link
                            href={edit.url({
                                challenge: challenge.slug,
                            })}
                        >
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to {challenge.title}
                        </Link>
                    </Button>
                </div>

                <HeadingSmall
                    title={`Invite Codes for ${challenge.title}`}
                    description="Manage invite codes for this challenge"
                />

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h3 className="mb-4 text-sm font-medium">
                        Create Invite Code
                    </h3>
                    <Form
                        {...store.form({ challenge: challenge.slug })}
                        className="flex items-start gap-3"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="flex-1">
                                    <FormField
                                        label="Label"
                                        htmlFor="label"
                                        error={errors.label}
                                    >
                                        <Input
                                            id="label"
                                            name="label"
                                            placeholder="e.g. Stanford Law, Twitter promo"
                                            disabled={processing}
                                            aria-invalid={
                                                errors.label !== undefined
                                                    ? true
                                                    : undefined
                                            }
                                        />
                                    </FormField>
                                </div>
                                <div className="w-48">
                                    <FormField
                                        label="Scope"
                                        htmlFor="scope"
                                        error={errors.scope}
                                    >
                                        <Select
                                            value={scope}
                                            onValueChange={setScope}
                                            disabled={processing}
                                        >
                                            <SelectTrigger
                                                id="scope"
                                                aria-invalid={
                                                    errors.scope !== undefined
                                                        ? true
                                                        : undefined
                                                }
                                            >
                                                <SelectValue placeholder="Select scope" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {scopeOptions.map((option) => (
                                                    <SelectItem
                                                        key={option.value}
                                                        value={String(
                                                            option.value,
                                                        )}
                                                    >
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <input
                                            type="hidden"
                                            name="scope"
                                            value={scope}
                                        />
                                    </FormField>
                                </div>
                                <div className="pt-6">
                                    <SubmitButton processing={processing}>
                                        Create Code
                                    </SubmitButton>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Invite Codes</ListCardTitle>
                        <Badge variant="secondary">
                            {inviteCodes.length}{' '}
                            {inviteCodes.length === 1 ? 'code' : 'codes'}
                        </Badge>
                    </ListCardHeader>

                    {inviteCodes.length > 0 ? (
                        <ListCardContent>
                            <div className="divide-y divide-neutral-100 dark:divide-neutral-800">
                                {inviteCodes.map((inviteCode) => (
                                    <div
                                        key={inviteCode.id}
                                        className="flex items-center gap-4 py-4"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm font-medium text-neutral-900 dark:text-white">
                                                    {inviteCode.label}
                                                </span>
                                                <Badge
                                                    variant="outline"
                                                    className="text-xs"
                                                >
                                                    {scopeOptions.find(
                                                        (o) =>
                                                            o.value ===
                                                            String(
                                                                inviteCode.scope,
                                                            ),
                                                    )?.label ?? 'Unknown'}
                                                </Badge>
                                                {inviteCode.users_count !==
                                                    undefined &&
                                                    inviteCode.users_count !==
                                                        null && (
                                                        <span className="text-xs text-neutral-500 dark:text-neutral-400">
                                                            {
                                                                inviteCode.users_count
                                                            }{' '}
                                                            {inviteCode.users_count ===
                                                            1
                                                                ? 'user'
                                                                : 'users'}
                                                        </span>
                                                    )}
                                            </div>
                                            <div className="mt-1 flex items-center gap-2">
                                                <span className="truncate text-xs text-neutral-500 dark:text-neutral-400">
                                                    {getInviteUrl(
                                                        inviteCode.code,
                                                    )}
                                                </span>
                                                <CopyButton
                                                    text={getInviteUrl(
                                                        inviteCode.code,
                                                    )}
                                                />
                                            </div>
                                        </div>

                                        <div className="flex shrink-0 items-center gap-2">
                                            {inviteCode.is_active === true ? (
                                                <Badge className="bg-green-500 text-white hover:bg-green-500">
                                                    Active
                                                </Badge>
                                            ) : (
                                                <Badge className="bg-red-500 text-white hover:bg-red-500">
                                                    Disabled
                                                </Badge>
                                            )}
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    handleToggle(inviteCode)
                                                }
                                            >
                                                {inviteCode.is_active ===
                                                true ? (
                                                    <>
                                                        <X className="size-4" />
                                                        Disable
                                                    </>
                                                ) : (
                                                    <>
                                                        <Check className="size-4" />
                                                        Enable
                                                    </>
                                                )}
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </ListCardContent>
                    ) : (
                        <ListCardEmpty>
                            No invite codes have been created yet.
                        </ListCardEmpty>
                    )}
                </ListCard>
            </div>
        </StaffAreaLayout>
    );
}
