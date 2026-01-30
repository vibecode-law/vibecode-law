import { cn } from '@/lib/utils';
import * as SelectPrimitive from '@radix-ui/react-select';
import { CheckIcon, ChevronDownIcon, ChevronUpIcon } from 'lucide-react';
import { useId, useState } from 'react';

interface SelectOption {
    value: string;
    label: string;
}

interface FancySelectProps {
    name: string;
    value?: string;
    onValueChange?: (value: string) => void;
    options: SelectOption[];
    placeholder?: string;
    label?: string;
    labelIcon?: React.ReactNode;
    description?: string;
    error?: string;
    required?: boolean;
    className?: string;
}

export function FancySelect({
    name,
    value,
    onValueChange,
    options,
    placeholder = 'Select an option...',
    label,
    labelIcon,
    description,
    error,
    required = false,
    className,
}: FancySelectProps) {
    const [isOpen, setIsOpen] = useState(false);
    const generatedId = useId();

    const hasValue = value !== undefined && value !== '';
    const showPlaceholder = isOpen === false && hasValue === false;

    const selectedOption = options.find((opt) => opt.value === value);

    return (
        <div className={cn('space-y-2', className)}>
            {label !== undefined && (
                <label
                    id={`${generatedId}-label`}
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
                        onClick={() => setIsOpen(true)}
                        onFocus={() => setIsOpen(true)}
                        className="w-full rounded-lg border border-dashed border-neutral-300 bg-neutral-50 p-4 text-left text-neutral-400 transition-colors hover:border-amber-400 hover:bg-amber-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-500 dark:hover:border-amber-500 dark:hover:bg-amber-950/20"
                    >
                        {placeholder}
                    </button>
                ) : (
                    <SelectPrimitive.Root
                        name={name}
                        value={value}
                        onValueChange={onValueChange}
                        open={isOpen}
                        onOpenChange={setIsOpen}
                    >
                        <SelectPrimitive.Trigger
                            aria-labelledby={
                                label !== undefined
                                    ? `${generatedId}-label`
                                    : undefined
                            }
                            className={cn(
                                'flex w-full items-center justify-between rounded-lg border bg-white p-4 text-neutral-900 outline-none transition-all',
                                'border-neutral-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white',
                                'focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 dark:focus:border-amber-500 dark:focus:ring-amber-500/20',
                                'data-[placeholder]:text-neutral-400 dark:data-[placeholder]:text-neutral-500',
                            )}
                        >
                            <SelectPrimitive.Value placeholder={placeholder}>
                                {selectedOption?.label}
                            </SelectPrimitive.Value>
                            <SelectPrimitive.Icon>
                                <ChevronDownIcon className="size-5 text-neutral-400" />
                            </SelectPrimitive.Icon>
                        </SelectPrimitive.Trigger>
                        <SelectPrimitive.Portal>
                            <SelectPrimitive.Content
                                className={cn(
                                    'relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-lg border bg-white shadow-lg dark:border-neutral-700 dark:bg-neutral-900',
                                    'data-[state=open]:animate-in data-[state=closed]:animate-out',
                                    'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
                                    'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
                                    'data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2',
                                    'data-[side=bottom]:translate-y-1 data-[side=top]:-translate-y-1',
                                )}
                                position="popper"
                            >
                                <SelectPrimitive.ScrollUpButton className="flex cursor-default items-center justify-center py-1">
                                    <ChevronUpIcon className="size-4" />
                                </SelectPrimitive.ScrollUpButton>
                                <SelectPrimitive.Viewport className="h-[var(--radix-select-trigger-height)] w-full min-w-[var(--radix-select-trigger-width)] scroll-my-1 p-1">
                                    {options.map((option) => (
                                        <SelectPrimitive.Item
                                            key={option.value}
                                            value={option.value}
                                            className={cn(
                                                'relative flex w-full cursor-default select-none items-center rounded-md py-2 pr-8 pl-3 text-sm outline-none',
                                                'text-neutral-900 dark:text-white',
                                                'focus:bg-amber-50 focus:text-amber-900 dark:focus:bg-amber-950/30 dark:focus:text-amber-100',
                                                'data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
                                            )}
                                        >
                                            <span className="absolute right-2 flex size-3.5 items-center justify-center">
                                                <SelectPrimitive.ItemIndicator>
                                                    <CheckIcon className="size-4 text-amber-500" />
                                                </SelectPrimitive.ItemIndicator>
                                            </span>
                                            <SelectPrimitive.ItemText>
                                                {option.label}
                                            </SelectPrimitive.ItemText>
                                        </SelectPrimitive.Item>
                                    ))}
                                </SelectPrimitive.Viewport>
                                <SelectPrimitive.ScrollDownButton className="flex cursor-default items-center justify-center py-1">
                                    <ChevronDownIcon className="size-4" />
                                </SelectPrimitive.ScrollDownButton>
                            </SelectPrimitive.Content>
                        </SelectPrimitive.Portal>
                    </SelectPrimitive.Root>
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
}
