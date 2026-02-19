import { Badge } from '@/components/ui/badge';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import {
    type ChecklistItem,
    ReadinessChecklist,
} from '@/components/ui/readiness-checklist';
import { SubmitButton } from '@/components/ui/submit-button';
import { syncVideoHost } from '@/routes/staff/academy/courses/lessons';
import { useForm } from '@inertiajs/react';
import { useMemo } from 'react';

interface LessonVideoHostSectionProps {
    courseSlug: string;
    lessonSlug: string;
    assetId: string | null;
    playbackId: string | null;
    durationSeconds: number | null;
    hasVttTranscript: boolean;
    hasTxtTranscript: boolean;
    hasTranscriptLines: boolean;
}

export default function LessonVideoHostSection({
    courseSlug,
    lessonSlug,
    assetId,
    playbackId,
    durationSeconds,
    hasVttTranscript,
    hasTxtTranscript,
    hasTranscriptLines,
}: LessonVideoHostSectionProps) {
    const syncForm = useForm({
        asset_id: assetId ?? '',
    });

    const isSynced =
        assetId !== null &&
        assetId.length > 0 &&
        playbackId !== null &&
        playbackId.length > 0 &&
        durationSeconds !== null;

    const syncChecks: ChecklistItem[] = useMemo(
        () => [
            {
                label: 'Asset ID',
                completed: assetId !== null && assetId.length > 0,
            },
            {
                label: 'Duration',
                completed: durationSeconds !== null,
            },
            {
                label: 'Playback ID',
                completed: playbackId !== null && playbackId.length > 0,
            },
            {
                label: 'VTT Transcript',
                completed: hasVttTranscript === true,
            },
            {
                label: 'TXT Transcript',
                completed: hasTxtTranscript === true,
            },
            {
                label: 'Parsed transcript lines',
                completed: hasTranscriptLines === true,
            },
        ],
        [
            assetId,
            playbackId,
            durationSeconds,
            hasVttTranscript,
            hasTxtTranscript,
            hasTranscriptLines,
        ],
    );

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        syncForm.patch(
            syncVideoHost.url({
                course: courseSlug,
                lesson: lessonSlug,
            }),
            { preserveScroll: true },
        );
    }

    return (
        <div className="rounded-lg border bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h3 className="font-semibold text-neutral-900 dark:text-white">
                        Video Host
                    </h3>
                    {isSynced === true ? (
                        <Badge className="bg-green-500 text-white hover:bg-green-500">
                            Synced
                        </Badge>
                    ) : (
                        <Badge variant="secondary">Not synced</Badge>
                    )}
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="space-y-6">
                        <FormField
                            label="Mux Asset ID"
                            htmlFor="sync_asset_id"
                            error={syncForm.errors.asset_id}
                        >
                            <div className="flex items-start gap-3">
                                <Input
                                    id="sync_asset_id"
                                    value={syncForm.data.asset_id}
                                    onChange={(e) =>
                                        syncForm.setData(
                                            'asset_id',
                                            e.target.value,
                                        )
                                    }
                                    disabled={syncForm.processing === true}
                                    placeholder="Enter Mux asset ID"
                                    className="max-w-md"
                                    aria-invalid={
                                        syncForm.errors.asset_id !== undefined
                                            ? true
                                            : undefined
                                    }
                                />
                                <SubmitButton
                                    processing={syncForm.processing === true}
                                    processingLabel="Syncing..."
                                    disabled={syncForm.data.asset_id === ''}
                                >
                                    Sync
                                </SubmitButton>
                            </div>
                        </FormField>

                        <ReadinessChecklist
                            title="Sync Status"
                            items={syncChecks}
                            variant="info"
                        />
                    </div>
                </form>
            </div>
        </div>
    );
}
