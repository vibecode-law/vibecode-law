import ShowcaseDismissCelebrationController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseDismissCelebrationController';
import { LinkedInIcon } from '@/components/icons/linkedin-icon';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogPortal,
    DialogTitle,
} from '@/components/ui/dialog';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { CONFETTI_COLORS } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { PartyPopper, X } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Firework {
    id: number;
    x: number;
    y: number;
    color: string;
}

interface ApprovalCelebrationModalProps {
    isOpen: boolean;
    onClose: () => void;
    showcaseSlug: string;
    linkedInShareUrl: string;
}

export function ApprovalCelebrationModal({
    isOpen,
    onClose,
    showcaseSlug,
    linkedInShareUrl,
}: ApprovalCelebrationModalProps) {
    const [isDismissing, setIsDismissing] = useState(false);
    const [fireworks, setFireworks] = useState<Firework[]>([]);
    const [wasOpen, setWasOpen] = useState(isOpen);
    const prefersReducedMotion = useReducedMotion();

    // Reset fireworks when modal closes (during render, not in effect)
    if (wasOpen !== isOpen) {
        setWasOpen(isOpen);
        if (isOpen === false) {
            setFireworks([]);
        }
    }

    useEffect(() => {
        if (isOpen === false || prefersReducedMotion === true) {
            return;
        }

        const createFirework = () => {
            const newFirework: Firework = {
                id: Date.now() + Math.random(),
                x: 10 + Math.random() * 80,
                y: 10 + Math.random() * 60,
                color: CONFETTI_COLORS[
                    Math.floor(Math.random() * CONFETTI_COLORS.length)
                ],
            };
            setFireworks((prev) => [...prev, newFirework]);

            setTimeout(() => {
                setFireworks((prev) =>
                    prev.filter((f) => f.id !== newFirework.id),
                );
            }, 1000);
        };

        // Initial burst of fireworks
        for (let i = 0; i < 3; i++) {
            setTimeout(createFirework, i * 200);
        }

        // Continue with periodic fireworks
        const interval = setInterval(createFirework, 800);

        return () => clearInterval(interval);
    }, [isOpen, prefersReducedMotion]);

    const handleDismiss = () => {
        setIsDismissing(true);

        router.post(
            ShowcaseDismissCelebrationController.url({
                showcase: showcaseSlug,
            }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    onClose();
                },
                onFinish: () => {
                    setIsDismissing(false);
                },
            },
        );
    };

    const handleShareOnLinkedIn = () => {
        if (typeof window === 'undefined') return;

        window.open(linkedInShareUrl, '_blank', 'noopener');
        handleDismiss();
    };

    return (
        <Dialog
            open={isOpen}
            onOpenChange={(open) => {
                if (open === false) {
                    onClose();
                }
            }}
        >
            {/* Fireworks - rendered in separate portal over the overlay */}
            <DialogPortal>
                <div className="pointer-events-none fixed inset-0 z-60 overflow-hidden">
                    {fireworks.map((firework) => (
                        <div
                            key={firework.id}
                            className="absolute"
                            style={{
                                left: `${firework.x}%`,
                                top: `${firework.y}%`,
                            }}
                        >
                            {[...Array(12)].map((_, i) => (
                                <span
                                    key={i}
                                    className="firework-particle absolute size-2 rounded-full"
                                    style={
                                        {
                                            backgroundColor: firework.color,
                                            '--angle': `${i * 30}deg`,
                                        } as React.CSSProperties
                                    }
                                />
                            ))}
                        </div>
                    ))}
                </div>
            </DialogPortal>

            <DialogContent className="z-70 overflow-hidden sm:max-w-md">
                {/* Confetti particles */}
                <div className="pointer-events-none absolute -top-2 right-0 left-0 flex justify-center gap-1">
                    {[...Array(12)].map((_, i) => (
                        <span
                            key={i}
                            className="confetti-fall-particle inline-block size-2 rounded-full"
                            style={{
                                backgroundColor:
                                    CONFETTI_COLORS[i % CONFETTI_COLORS.length],
                                animationDelay: `${i * 0.1}s`,
                            }}
                        />
                    ))}
                </div>

                <DialogHeader className="flex flex-col items-center text-center">
                    <div className="mb-3 animate-bounce rounded-full border border-amber-200 bg-amber-50 p-0.5 shadow-sm dark:border-amber-700 dark:bg-amber-950">
                        <div className="rounded-full border border-amber-200 bg-amber-100 p-3 dark:border-amber-700 dark:bg-amber-900">
                            <PartyPopper className="size-8 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <DialogTitle className="text-xl">
                        Congratulations!
                    </DialogTitle>
                    <DialogDescription className="text-center">
                        Your showcase has been approved and is now live! Share
                        your achievement with your professional network on
                        LinkedIn.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter className="flex-col gap-2 sm:flex-col">
                    <Button
                        onClick={handleShareOnLinkedIn}
                        className="w-full gap-2 bg-[#0A66C2] text-white hover:bg-[#004182]"
                        disabled={isDismissing}
                    >
                        <LinkedInIcon className="size-5" />
                        Share on LinkedIn
                    </Button>
                    <Button
                        variant="ghost"
                        onClick={handleDismiss}
                        className="w-full"
                        disabled={isDismissing}
                    >
                        <X className="size-4" />
                        Maybe Later
                    </Button>
                </DialogFooter>

                <style>{`
                    @keyframes confetti-fall {
                        0% {
                            transform: translateY(0) rotate(0deg);
                            opacity: 1;
                        }
                        100% {
                            transform: translateY(60px) rotate(720deg);
                            opacity: 0;
                        }
                    }

                    .confetti-fall-particle {
                        animation: confetti-fall 1.5s ease-out infinite;
                    }

                    @keyframes firework-burst {
                        0% {
                            transform: translate(0, 0) scale(1);
                            opacity: 1;
                        }
                        100% {
                            transform: translate(
                                calc(cos(var(--angle)) * 80px),
                                calc(sin(var(--angle)) * 80px)
                            ) scale(0);
                            opacity: 0;
                        }
                    }

                    .firework-particle {
                        animation: firework-burst 1s ease-out forwards;
                        box-shadow: 0 0 6px 2px currentColor;
                    }

                    @media (prefers-reduced-motion: reduce) {
                        .confetti-fall-particle,
                        .firework-particle {
                            animation: none;
                            display: none;
                        }
                    }
                `}</style>
            </DialogContent>
        </Dialog>
    );
}
