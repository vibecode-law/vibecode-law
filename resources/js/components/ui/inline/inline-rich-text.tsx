import { cn } from '@/lib/utils';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { useEffect, useRef, useState } from 'react';

interface InlineRichTextProps {
    name: string;
    value?: string;
    onChange?: (value: string) => void;
    placeholder?: string;
    label?: string;
    labelIcon?: React.ReactNode;
    className?: string;
    height?: number;
    error?: string;
    required?: boolean;
    showOptionalLabel?: boolean;
}

export function InlineRichText({
    name,
    value,
    onChange,
    placeholder,
    label,
    labelIcon,
    className,
    height = 200,
    error,
    required = false,
    showOptionalLabel = true,
}: InlineRichTextProps) {
    const [internalValue, setInternalValue] = useState(value ?? '');
    const [isFocused, setIsFocused] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    const currentValue = value !== undefined ? value : internalValue;

    const handleChange = (newVal: string) => {
        if (onChange !== undefined) {
            onChange(newVal);
        } else {
            setInternalValue(newVal);
        }
    };

    // Collapse back to the placeholder when a click lands outside the
    // component. A click anywhere inside the container (textarea, toolbar
    // command bar, chrome, hint) is never "outside", so toggling a toolbar
    // button on an empty field no longer collapses it.
    useEffect(() => {
        if (isFocused === false) {
            return;
        }

        const handleDocumentMouseDown = (event: MouseEvent) => {
            if (
                containerRef.current !== null &&
                containerRef.current.contains(event.target as Node) === false
            ) {
                setIsFocused(false);
            }
        };

        document.addEventListener('mousedown', handleDocumentMouseDown);

        return () => {
            document.removeEventListener('mousedown', handleDocumentMouseDown);
        };
    }, [isFocused]);

    const handleBlur = (event: React.FocusEvent) => {
        // Only collapse on a genuine focus move to a focusable element
        // outside the container (e.g. keyboard tab-out). A null
        // relatedTarget means focus went nowhere/non-focusable (toolbar
        // mousedown, editor chrome) — leave that to the outside-click
        // handler so the editor doesn't collapse mid-interaction.
        if (
            event.relatedTarget instanceof Node &&
            containerRef.current !== null &&
            containerRef.current.contains(event.relatedTarget) === false
        ) {
            setIsFocused(false);
        }
    };

    const handleContainerMouseDown = (event: React.MouseEvent) => {
        if (containerRef.current === null) {
            return;
        }

        // Clicking the editor's non-focusable chrome (the area below the
        // first line, the markdown hint, padding) would otherwise blur the
        // textarea and collapse an empty field. Keep the caret in the
        // textarea instead.
        const target = event.target as HTMLElement;

        if (target.closest('textarea, button, a, input, [role="button"]') !== null) {
            return;
        }

        event.preventDefault();
        containerRef.current.querySelector('textarea')?.focus();
    };

    const hasContent = currentValue.trim().length > 0;

    return (
        <div className={cn('space-y-2', className)}>
            {label !== undefined && (
                <label className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                    {labelIcon}
                    {label}
                    {required === false && showOptionalLabel === true && (
                        <span className="ml-2 text-sm font-normal text-neutral-400">
                            (optional)
                        </span>
                    )}
                </label>
            )}
            <div
                ref={containerRef}
                onMouseDown={handleContainerMouseDown}
                className={cn(
                    'rounded-lg transition-all',
                    isFocused === false && hasContent === false && 'cursor-pointer',
                    error !== undefined && 'ring-2 ring-red-500 dark:ring-red-400',
                )}
            >
                {isFocused === false && hasContent === false ? (
                    <>
                        <input type="hidden" name={name} value={currentValue} />
                        <button
                            type="button"
                            onClick={() => setIsFocused(true)}
                            onFocus={() => setIsFocused(true)}
                            className="w-full rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-4 text-left text-neutral-400 transition-colors hover:border-amber-400 hover:bg-amber-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-500 dark:hover:border-amber-500 dark:hover:bg-amber-950/20"
                        >
                            {placeholder ?? 'Click to add content...'}
                        </button>
                    </>
                ) : (
                    <MarkdownEditor
                        name={name}
                        value={currentValue}
                        onChange={handleChange}
                        placeholder={placeholder}
                        height={height}
                        autoFocus
                        onFocus={() => setIsFocused(true)}
                        onBlur={handleBlur}
                    />
                )}
            </div>
            {error !== undefined && (
                <p className="text-sm text-red-500 dark:text-red-400">{error}</p>
            )}
        </div>
    );
}
