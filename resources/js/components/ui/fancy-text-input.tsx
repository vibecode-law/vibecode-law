import { cn } from '@/lib/utils';
import { forwardRef, useId, useState } from 'react';

interface FancyTextInputProps
    extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'size'> {
    label?: string;
    labelIcon?: React.ReactNode;
    description?: string;
    error?: string;
    required?: boolean;
}

export const FancyTextInput = forwardRef<HTMLInputElement, FancyTextInputProps>(
    (
        {
            className,
            label,
            labelIcon,
            description,
            error,
            required = false,
            id,
            type = 'text',
            value,
            placeholder,
            ...props
        },
        ref,
    ) => {
        const [isFocused, setIsFocused] = useState(false);
        const generatedId = useId();
        const inputId = id ?? generatedId;

        const hasContent =
            value !== undefined && value !== null && String(value).length > 0;
        const showPlaceholder = isFocused === false && hasContent === false;

        return (
            <div className={cn('space-y-2', className)}>
                {label !== undefined && (
                    <label
                        htmlFor={inputId}
                        className="flex items-center gap-2 text-xl font-semibold text-neutral-900 dark:text-white"
                    >
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
                    className={cn(
                        'rounded-lg transition-all',
                        error !== undefined &&
                            'ring-2 ring-red-500 dark:ring-red-400',
                    )}
                >
                    {showPlaceholder === true ? (
                        <button
                            type="button"
                            onClick={() => setIsFocused(true)}
                            onFocus={() => setIsFocused(true)}
                            className="w-full rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-4 text-left text-neutral-400 transition-colors hover:border-amber-400 hover:bg-amber-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-500 dark:hover:border-amber-500 dark:hover:bg-amber-950/20"
                        >
                            {placeholder ?? 'Click to add content...'}
                        </button>
                    ) : (
                        <input
                            ref={ref}
                            id={inputId}
                            type={type}
                            value={value}
                            placeholder={placeholder}
                            className={cn(
                                'w-full rounded-lg border bg-white p-4 text-neutral-900 outline-none transition-all',
                                'placeholder:text-neutral-400 dark:placeholder:text-neutral-500',
                                'border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white',
                                'focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 dark:focus:border-amber-500 dark:focus:ring-amber-500/20',
                            )}
                            autoFocus={isFocused === true}
                            onFocus={(e) => {
                                setIsFocused(true);
                                props.onFocus?.(e);
                            }}
                            onBlur={(e) => {
                                setIsFocused(false);
                                props.onBlur?.(e);
                            }}
                            {...props}
                        />
                    )}
                </div>
                {description !== undefined && (
                    <p className="text-sm text-neutral-500 dark:text-neutral-400">
                        {description}
                    </p>
                )}
                {error !== undefined && (
                    <p className="text-sm text-red-500 dark:text-red-400">
                        {error}
                    </p>
                )}
            </div>
        );
    },
);

FancyTextInput.displayName = 'FancyTextInput';
