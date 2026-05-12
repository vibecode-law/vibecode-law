import HeadingSmall from '@/components/heading/heading-small';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { SubmitButton } from '@/components/ui/submit-button';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { updateAnnouncement } from '@/routes/staff/settings';
import { Head, useForm } from '@inertiajs/react';

interface SettingsIndexProps {
    announcementMarkdown: string | null;
}

export default function SettingsIndex({
    announcementMarkdown,
}: SettingsIndexProps) {
    const { data, setData, patch, processing, errors } = useForm<{
        announcement: string | null;
    }>({
        announcement: announcementMarkdown ?? '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(updateAnnouncement.url(), { preserveScroll: true });
    };

    const handleClear = () => {
        setData('announcement', '');
        patch(updateAnnouncement.url(), { preserveScroll: true });
    };

    return (
        <StaffAreaLayout fullWidth>
            <Head title="Settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Settings"
                    description="Manage site-wide settings"
                />

                <form onSubmit={handleSubmit} className="max-w-2xl space-y-4">
                    <FormField
                        label="Announcement Banner"
                        htmlFor="announcement"
                        error={errors.announcement}
                    >
                        <p className="-mt-1 text-sm text-muted-foreground">
                            Displayed across the top of the website. Supports
                            markdown. Leave empty to hide.
                        </p>
                        <MarkdownEditor
                            name="announcement"
                            value={data.announcement ?? ''}
                            onChange={(value) => setData('announcement', value)}
                            placeholder="e.g. We're launching a new feature! [Learn more](/about)"
                            height={150}
                            profile="basic"
                        />
                    </FormField>

                    <div className="flex items-center gap-3">
                        <SubmitButton
                            processing={processing}
                            processingLabel="Saving..."
                        >
                            Save
                        </SubmitButton>
                        {announcementMarkdown !== null &&
                            announcementMarkdown !== '' && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={handleClear}
                                    disabled={processing}
                                >
                                    Clear Announcement
                                </Button>
                            )}
                    </div>
                </form>
            </div>
        </StaffAreaLayout>
    );
}
