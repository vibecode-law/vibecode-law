import { cn } from '@/lib/utils';
import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { CodeXml } from 'lucide-react';

interface AppLogoProps {
    className?: string;
}

export default function AppLogo({ className }: AppLogoProps) {
    return (
        <Link
            href={home()}
            className={cn(
                'flex cursor-pointer items-center gap-2 transition-opacity hover:opacity-80',
                className,
            )}
        >
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <CodeXml className="h-5 w-5" aria-hidden="true" />
            </span>
            <span className="font-heading text-xl font-bold tracking-tight">
                vibecode<span className="text-muted-foreground">.law</span>
            </span>
        </Link>
    );
}
