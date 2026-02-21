import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import {
    type ChecklistItem,
    ReadinessChecklist,
} from '@/components/ui/readiness-checklist';
import { Separator } from '@/components/ui/separator';
import { SubmitButton } from '@/components/ui/submit-button';
import { Switch } from '@/components/ui/switch';

interface CheckGroup {
    title: string;
    items: ChecklistItem[];
    variant: 'required' | 'optional' | 'info';
}

interface PublishingSectionProps {
    publishDate: string | null;
    allowPreview: boolean;
    entityLabel: string;
    formPublishDate: string;
    onFormPublishDateChange: (date: string) => void;
    onSubmit: (e: React.FormEvent) => void;
    onClearPublishDate: () => void;
    onAllowPreviewChange: (checked: boolean) => void;
    processing: boolean;
    publishDateError?: string;
    allowPreviewError?: string;
    allRequiredComplete: boolean;
    canEnablePreview?: boolean;
    canPublish: boolean;
    checkGroups: CheckGroup[];
}

export default function PublishingSection({
    publishDate,
    allowPreview,
    entityLabel,
    formPublishDate,
    onFormPublishDateChange,
    onSubmit,
    onClearPublishDate,
    onAllowPreviewChange,
    processing,
    publishDateError,
    allowPreviewError,
    allRequiredComplete,
    canEnablePreview,
    canPublish,
    checkGroups,
}: PublishingSectionProps) {
    const previewReady = canEnablePreview ?? allRequiredComplete;
    const gridCols =
        checkGroups.length >= 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2';

    return (
        <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        Publishing
                    </h3>
                    {publishDate !== null &&
                        (new Date(publishDate) >
                        new Date(new Date().toISOString().split('T')[0]) ? (
                            <Badge className="bg-orange-500 text-white hover:bg-orange-500">
                                Scheduled: {publishDate}
                            </Badge>
                        ) : (
                            <Badge className="bg-green-500 text-white hover:bg-green-500">
                                Published: {publishDate}
                            </Badge>
                        ))}
                    {publishDate === null && (
                        <Badge variant="secondary">No publish date</Badge>
                    )}
                </div>

                <div className="space-y-2">
                    <label className="flex cursor-pointer items-start gap-3">
                        <Switch
                            checked={allowPreview === true}
                            onCheckedChange={onAllowPreviewChange}
                            disabled={
                                previewReady === false && allowPreview === false
                            }
                            className="mt-0.5"
                        />
                        <div>
                            <span className="text-sm font-medium">
                                Allow Preview
                            </span>
                            <p className="text-sm text-neutral-500 dark:text-neutral-400">
                                Allow users to preview this {entityLabel} before
                                publication.
                            </p>
                        </div>
                    </label>
                    {previewReady === false && allowPreview === false && (
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">
                            Complete all required fields to enable preview.
                        </p>
                    )}
                    {allowPreviewError !== undefined && (
                        <p className="text-sm text-red-600 dark:text-red-400">
                            {allowPreviewError}
                        </p>
                    )}
                </div>

                <Separator />

                <form onSubmit={onSubmit}>
                    <div className="space-y-6">
                        <FormField
                            label="Publish Date"
                            htmlFor="publish_date_input"
                            error={publishDateError}
                        >
                            <Input
                                id="publish_date_input"
                                type="date"
                                value={formPublishDate}
                                onChange={(e) =>
                                    onFormPublishDateChange(e.target.value)
                                }
                                disabled={processing === true}
                                className="max-w-xs"
                                aria-invalid={
                                    publishDateError !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>

                        <Separator />

                        <div className={`grid gap-6 ${gridCols}`}>
                            {checkGroups.map((group) => (
                                <ReadinessChecklist
                                    key={group.title}
                                    title={group.title}
                                    items={group.items}
                                    variant={group.variant}
                                />
                            ))}
                        </div>

                        <div className="flex items-center justify-end gap-3 border-t pt-6 dark:border-neutral-800">
                            {publishDate !== null && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={onClearPublishDate}
                                    disabled={processing === true}
                                >
                                    Cancel Publication
                                </Button>
                            )}
                            <SubmitButton
                                processing={processing === true}
                                processingLabel="Saving..."
                                disabled={canPublish === false}
                            >
                                Set Publish Date
                            </SubmitButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    );
}
