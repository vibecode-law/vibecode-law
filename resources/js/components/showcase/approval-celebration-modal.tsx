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
import { Fireworks } from '@/components/ui/fireworks';
import { CONFETTI_COLORS } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { PartyPopper, X } from 'lucide-react';
import { useState } from 'react';

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
            {/* Fireworks - rendered in a separate portal over the overlay */}
            <DialogPortal>
                <Fireworks active={isOpen} className="fixed inset-0 z-60" />
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

                    @media (prefers-reduced-motion: reduce) {
                        .confetti-fall-particle {
                            animation: none;
                            display: none;
                        }
                    }
                `}</style>
            </DialogContent>
        </Dialog>
    );
}
