import { Badge } from '@/components/ui/badge';

type ImportResource =
    App.Http.Resources.Challenge.ChallengeInviteCodeImportResource;

export const IMPORT_STATUS = {
    Pending: 1,
    Processing: 2,
    Completed: 3,
    Failed: 4,
} as const satisfies Record<string, App.Enums.ChallengeInviteCodeImportStatus>;

const STATUS_LABELS: Record<
    App.Enums.ChallengeInviteCodeImportStatus,
    { label: string; className: string }
> = {
    1: { label: 'Pending', className: 'bg-amber-500 text-white' },
    2: { label: 'Processing', className: 'bg-amber-500 text-white' },
    3: { label: 'Completed', className: 'bg-green-500 text-white' },
    4: { label: 'Failed', className: 'bg-red-500 text-white' },
};

export function ImportSummary({ summary }: { summary: ImportResource }) {
    const status = STATUS_LABELS[summary.status];
    const skippedRows =
        summary.skipped_rows !== null
            ? Object.values(summary.skipped_rows)
            : [];

    return (
        <div className="mt-2 rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-800/50">
            <div className="flex flex-wrap items-center gap-2">
                <span className="font-medium text-neutral-700 dark:text-neutral-200">
                    Last import:
                </span>
                <Badge className={status.className}>{status.label}</Badge>
                {summary.status === IMPORT_STATUS.Completed && (
                    <span className="text-neutral-500 dark:text-neutral-400">
                        {summary.imported_count} imported,{' '}
                        {summary.skipped_count} skipped of {summary.total_rows}{' '}
                        rows
                    </span>
                )}
            </div>

            {skippedRows.length > 0 && (
                <ul className="mt-2 space-y-1 text-neutral-500 dark:text-neutral-400">
                    {skippedRows.map((skipped, index) => (
                        <li key={index}>
                            Row {skipped.row}
                            {skipped.email !== null
                                ? ` (${skipped.email})`
                                : ''}
                            : {skipped.reason}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
