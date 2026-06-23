import { type FrontendEnum } from '@/types';
import { CheckCircle, Clock, FileEdit, XCircle } from 'lucide-react';

interface ShowcaseStatusBadgeProps {
    status: FrontendEnum<string>;
    size?: 'default' | 'sm';
}

const getStatusConfig = (status: string) => {
    switch (status) {
        case 'Approved':
            return {
                styles: 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800',
                icon: CheckCircle,
            };
        case 'Pending':
            return {
                styles: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
                icon: Clock,
            };
        case 'Rejected':
            return {
                styles: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
                icon: XCircle,
            };
        default:
            return {
                styles: 'bg-neutral-100 text-neutral-600 border-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:border-neutral-700',
                icon: FileEdit,
            };
    }
};

export function ShowcaseStatusBadge({
    status,
    size = 'default',
}: ShowcaseStatusBadgeProps) {
    const statusConfig = getStatusConfig(status.name ?? '');
    const StatusIcon = statusConfig.icon;

    const sizeStyles = {
        default: 'gap-1.5 px-3 py-1 text-sm',
        sm: 'gap-1 px-2 py-0.5 text-xs',
    };

    const iconStyles = {
        default: 'size-4',
        sm: 'size-3',
    };

    return (
        <span
            className={`inline-flex items-center rounded-full border font-medium ${sizeStyles[size]} ${statusConfig.styles}`}
        >
            <StatusIcon className={iconStyles[size]} />
            {status.label}
        </span>
    );
}
