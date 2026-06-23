import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface BackLinkProps {
    href: string;
    label: string;
}

export function BackLink({ href, label }: BackLinkProps) {
    return (
        <Link
            href={href}
            className="mb-6 inline-flex items-center gap-2 text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white"
        >
            <ArrowLeft className="size-4" />
            {label}
        </Link>
    );
}
