import { useCallback, useLayoutEffect, useRef } from 'react';

/**
 * FLIP animation helper for reordering lists. Register each item's element with
 * the returned callback ref; whenever `dependency` changes, items smoothly
 * animate from their previous position to their new one using the Web
 * Animations API. Dependency-free and respects `prefers-reduced-motion`.
 */
export function useFlipAnimation(dependency: unknown) {
    const elements = useRef(new Map<string, HTMLElement>());
    const previousRects = useRef(new Map<string, DOMRect>());

    const register = useCallback(
        (key: string | number) =>
            (element: HTMLElement | null): void => {
                const id = String(key);

                if (element === null) {
                    elements.current.delete(id);

                    return;
                }

                elements.current.set(id, element);
            },
        [],
    );

    useLayoutEffect(() => {
        const prefersReducedMotion =
            typeof window !== 'undefined' &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const nextRects = new Map<string, DOMRect>();

        elements.current.forEach((element, id) => {
            const newRect = element.getBoundingClientRect();
            nextRects.set(id, newRect);

            if (prefersReducedMotion === true) {
                return;
            }

            const oldRect = previousRects.current.get(id);

            if (oldRect === undefined) {
                return;
            }

            const deltaX = oldRect.left - newRect.left;
            const deltaY = oldRect.top - newRect.top;

            if (deltaX === 0 && deltaY === 0) {
                return;
            }

            element.animate(
                [
                    { transform: `translate(${deltaX}px, ${deltaY}px)` },
                    { transform: 'translate(0, 0)' },
                ],
                {
                    duration: 600,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                },
            );
        });

        previousRects.current = nextRects;
    }, [dependency]);

    return register;
}
