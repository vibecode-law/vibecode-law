import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { SubmitButton } from '@/components/ui/submit-button';
import { importMethod } from '@/routes/staff/challenges/invite-codes';
import { Form } from '@inertiajs/react';
import { Upload } from 'lucide-react';
import { useState } from 'react';

type InviteCodeResource =
    App.Http.Resources.Challenge.ChallengeInviteCodeResource;

interface ImportInviteesDialogProps {
    challengeSlug: string;
    inviteCode: InviteCodeResource;
}

export function ImportInviteesDialog({
    challengeSlug,
    inviteCode,
}: ImportInviteesDialogProps) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm">
                    <Upload className="size-4" />
                    Import
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Import invitees</DialogTitle>
                    <DialogDescription>
                        Upload a CSV with the columns email, first_name and
                        last_name (required) plus optional organisation,
                        job_title, linkedin_url and bio.
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...importMethod.form({
                        challenge: challengeSlug,
                        inviteCode: inviteCode.id,
                    })}
                    encType="multipart/form-data"
                    options={{ preserveScroll: true }}
                    onSuccess={() => setOpen(false)}
                    className="space-y-4"
                >
                    {({ processing, errors }) => (
                        <>
                            <FormField
                                label="CSV file"
                                htmlFor="file"
                                error={errors.file}
                            >
                                <Input
                                    id="file"
                                    name="file"
                                    type="file"
                                    accept=".csv,text/csv"
                                    disabled={processing}
                                    aria-invalid={
                                        errors.file !== undefined
                                            ? true
                                            : undefined
                                    }
                                />
                            </FormField>

                            <FormField
                                label="Custom message (optional)"
                                htmlFor="custom_message"
                                error={errors.custom_message}
                            >
                                <MarkdownEditor
                                    name="custom_message"
                                    placeholder="Added to the invitation email sent to each participant. Supports markdown."
                                    height={150}
                                    profile="basic"
                                />
                            </FormField>

                            <div className="flex justify-end">
                                <SubmitButton processing={processing}>
                                    Queue import
                                </SubmitButton>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
