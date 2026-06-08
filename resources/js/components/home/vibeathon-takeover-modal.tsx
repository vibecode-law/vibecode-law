import ChallengeShowController from '@/actions/App/Http/Controllers/Challenge/Public/ChallengeShowController';
import { DialogOverlay, DialogPortal } from '@/components/ui/dialog';
import { Fireworks } from '@/components/ui/fireworks';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import {
    ArrowRight,
    Award,
    Calendar,
    CodeXml,
    Globe,
    GraduationCap,
    type LucideIcon,
    MapPin,
    Rocket,
    Trophy,
    Users,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

const VIBEATHON_SLUG = 'legaltechtalk-vibeathon-2026';

// 18 June 2026, 12:00 BST (British Summer Time, UTC+1)
const COUNTDOWN_TARGET = new Date('2026-06-18T12:00:00+01:00').getTime();

interface VibeathonTakeoverModalProps {
    open: boolean;
    onClose: () => void;
    onDontShowAgain: () => void;
}

interface Countdown {
    days: number;
    hours: number;
    minutes: number;
    isPast: boolean;
}

function getCountdown(): Countdown {
    const diff = COUNTDOWN_TARGET - Date.now();

    if (diff <= 0) {
        return { days: 0, hours: 0, minutes: 0, isPast: true };
    }

    const minutes = Math.floor(diff / (1000 * 60));

    return {
        days: Math.floor(minutes / (60 * 24)),
        hours: Math.floor((minutes / 60) % 24),
        minutes: minutes % 60,
        isPast: false,
    };
}

function useCountdown(active: boolean): Countdown {
    const [countdown, setCountdown] = useState<Countdown>(() => getCountdown());

    useEffect(() => {
        if (active === false) {
            return;
        }

        const interval = window.setInterval(() => {
            setCountdown(getCountdown());
        }, 1000 * 15);

        return () => window.clearInterval(interval);
    }, [active]);

    return countdown;
}

/**
 * Approximation of the Replit brand mark. Mode-independent so the orange
 * reads correctly in both light and dark mode.
 */
function ReplitMark({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 24 24"
            className={cn('text-[#F26207]', className)}
            fill="currentColor"
            aria-hidden="true"
        >
            <path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h6.75v6H3V4.5Z" />
            <path d="M11.25 9h6.75a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-6.75V9Z" />
            <path d="M3 15h8.25v6H4.5A1.5 1.5 0 0 1 3 19.5V15Z" />
        </svg>
    );
}

function HeaderPill({
    icon: IconComponent,
    children,
}: {
    icon: LucideIcon;
    children: React.ReactNode;
}) {
    return (
        <span className="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-neutral-50/80 px-3 py-1.5 text-xs font-medium text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800/60 dark:text-neutral-300">
            <IconComponent className="size-3.5 text-emerald-500" />
            {children}
        </span>
    );
}

function CategoryCard({
    icon: IconComponent,
    title,
    description,
}: {
    icon: LucideIcon;
    title: string;
    description: string;
}) {
    return (
        <div className="flex items-start gap-3 rounded-xl border border-neutral-200 bg-neutral-50/60 p-3.5 dark:border-neutral-700 dark:bg-neutral-900">
            <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                <IconComponent className="size-4.5" />
            </span>
            <div className="space-y-0.5">
                <p className="text-sm font-semibold text-neutral-900 dark:text-white">
                    {title}
                </p>
                <p className="text-sm text-neutral-600 dark:text-neutral-300">
                    {description}
                </p>
            </div>
        </div>
    );
}

function PrizeRow({
    icon: IconComponent,
    children,
}: {
    icon: LucideIcon;
    children: React.ReactNode;
}) {
    return (
        <li className="flex items-start gap-2.5 text-sm text-neutral-700 dark:text-neutral-200">
            <IconComponent className="mt-0.5 size-4 shrink-0 text-emerald-500" />
            <span>{children}</span>
        </li>
    );
}

export function VibeathonTakeoverModal({
    open,
    onClose,
    onDontShowAgain,
}: VibeathonTakeoverModalProps) {
    const { days, hours, minutes, isPast } = useCountdown(open);

    const countdownLabel = useMemo(() => {
        if (isPast === true) {
            return 'HAPPENING NOW';
        }

        return `${days}D ${hours}H ${minutes}M TO GO`;
    }, [days, hours, minutes, isPast]);

    return (
        <DialogPrimitive.Root
            open={open}
            onOpenChange={(next) => {
                if (next === false) {
                    onClose();
                }
            }}
        >
            <DialogPortal>
                <DialogOverlay className="bg-black/80 backdrop-blur-xs dark:bg-neutral-500/30" />

                {/* Fireworks sit over the overlay, behind the modal content. */}
                <Fireworks
                    active={open}
                    initialCount={8}
                    burstCount={3}
                    interval={280}
                    className="fixed inset-0 z-[55]"
                />

                <div className="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto p-3 sm:p-4">
                    <DialogPrimitive.Content
                        onOpenAutoFocus={(event) => event.preventDefault()}
                        className="relative my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-6xl overflow-y-auto rounded-2xl border border-neutral-200 bg-white shadow-2xl duration-200 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 dark:border-neutral-700 dark:bg-neutral-950"
                    >
                        {/* Decorative corner glow */}
                        <div className="pointer-events-none absolute top-0 right-0 size-64 rounded-full bg-emerald-500/10 blur-3xl dark:bg-emerald-500/15" />

                        <div className="relative p-6 sm:p-8 lg:p-10">
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex flex-wrap items-center gap-2">
                                    <img
                                        src="/static/ltt-vibeathon-logos/ltt.png"
                                        alt="LegalTechTalk"
                                        className="h-7 w-auto dark:invert"
                                    />
                                    <HeaderPill icon={Calendar}>
                                        17–18 June 2026
                                    </HeaderPill>
                                    <HeaderPill icon={MapPin}>
                                        London · InterContinental O2
                                    </HeaderPill>
                                </div>

                                <DialogPrimitive.Close
                                    className="flex size-9 shrink-0 items-center justify-center rounded-lg border border-neutral-200 text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-white"
                                    aria-label="Close"
                                >
                                    <X className="size-4" />
                                </DialogPrimitive.Close>
                            </div>

                            <div className="mt-6">
                                <div className="flex flex-wrap items-center gap-2.5">
                                    <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold tracking-wide text-emerald-600 dark:text-emerald-400">
                                        <span className="size-1.5 rounded-full bg-emerald-500" />
                                        {countdownLabel}
                                    </span>
                                    <DialogPrimitive.Title className="text-sm font-medium text-neutral-500 dark:text-neutral-300">
                                        LegalTechTalk Vibeathon 2026
                                    </DialogPrimitive.Title>
                                </div>

                                <h2 className="mt-4 text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl lg:text-6xl dark:text-white">
                                    It's Time to{' '}
                                    <span className="bg-linear-to-r from-emerald-600 via-amber-500 to-orange-500 bg-clip-text text-transparent">
                                        Build
                                    </span>
                                </h2>

                                <DialogPrimitive.Description className="mt-4 max-w-xl text-base text-neutral-600 sm:text-lg dark:text-neutral-300">
                                    On{' '}
                                    <strong className="font-semibold text-neutral-900 dark:text-white">
                                        17–18 June 2026
                                    </strong>
                                    , we're running the biggest vibecode event
                                    in legal. You're invited.
                                </DialogPrimitive.Description>

                                <div className="mt-6 flex flex-col gap-3">
                                    <div className="flex flex-col gap-3 md:flex-row">
                                        <Link
                                            href={ChallengeShowController.url({
                                                challenge: VIBEATHON_SLUG,
                                            })}
                                            className="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-emerald-500 px-6 text-base font-semibold text-white shadow-lg shadow-emerald-500/25 transition-colors hover:bg-emerald-600 focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:outline-none dark:focus-visible:ring-offset-neutral-950"
                                        >
                                            Take me to the Vibeathon
                                            <ArrowRight className="size-4" />
                                        </Link>
                                        <button
                                            type="button"
                                            onClick={onClose}
                                            className="inline-flex h-12 items-center justify-center rounded-lg border border-neutral-300 px-6 text-base font-medium text-neutral-700 transition-colors hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                        >
                                            I'm just browsing
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={onDontShowAgain}
                                        className="self-center text-sm text-neutral-500 underline underline-offset-2 transition-colors hover:text-neutral-800 md:self-start dark:text-neutral-400 dark:hover:text-neutral-200"
                                    >
                                        Don't show this again
                                    </button>
                                </div>
                            </div>

                            <hr className="my-8 border-neutral-200 dark:border-neutral-700" />

                            <div className="grid gap-8 lg:grid-cols-2">
                                <div>
                                    <h3 className="text-sm font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-400">
                                        Three categories
                                    </h3>
                                    <div className="mt-3 space-y-2.5">
                                        <CategoryCard
                                            icon={GraduationCap}
                                            title="Lawyer Training"
                                            description="Arm lawyers with the skills they'll need."
                                        />
                                        <CategoryCard
                                            icon={Globe}
                                            title="People, Planet & Justice"
                                            description="Pair tech with legal skills for positive impact."
                                        />
                                        <CategoryCard
                                            icon={Rocket}
                                            title="Freestyle"
                                            description="Build whatever the industry needs next."
                                        />
                                    </div>
                                </div>

                                <div className="space-y-6">
                                    <div>
                                        <h3 className="text-sm font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-400">
                                            Free Replit Pro credits
                                        </h3>
                                        <div className="mt-3 flex items-start gap-3 rounded-xl border border-neutral-200 bg-neutral-50/60 p-3.5 dark:border-neutral-700 dark:bg-neutral-900">
                                            <ReplitMark className="mt-0.5 size-5 shrink-0" />
                                            <p className="text-sm text-neutral-600 dark:text-neutral-300">
                                                Every participant gets{' '}
                                                <strong className="font-semibold text-neutral-900 dark:text-white">
                                                    free Replit Pro
                                                </strong>{' '}
                                                for one month.
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 className="text-sm font-semibold tracking-wide text-emerald-600 uppercase dark:text-emerald-400">
                                            Prizes For Three Winners
                                        </h3>
                                        <ul className="mt-3 space-y-2.5">
                                            <PrizeRow icon={Trophy}>
                                                An official{' '}
                                                <strong className="font-semibold text-neutral-900 dark:text-white">
                                                    LegalTechTalk trophy
                                                </strong>
                                                , awarded live on stage.
                                            </PrizeRow>
                                            <PrizeRow icon={Award}>
                                                <strong className="font-semibold text-neutral-900 dark:text-white">
                                                    One year of Replit Pro
                                                </strong>{' '}
                                                for every winner.
                                            </PrizeRow>
                                            <PrizeRow icon={Users}>
                                                A place in an exclusive{' '}
                                                <strong className="font-semibold text-neutral-900 dark:text-white">
                                                    Masterclass
                                                </strong>{' '}
                                                on investment, product &
                                                go-to-market.
                                            </PrizeRow>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <hr className="my-8 border-neutral-200 dark:border-neutral-700" />

                            <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-4">
                                <img
                                    src="/static/ltt-vibeathon-logos/ltt.png"
                                    alt="LegalTechTalk"
                                    className="h-6 w-auto dark:invert"
                                />
                                <img
                                    src="/static/ltt-vibeathon-logos/hsfk.png"
                                    alt="Herbert Smith Freehills Kramer"
                                    className="h-7 w-auto dark:brightness-0 dark:invert"
                                />
                                <span className="flex items-center gap-2 font-bold tracking-tight text-neutral-900 dark:text-white">
                                    <span className="flex size-6 items-center justify-center rounded-md bg-neutral-900 text-white dark:bg-white dark:text-neutral-900">
                                        <CodeXml className="size-4" />
                                    </span>
                                    <span>
                                        vibecode
                                        <span className="text-neutral-400 dark:text-neutral-500">
                                            .law
                                        </span>
                                    </span>
                                </span>
                                <span className="flex items-center gap-1.5 font-bold text-neutral-900 dark:text-white">
                                    <ReplitMark className="size-5" />
                                    Replit
                                </span>
                            </div>
                        </div>
                    </DialogPrimitive.Content>
                </div>
            </DialogPortal>
        </DialogPrimitive.Root>
    );
}
