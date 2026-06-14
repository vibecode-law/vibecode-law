import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { type ResolvedAppearance } from '@/hooks/use-appearance';
import { LayoutGrid, List, Minus, Moon, Plus, Sun } from 'lucide-react';
import { useEffect, useState } from 'react';

export type LiveLayoutMode = 'single' | 'per-sub-challenge';
export type LiveTopLimit = '3' | '5';
export type LiveColumns = '1' | '2' | '3' | '4';

export const SIZE_MIN = 70;
export const SIZE_MAX = 160;
export const SIZE_STEP = 10;

const clampSize = (value: number): number =>
    Math.min(SIZE_MAX, Math.max(SIZE_MIN, value));

interface LiveSettingsMenuProps {
    appearance: ResolvedAppearance;
    onAppearanceChange: (appearance: ResolvedAppearance) => void;
    layoutMode: LiveLayoutMode;
    onLayoutModeChange: (mode: LiveLayoutMode) => void;
    hasSubChallenges: boolean;
    topLimit: LiveTopLimit;
    onTopLimitChange: (limit: LiveTopLimit) => void;
    columns: LiveColumns;
    onColumnsChange: (columns: LiveColumns) => void;
    size: number;
    onSizeChange: (size: number) => void;
}

export function LiveSettingsMenu({
    appearance,
    onAppearanceChange,
    layoutMode,
    onLayoutModeChange,
    hasSubChallenges,
    topLimit,
    onTopLimitChange,
    columns,
    onColumnsChange,
    size,
    onSizeChange,
}: LiveSettingsMenuProps) {
    const [open, setOpen] = useState(false);
    const [showHint, setShowHint] = useState(true);

    // Briefly hint how to reach the (otherwise hidden) settings menu, then fade.
    useEffect(() => {
        const timeout = setTimeout(() => setShowHint(false), 6000);

        return () => clearTimeout(timeout);
    }, []);

    // Hidden trigger: press "s" to open the settings menu. Ignored while typing
    // in a field or when a modifier key is held.
    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent): void => {
            if (event.key !== 's' && event.key !== 'S') {
                return;
            }

            if (
                event.ctrlKey === true ||
                event.metaKey === true ||
                event.altKey === true
            ) {
                return;
            }

            const target = event.target as HTMLElement | null;

            if (
                target !== null &&
                (target.tagName === 'INPUT' ||
                    target.tagName === 'TEXTAREA' ||
                    target.isContentEditable === true)
            ) {
                return;
            }

            event.preventDefault();
            setShowHint(false);
            setOpen((current) => current === false);
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => {
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, []);

    return (
        <>
            <div
                className={`fixed right-6 bottom-6 z-50 rounded-full bg-neutral-900/80 px-5 py-3 text-lg font-medium text-white shadow-xl backdrop-blur transition-opacity duration-700 motion-reduce:transition-none lg:text-xl dark:bg-white/80 dark:text-neutral-900 ${
                    showHint === true && open === false
                        ? 'opacity-100'
                        : 'pointer-events-none opacity-0'
                }`}
            >
                Press{' '}
                <kbd className="rounded bg-white/20 px-2 py-0.5 font-semibold dark:bg-neutral-900/20">
                    S
                </kbd>{' '}
                for settings
            </div>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="sm:max-w-sm">
                    <DialogTitle>Display settings</DialogTitle>
                    <DialogDescription>
                        These only affect this screen and are remembered on this
                        device.
                    </DialogDescription>

                    <div className="space-y-6 pt-2">
                        <div className="space-y-2">
                            <p className="text-sm font-medium">Appearance</p>
                            <ToggleGroup
                                type="single"
                                variant="outline"
                                value={appearance}
                                onValueChange={(value) => {
                                    if (value === 'light' || value === 'dark') {
                                        onAppearanceChange(value);
                                    }
                                }}
                                className="w-full"
                            >
                                <ToggleGroupItem
                                    value="light"
                                    className="flex-1 gap-2"
                                >
                                    <Sun className="size-4" />
                                    Light
                                </ToggleGroupItem>
                                <ToggleGroupItem
                                    value="dark"
                                    className="flex-1 gap-2"
                                >
                                    <Moon className="size-4" />
                                    Dark
                                </ToggleGroupItem>
                            </ToggleGroup>
                        </div>

                        <div className="space-y-2">
                            <p className="text-sm font-medium">Size</p>
                            <div className="flex items-center gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    onClick={() =>
                                        onSizeChange(
                                            clampSize(size - SIZE_STEP),
                                        )
                                    }
                                    disabled={size <= SIZE_MIN}
                                    aria-label="Decrease size"
                                >
                                    <Minus className="size-4" />
                                </Button>
                                <span className="flex-1 text-center text-sm font-medium tabular-nums">
                                    {size}%
                                </span>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    onClick={() =>
                                        onSizeChange(
                                            clampSize(size + SIZE_STEP),
                                        )
                                    }
                                    disabled={size >= SIZE_MAX}
                                    aria-label="Increase size"
                                >
                                    <Plus className="size-4" />
                                </Button>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <p className="text-sm font-medium">Show</p>
                            <ToggleGroup
                                type="single"
                                variant="outline"
                                value={topLimit}
                                onValueChange={(value) => {
                                    if (value === '3' || value === '5') {
                                        onTopLimitChange(value);
                                    }
                                }}
                                className="w-full"
                            >
                                <ToggleGroupItem value="3" className="flex-1">
                                    Top 3
                                </ToggleGroupItem>
                                <ToggleGroupItem value="5" className="flex-1">
                                    Top 5
                                </ToggleGroupItem>
                            </ToggleGroup>
                        </div>

                        {hasSubChallenges === true && (
                            <>
                                <div className="space-y-2">
                                    <p className="text-sm font-medium">
                                        Layout
                                    </p>
                                    <ToggleGroup
                                        type="single"
                                        variant="outline"
                                        value={layoutMode}
                                        onValueChange={(value) => {
                                            if (
                                                value === 'single' ||
                                                value === 'per-sub-challenge'
                                            ) {
                                                onLayoutModeChange(value);
                                            }
                                        }}
                                        className="w-full"
                                    >
                                        <ToggleGroupItem
                                            value="single"
                                            className="flex-1 gap-2"
                                        >
                                            <List className="size-4" />
                                            Single board
                                        </ToggleGroupItem>
                                        <ToggleGroupItem
                                            value="per-sub-challenge"
                                            className="flex-1 gap-2"
                                        >
                                            <LayoutGrid className="size-4" />
                                            Per category
                                        </ToggleGroupItem>
                                    </ToggleGroup>
                                </div>

                                {layoutMode === 'per-sub-challenge' && (
                                    <div className="space-y-2">
                                        <p className="text-sm font-medium">
                                            Columns
                                        </p>
                                        <ToggleGroup
                                            type="single"
                                            variant="outline"
                                            value={columns}
                                            onValueChange={(value) => {
                                                if (
                                                    value === '1' ||
                                                    value === '2' ||
                                                    value === '3' ||
                                                    value === '4'
                                                ) {
                                                    onColumnsChange(value);
                                                }
                                            }}
                                            className="w-full"
                                        >
                                            <ToggleGroupItem
                                                value="1"
                                                className="flex-1"
                                            >
                                                1
                                            </ToggleGroupItem>
                                            <ToggleGroupItem
                                                value="2"
                                                className="flex-1"
                                            >
                                                2
                                            </ToggleGroupItem>
                                            <ToggleGroupItem
                                                value="3"
                                                className="flex-1"
                                            >
                                                3
                                            </ToggleGroupItem>
                                            <ToggleGroupItem
                                                value="4"
                                                className="flex-1"
                                            >
                                                4
                                            </ToggleGroupItem>
                                        </ToggleGroup>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
}
