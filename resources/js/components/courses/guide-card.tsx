import { Link } from '@inertiajs/react';
import {
    AlertTriangle,
    ArrowRight,
    Lightbulb,
    Play,
    type LucideIcon,
} from 'lucide-react';

const iconMap: Record<string, LucideIcon> = {
    lightbulb: Lightbulb,
    play: Play,
    'alert-triangle': AlertTriangle,
};

const colorMap: Record<
    string,
    { bg: string; icon: string; hover: string; border: string }
> = {
    lightbulb: {
        bg: 'bg-linear-to-br from-yellow-50 to-amber-50 dark:from-yellow-950/30 dark:to-amber-950/30',
        icon: 'text-yellow-600 dark:text-yellow-400',
        hover: 'group-hover:from-yellow-100 group-hover:to-amber-100 dark:group-hover:from-yellow-950/50 dark:group-hover:to-amber-950/50',
        border: 'border-yellow-200 dark:border-yellow-800/50',
    },
    play: {
        bg: 'bg-linear-to-br from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/30',
        icon: 'text-emerald-600 dark:text-emerald-400',
        hover: 'group-hover:from-emerald-100 group-hover:to-green-100 dark:group-hover:from-emerald-950/50 dark:group-hover:to-green-950/50',
        border: 'border-emerald-200 dark:border-emerald-800/50',
    },
    'alert-triangle': {
        bg: 'bg-linear-to-br from-red-50 to-orange-50 dark:from-red-950/30 dark:to-orange-950/30',
        icon: 'text-red-600 dark:text-red-400',
        hover: 'group-hover:from-red-100 group-hover:to-orange-100 dark:group-hover:from-red-950/50 dark:group-hover:to-orange-950/50',
        border: 'border-red-200 dark:border-red-800/50',
    },
    scale: {
        bg: 'bg-linear-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/30',
        icon: 'text-violet-600 dark:text-violet-400',
        hover: 'group-hover:from-violet-100 group-hover:to-purple-100 dark:group-hover:from-violet-950/50 dark:group-hover:to-purple-950/50',
        border: 'border-violet-200 dark:border-violet-800/50',
    },
};

export interface GuideItem {
    name: string;
    slug: string;
    summary: string;
    icon: string;
    route: string;
}

export function GuideCard({ guide }: { guide: GuideItem }) {
    const Icon = iconMap[guide.icon] || Lightbulb;
    const colors = colorMap[guide.icon] || colorMap.lightbulb;

    return (
        <Link
            href={guide.route}
            className={`group relative flex items-start gap-4 rounded-xl border p-6 transition-all duration-200 ${colors.border} bg-white hover:shadow-md dark:bg-neutral-900 dark:hover:bg-neutral-800/50`}
        >
            <div
                className={`flex size-12 shrink-0 items-center justify-center rounded-lg transition-all duration-200 ${colors.bg} ${colors.hover}`}
            >
                <Icon className={`size-6 ${colors.icon}`} />
            </div>
            <div className="min-w-0 flex-1">
                <h3 className="flex items-center gap-2 font-semibold text-neutral-900 dark:text-neutral-100">
                    {guide.name}
                    <ArrowRight className="size-4 opacity-0 transition-all duration-200 group-hover:translate-x-1 group-hover:opacity-100" />
                </h3>
                <p className="mt-1 text-sm leading-relaxed text-neutral-600 dark:text-neutral-400">
                    {guide.summary}
                </p>
            </div>
        </Link>
    );
}
