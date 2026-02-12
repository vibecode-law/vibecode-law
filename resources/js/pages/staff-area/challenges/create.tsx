import ChallengeFormFields from '@/components/challenges/challenge-form-fields';
import HeadingSmall from '@/components/heading/heading-small';
import { Button } from '@/components/ui/button';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { index, store } from '@/routes/staff/challenges';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function ChallengesCreate() {
    return (
        <StaffAreaLayout fullWidth>
            <Head title="Create Challenge" />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={index.url()}>
                            <ArrowLeft className="mr-1.5 size-4" />
                            Back to challenges
                        </Link>
                    </Button>
                </div>

                <HeadingSmall
                    title="Create Challenge"
                    description="Create a new challenge or competition for the community."
                />

                <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <Form
                        action={store.url()}
                        method="post"
                        encType="multipart/form-data"
                    >
                        {({ errors, processing }) => (
                            <>
                                <ChallengeFormFields
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
                                        Create Challenge
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
