import { cn } from '@/lib/utils';
import { X } from 'lucide-react';

interface PillOption {
    value: number | string;
    label: string;
}

interface PillSelectProps {
    name: string;
    options: PillOption[];
    selected: (number | string)[];
    onChange: (selected: (number | string)[]) => void;
    placeholder?: string;
    error?: string;
    label?: string;
    labelIcon?: React.ReactNode;
    className?: string;
    required?: boolean;
}

export function PillSelect({
    name,
    options,
    selected,
    onChange,
    placeholder,
    error,
    label,
    labelIcon,
    className,
    required = false,
}: PillSelectProps) {
    const toggleOption = (value: number | string) => {
        if (selected.includes(value)) {
            onChange(selected.filter((v) => v !== value));
        } else {
            onChange([...selected, value]);
        }
    };

    const selectedOptions = options.filter((opt) => selected.includes(opt.value));
    const availableOptions = options.filter((opt) => selected.includes(opt.value) === false);

    return (
        <div className={cn('space-y-3', className)}>
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

            {/* Hidden inputs for form submission */}
            {selected.map((value) => (
                <input
                    key={value}
                    type="hidden"
                    name={`${name}[]`}
                    value={value}
                />
            ))}

            {/* Selected pills */}
            {selectedOptions.length > 0 && (
                <div className="flex flex-wrap gap-2">
                    {selectedOptions.map((option) => (
                        <button
                            key={option.value}
                            type="button"
                            onClick={() => toggleOption(option.value)}
                            className="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-800 transition-colors hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50"
                        >
                            {option.label}
                            <X className="size-3.5" />
                        </button>
                    ))}
                </div>
            )}

            {/* Available options */}
            {availableOptions.length > 0 && (
                <div className="flex flex-wrap gap-2">
                    {availableOptions.map((option) => (
                        <button
                            key={option.value}
                            type="button"
                            onClick={() => toggleOption(option.value)}
                            className="rounded-full bg-neutral-100 px-3 py-1 text-sm text-neutral-700 transition-colors hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                        >
                            {option.label}
                        </button>
                    ))}
                </div>
            )}

            {options.length === 0 && (
                <p className="text-sm text-neutral-400 dark:text-neutral-500">
                    {placeholder ?? 'No options available'}
                </p>
            )}

            {error !== undefined && (
                <p className="text-sm text-red-500 dark:text-red-400">{error}</p>
            )}
        </div>
    );
}
