import { Input } from '@/components/ui/input';
import {
    formatTimezoneLabel,
    getTimezoneOffsetMinutes,
} from '@/lib/challenge-utils';
import { cn } from '@/lib/utils';
import { ChevronsUpDown, Search, X } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

interface TimezoneSearchSelectProps {
    name: string;
    value: string;
    onValueChange: (value: string) => void;
    disabled?: boolean;
    error?: string;
}

// Shown by default to keep the list short; searching falls back to the full
// IANA list so any zone is still reachable.
const COMMON_TIMEZONES = [
    'UTC',
    'Europe/London',
    'Europe/Dublin',
    'Europe/Paris',
    'Europe/Berlin',
    'Europe/Madrid',
    'Europe/Rome',
    'Europe/Amsterdam',
    'Europe/Athens',
    'America/New_York',
    'America/Chicago',
    'America/Denver',
    'America/Los_Angeles',
    'America/Toronto',
    'America/Sao_Paulo',
    'Asia/Dubai',
    'Asia/Kolkata',
    'Asia/Singapore',
    'Asia/Hong_Kong',
    'Asia/Tokyo',
    'Asia/Shanghai',
    'Australia/Sydney',
    'Pacific/Auckland',
];

function getTimezones(): string[] {
    const intl = Intl as typeof Intl & {
        supportedValuesOf?: (key: string) => string[];
    };

    return intl.supportedValuesOf?.('timeZone') ?? ['UTC'];
}

function byOffsetThenName(a: string, b: string): number {
    return (
        getTimezoneOffsetMinutes(a) - getTimezoneOffsetMinutes(b) ||
        formatTimezoneLabel(a).localeCompare(formatTimezoneLabel(b))
    );
}

export function TimezoneSearchSelect({
    name,
    value,
    onValueChange,
    disabled = false,
    error,
}: TimezoneSearchSelectProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [search, setSearch] = useState('');
    const containerRef = useRef<HTMLDivElement>(null);

    const timezones = useMemo(() => getTimezones(), []);

    const results = useMemo(() => {
        const query = search.trim().toLowerCase();

        if (query === '') {
            return [...COMMON_TIMEZONES].sort(byOffsetThenName);
        }

        return timezones
            .filter(
                (zone) =>
                    zone.toLowerCase().includes(query) ||
                    formatTimezoneLabel(zone).toLowerCase().includes(query),
            )
            .sort(byOffsetThenName)
            .slice(0, 100);
    }, [search, timezones]);

    const close = () => {
        setIsOpen(false);
        setSearch('');
    };

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                close();
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () =>
            document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = (zone: string) => {
        onValueChange(zone);
        close();
    };

    const handleClear = () => {
        onValueChange('');
        close();
    };

    return (
        <div ref={containerRef} className="relative">
            <input type="hidden" name={name} value={value} />

            <button
                type="button"
                onClick={() => (isOpen ? close() : setIsOpen(true))}
                disabled={disabled}
                aria-invalid={error !== undefined ? true : undefined}
                className={cn(
                    'flex w-full items-center justify-between rounded-md border border-neutral-300 bg-transparent py-2 pr-16 pl-3 text-sm dark:border-neutral-700',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    error !== undefined && 'border-red-500 dark:border-red-400',
                )}
            >
                <span className={value === '' ? 'text-neutral-400' : undefined}>
                    {value === ''
                        ? 'Select a timezone...'
                        : formatTimezoneLabel(value)}
                </span>
            </button>

            <div className="pointer-events-none absolute inset-y-0 right-2 flex items-center gap-1">
                {value !== '' && disabled === false && (
                    <button
                        type="button"
                        onClick={handleClear}
                        aria-label="Clear timezone"
                        className="pointer-events-auto rounded p-0.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200"
                    >
                        <X className="size-4" />
                    </button>
                )}
                <ChevronsUpDown className="size-4 text-neutral-400" />
            </div>

            {isOpen && (
                <div className="absolute z-50 mt-1 w-full rounded-md border border-neutral-200 bg-white shadow-lg dark:border-neutral-800 dark:bg-neutral-950">
                    <div className="relative border-b border-neutral-100 p-2 dark:border-neutral-800">
                        <Search className="absolute top-1/2 left-4 size-4 -translate-y-1/2 text-neutral-400" />
                        <Input
                            type="text"
                            autoFocus
                            placeholder="Search all timezones..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="pl-9"
                        />
                    </div>
                    {results.length === 0 ? (
                        <div className="p-4 text-center text-sm text-neutral-500">
                            No timezones found
                        </div>
                    ) : (
                        <div className="max-h-60 overflow-y-auto">
                            {results.map((zone) => (
                                <button
                                    key={zone}
                                    type="button"
                                    onClick={() => handleSelect(zone)}
                                    className={cn(
                                        'w-full border-b border-neutral-100 px-3 py-2 text-left text-sm transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900',
                                        zone === value &&
                                            'bg-neutral-50 font-medium dark:bg-neutral-900',
                                    )}
                                >
                                    {formatTimezoneLabel(zone)}
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
