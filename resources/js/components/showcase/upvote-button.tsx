import ShowcaseUpvoteController from '@/actions/App/Http/Controllers/Showcase/ShowcaseUpvoteController';
import { Button } from '@/components/ui/button';
import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn, CONFETTI_COLORS } from '@/lib/utils';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ArrowUp } from 'lucide-react';
import { useId, useState } from 'react';
import { AuthPromptModal } from './upvote-prompt-modal';

interface ConfettiParticle {
    id: string;
    color: string;
    angle: number;
}

interface UpvoteButtonProps {
    showcaseSlug: string;
    upvotesCount: number;
    hasUpvoted: boolean;
    variant?: 'compact' | 'full';
}

export function UpvoteButton({
    showcaseSlug,
    upvotesCount,
    hasUpvoted,
    variant = 'compact',
}: UpvoteButtonProps) {
    const page = usePage<SharedData>();
    const { auth } = page.props;
    const isAuthenticated = auth?.user !== undefined && auth?.user !== null;
    const [showAuthModal, setShowAuthModal] = useState(false);
    const [particles, setParticles] = useState<ConfettiParticle[]>([]);
    const prefersReducedMotion = useReducedMotion();
    const instanceId = useId();

    const triggerConfetti = () => {
        if (prefersReducedMotion === true) {
            return;
        }

        const newParticles: ConfettiParticle[] = Array.from(
            { length: 16 },
            (_, i) => ({
                id: `${instanceId}-${Date.now()}-${i}`,
                color: CONFETTI_COLORS[
                    Math.floor(Math.random() * CONFETTI_COLORS.length)
                ],
                angle: i * 22.5,
            }),
        );

        setParticles(newParticles);

        setTimeout(() => {
            setParticles([]);
        }, 800);
    };

    const handleUpvote = () => {
        if (isAuthenticated === false) {
            setShowAuthModal(true);
            return;
        }

        if (hasUpvoted === false) {
            triggerConfetti();
        }

        router.post(
            ShowcaseUpvoteController.url({ showcase: showcaseSlug }),
            {},
            { preserveScroll: true },
        );
    };

    const confettiBurst =
        particles.length > 0 ? (
            <>
                <div className="pointer-events-none absolute inset-0 z-50 overflow-visible">
                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        {particles.map((particle) => (
                            <span
                                key={particle.id}
                                className="confetti-burst-particle absolute size-2.5 rounded-full"
                                style={
                                    {
                                        backgroundColor: particle.color,
                                        '--angle': `${particle.angle}deg`,
                                    } as React.CSSProperties
                                }
                            />
                        ))}
                    </div>
                </div>
                <style>{`
                    @keyframes confetti-burst {
                        0% {
                            transform: translate(0, 0) scale(1);
                            opacity: 1;
                        }
                        100% {
                            transform: translate(
                                calc(cos(var(--angle)) * 100px),
                                calc(sin(var(--angle)) * 100px)
                            ) scale(0);
                            opacity: 0;
                        }
                    }

                    .confetti-burst-particle {
                        animation: confetti-burst 0.7s ease-out forwards;
                        box-shadow: 0 0 6px 2px currentColor;
                    }

                    @media (prefers-reduced-motion: reduce) {
                        .confetti-burst-particle {
                            animation: none;
                            display: none;
                        }
                    }
                `}</style>
            </>
        ) : null;

    if (variant === 'full') {
        return (
            <>
                <div className="relative w-full overflow-visible">
                    <Button
                        variant={hasUpvoted === true ? 'default' : 'outline'}
                        className="w-full"
                        onClick={handleUpvote}
                    >
                        <ArrowUp className="size-4" />
                        Upvote
                        {upvotesCount > 0 && (
                            <span className="ml-1">&bull; {upvotesCount}</span>
                        )}
                    </Button>
                    {confettiBurst}
                </div>
                <AuthPromptModal
                    isOpen={showAuthModal}
                    onClose={() => setShowAuthModal(false)}
                />
            </>
        );
    }

    return (
        <>
            <div className="relative overflow-visible">
                <Button
                    variant={hasUpvoted ? 'default' : 'outline'}
                    size="sm"
                    onClick={handleUpvote}
                    className="flex h-auto flex-col gap-1 px-3 py-2"
                >
                    <ArrowUp
                        className={cn(
                            'size-5',
                            hasUpvoted === true && 'text-primary-foreground',
                        )}
                    />
                    <span className="text-sm font-semibold">
                        {upvotesCount}
                    </span>
                </Button>
                {confettiBurst}
            </div>
            <AuthPromptModal
                isOpen={showAuthModal}
                onClose={() => setShowAuthModal(false)}
            />
        </>
    );
}
