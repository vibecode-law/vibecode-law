import { useReducedMotion } from '@/hooks/use-reduced-motion';
import { cn, CONFETTI_COLORS } from '@/lib/utils';
import { useEffect, useState } from 'react';

interface Firework {
    id: number;
    x: number;
    y: number;
    color: string;
}

interface FireworksProps {
    /** When true, fireworks spawn continuously; when false they stop and clear. */
    active: boolean;
    /** Number of fireworks in the opening volley. */
    initialCount?: number;
    /** Number of fireworks spawned on each subsequent tick. */
    burstCount?: number;
    /** Milliseconds between ticks. */
    interval?: number;
    /** Positioning/stacking classes for the full-screen overlay container. */
    className?: string;
}

const PARTICLES_PER_FIREWORK = 12;

/**
 * Full-screen celebratory fireworks rendered with CSS keyframes. Respects
 * reduced-motion preferences (no particles spawn and any rendered are hidden).
 */
export function Fireworks({
    active,
    initialCount = 3,
    burstCount = 1,
    interval = 800,
    className,
}: FireworksProps) {
    const prefersReducedMotion = useReducedMotion();
    const [fireworks, setFireworks] = useState<Firework[]>([]);
    const [wasActive, setWasActive] = useState(active);

    // Clear fireworks when deactivated (during render, not in an effect).
    if (wasActive !== active) {
        setWasActive(active);
        if (active === false) {
            setFireworks([]);
        }
    }

    useEffect(() => {
        if (active === false || prefersReducedMotion === true) {
            return;
        }

        const createBurst = (count: number) => {
            const batch: Firework[] = Array.from({ length: count }, () => ({
                id: Date.now() + Math.random(),
                x: 5 + Math.random() * 90,
                y: 5 + Math.random() * 70,
                color: CONFETTI_COLORS[
                    Math.floor(Math.random() * CONFETTI_COLORS.length)
                ],
            }));
            setFireworks((prev) => [...prev, ...batch]);

            const ids = new Set(batch.map((firework) => firework.id));
            setTimeout(() => {
                setFireworks((prev) =>
                    prev.filter((firework) => ids.has(firework.id) === false),
                );
            }, 1000);
        };

        createBurst(initialCount);
        const id = setInterval(() => createBurst(burstCount), interval);

        return () => clearInterval(id);
    }, [active, prefersReducedMotion, initialCount, burstCount, interval]);

    return (
        <div className={cn('pointer-events-none overflow-hidden', className)}>
            {fireworks.map((firework) => (
                <div
                    key={firework.id}
                    className="absolute"
                    style={{ left: `${firework.x}%`, top: `${firework.y}%` }}
                >
                    {[...Array(PARTICLES_PER_FIREWORK)].map((_, index) => (
                        <span
                            key={index}
                            className="firework-particle absolute size-2 rounded-full"
                            style={
                                {
                                    backgroundColor: firework.color,
                                    '--angle': `${index * 30}deg`,
                                } as React.CSSProperties
                            }
                        />
                    ))}
                </div>
            ))}

            <style>{`
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
                    .firework-particle {
                        animation: none;
                        display: none;
                    }
                }
            `}</style>
        </div>
    );
}
