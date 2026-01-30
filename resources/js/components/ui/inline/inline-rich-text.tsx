import { cn } from '@/lib/utils';
import { MarkdownEditor } from '@/components/ui/markdown-editor';
import { useRef, useState } from 'react';

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

    const handleBlur = (event: React.FocusEvent) => {
        // Don't unfocus if focus is moving to another element within the editor container
        // (e.g., toolbar buttons)
        if (
            containerRef.current !== null &&
            event.relatedTarget instanceof Node &&
            containerRef.current.contains(event.relatedTarget)
        ) {
            return;
        }
        setIsFocused(false);
    };

    const hasContent = currentValue.trim().length > 0;

    return (
        <div className={cn('space-y-2', className)}>
            {label !== undefined && (
                <label className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white">
                    {labelIcon}
                    {label}
                    {required === false && (
                        <span className="ml-2 text-sm font-normal text-neutral-400">
                            (optional)
                        </span>
                    )}
                </label>
            )}
            <div
                ref={containerRef}
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
