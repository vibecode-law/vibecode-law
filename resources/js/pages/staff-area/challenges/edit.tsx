import ChallengeFormFields from '@/components/challenges/challenge-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, update } from '@/routes/staff/challenges';
import { index as inviteCodesIndex } from '@/routes/staff/challenges/invite-codes';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, KeyRound } from 'lucide-react';

interface ChallengesEditProps {
    challenge: App.Http.Resources.Challenge.ChallengeResource;
    inviteCodesCount: number;
    visibilityOptions: App.ValueObjects.FrontendEnum[];
}

export default function ChallengesEdit({
    challenge,
    inviteCodesCount,
    visibilityOptions,
}: ChallengesEditProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title={`Edit ${challenge.title}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to challenges
                        </Link>
                    </Button>
                </div>

                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title={`Edit ${challenge.title}`}
                        description="Update challenge details and settings"
                    />
                    <div className="flex items-center gap-2">
                        {challenge.is_active === true && (
                            <Badge className="bg-green-500 text-white hover:bg-green-500">
                                Active
                            </Badge>
                        )}
                        {challenge.is_featured === true && (
                            <Badge className="bg-amber-500 text-white hover:bg-amber-500">
                                Featured
                            </Badge>
                        )}
                    </div>
                </div>

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={update.url({ challenge: challenge.slug })}
                        method="patch"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <ChallengeFormFields
                                    processing={processing}
                                    errors={errors}
                                    visibilityOptions={visibilityOptions}
                                    mode="edit"
                                    defaultValues={{
                                        title: challenge.title,
                                        slug: challenge.slug,
                                        tagline: challenge.tagline,
                                        description: challenge.description,
                                        starts_at: challenge.starts_at,
                                        ends_at: challenge.ends_at,
                                        is_active: challenge.is_active,
                                        is_featured: challenge.is_featured,
                                        visibility: challenge.visibility,
                                        organisation:
                                            challenge.organisation ?? null,
                                        thumbnail_url:
                                            challenge.thumbnail_url ?? null,
                                        thumbnail_crops:
                                            challenge.thumbnail_crops ?? null,
                                    }}
                                />

                                <div className="mt-6 flex items-center border-t pt-6 dark:border-neutral-800">
                                    {challenge.visibility !== undefined &&
                                        challenge.visibility !== 1 && (
                                            <Button
                                                variant="outline"
                                                type="button"
                                                asChild
                                            >
                                                <Link
                                                    href={inviteCodesIndex.url({
                                                        challenge:
                                                            challenge.slug,
                                                    })}
                                                >
                                                    <KeyRound className="mr-1.5 size-4" />
                                                    Manage Invite Codes
                                                    {inviteCodesCount > 0 && (
                                                        <Badge
                                                            variant="secondary"
                                                            className="ml-1.5"
                                                        >
                                                            {inviteCodesCount}
                                                        </Badge>
                                                    )}
                                                </Link>
                                            </Button>
                                        )}
                                    <div className="ml-auto flex items-center gap-3">
                                        <Button
                                            variant="outline"
                                            type="button"
                                            asChild
                                        >
                                            <Link href={index.url()}>
                                                Cancel
                                            </Link>
                                        </Button>
                                        <SubmitButton
                                            processing={processing}
                                            processingLabel="Saving..."
                                        >
                                            Save changes
                                        </SubmitButton>
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
