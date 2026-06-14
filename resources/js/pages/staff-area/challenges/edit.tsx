import ChallengeFormFields from '@/components/challenges/challenge-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { CHALLENGE_VISIBILITY } from '@/lib/challenge-utils';
import { index, update } from '@/routes/staff/challenges';
import { index as inviteCodesIndex } from '@/routes/staff/challenges/invite-codes';
import { index as partnerLogosIndex } from '@/routes/staff/challenges/partner-logos';
import { index as subChallengesIndex } from '@/routes/staff/challenges/sub-challenges';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, ImagePlus, KeyRound, ListChecks } from 'lucide-react';

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

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <HeadingSmall
                        title={`Edit ${challenge.title}`}
                        description="Update challenge details and settings"
                    />
                    <div className="flex flex-wrap items-center gap-2">
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
                                        involvement_instructions:
                                            challenge.involvement_instructions,
                                        participant_instructions:
                                            challenge.participant_instructions,
                                        starts_at: challenge.starts_at,
                                        ends_at: challenge.ends_at,
                                        is_active: challenge.is_active,
                                        is_featured: challenge.is_featured,
                                        live_view_enabled:
                                            challenge.live_view_enabled,
                                        live_view_access_token:
                                            challenge.live_view_access_token,
                                        live_view_heading:
                                            challenge.live_view_heading,
                                        live_view_subheading:
                                            challenge.live_view_subheading,
                                        visibility: challenge.visibility,
                                        organisation:
                                            challenge.organisation ?? null,
                                        thumbnail_url:
                                            challenge.thumbnail_url ?? null,
                                        thumbnail_crops:
                                            challenge.thumbnail_crops ?? null,
                                    }}
                                />

                                <div className="mt-6 flex flex-col gap-4 border-t pt-6 sm:flex-row sm:items-center dark:border-neutral-800">
                                    <div className="flex flex-wrap items-center gap-3">
                                        <Button
                                            variant="outline"
                                            type="button"
                                            asChild
                                        >
                                            <Link
                                                href={subChallengesIndex.url({
                                                    challenge: challenge.slug,
                                                })}
                                            >
                                                <ListChecks className="mr-1.5 size-4" />
                                                Manage Sub-challenges
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="outline"
                                            type="button"
                                            asChild
                                        >
                                            <Link
                                                href={partnerLogosIndex.url({
                                                    challenge: challenge.slug,
                                                })}
                                            >
                                                <ImagePlus className="mr-1.5 size-4" />
                                                Manage Partner Logos
                                            </Link>
                                        </Button>
                                        {challenge.visibility !== undefined &&
                                            challenge.visibility !==
                                                CHALLENGE_VISIBILITY.Public && (
                                                <Button
                                                    variant="outline"
                                                    type="button"
                                                    asChild
                                                >
                                                    <Link
                                                        href={inviteCodesIndex.url(
                                                            {
                                                                challenge:
                                                                    challenge.slug,
                                                            },
                                                        )}
                                                    >
                                                        <KeyRound className="mr-1.5 size-4" />
                                                        Manage Invite Codes
                                                        {inviteCodesCount >
                                                            0 && (
                                                            <Badge
                                                                variant="secondary"
                                                                className="ml-1.5"
                                                            >
                                                                {
                                                                    inviteCodesCount
                                                                }
                                                            </Badge>
                                                        )}
                                                    </Link>
                                                </Button>
                                            )}
                                    </div>
                                    <div className="flex flex-wrap items-center gap-3 sm:ml-auto">
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
