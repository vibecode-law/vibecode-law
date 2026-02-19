import { cn } from '@/lib/utils';
import { X } from 'lucide-react';

interface PillOption {
    value: number;
    label: string;
}

interface PillGroup {
    label: string;
    options: PillOption[];
}

interface GroupedPillSelectProps {
    name: string;
    groups: PillGroup[];
    selected: number[];
    onChange: (selected: number[]) => void;
    error?: string;
    label?: string;
    className?: string;
}

export function GroupedPillSelect({
    name,
    groups,
    selected,
    onChange,
    error,
    label,
    className,
}: GroupedPillSelectProps) {
    const toggleOption = (value: number) => {
        if (selected.includes(value)) {
            onChange(selected.filter((v) => v !== value));
        } else {
            onChange([...selected, value]);
        }
    };

    const allOptions = groups.flatMap((g) => g.options);

    return (
        <div className={cn('space-y-4', className)}>
            {label !== undefined && (
                <label className="flex items-center gap-2 text-sm font-medium text-neutral-900 dark:text-white">
                    {label}
                    <span className="text-sm font-normal text-neutral-400">
                        (optional)
                    </span>
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

            {/* Each group rendered as its own pill selector */}
            {groups.map((group) => {
                const groupSelected = group.options.filter((opt) =>
                    selected.includes(opt.value),
                );
                const groupAvailable = group.options.filter(
                    (opt) => selected.includes(opt.value) === false,
                );

                return (
                    <div key={group.label} className="space-y-2">
                        <p className="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400">
                            {group.label}
                        </p>

                        {groupSelected.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {groupSelected.map((option) => (
                                    <button
                                        key={option.value}
                                        type="button"
                                        onClick={() =>
                                            toggleOption(option.value)
                                        }
                                        className="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-800 transition-colors hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50"
                                    >
                                        {option.label}
                                        <X className="size-3.5" />
                                    </button>
                                ))}
                            </div>
                        )}

                        {groupAvailable.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                {groupAvailable.map((option) => (
                                    <button
                                        key={option.value}
                                        type="button"
                                        onClick={() =>
                                            toggleOption(option.value)
                                        }
                                        className="rounded-full bg-neutral-100 px-3 py-1 text-sm text-neutral-700 transition-colors hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                                    >
                                        {option.label}
                                    </button>
                                ))}
                            </div>
                        )}

                        {group.options.length === 0 && (
                            <p className="text-sm text-neutral-400 dark:text-neutral-500">
                                No tags available
                            </p>
                        )}
                    </div>
                );
            })}

            {allOptions.length === 0 && (
                <p className="text-sm text-neutral-400 dark:text-neutral-500">
                    No tags available
                </p>
            )}

            {error !== undefined && (
                <p className="text-sm text-red-500 dark:text-red-400">
                    {error}
                </p>
            )}
        </div>
    );
}
