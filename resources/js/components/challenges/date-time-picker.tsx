import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { format } from 'date-fns';
import { CalendarIcon, X } from 'lucide-react';
import { useState } from 'react';

interface DateTimePickerProps {
    name: string;
    value: string;
    onChange: (value: string) => void;
    disabled?: boolean;
    error?: boolean;
    /** Time applied automatically once a date is chosen but no time is. */
    defaultTime?: 'start' | 'end';
}

interface DateTimeParts {
    year: string;
    month: string;
    day: string;
    hour: string;
    minute: string;
}

const EMPTY_PARTS: DateTimeParts = {
    year: '',
    month: '',
    day: '',
    hour: '',
    minute: '',
};

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

function parseParts(value: string): DateTimeParts {
    const match = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(value);

    if (match === null) {
        return { ...EMPTY_PARTS };
    }

    return {
        year: match[1],
        month: match[2],
        day: match[3],
        hour: match[4],
        minute: match[5],
    };
}

function partsToDate(parts: DateTimeParts): Date | undefined {
    if (parts.year === '' || parts.month === '' || parts.day === '') {
        return undefined;
    }

    return new Date(
        Number(parts.year),
        Number(parts.month) - 1,
        Number(parts.day),
    );
}

function range(start: number, end: number): string[] {
    const values: string[] = [];

    for (let value = start; value <= end; value++) {
        values.push(pad(value));
    }

    return values;
}

export function DateTimePicker({
    name,
    value,
    onChange,
    disabled = false,
    error = false,
    defaultTime = 'start',
}: DateTimePickerProps) {
    const [open, setOpen] = useState(false);
    const [parts, setParts] = useState(() => parseParts(value));

    const commit = (next: DateTimeParts) => {
        const hasDate =
            next.year !== '' && next.month !== '' && next.day !== '';

        // Once a date is picked, default the time so the field is usable
        // without forcing the admin to set hours/minutes by hand.
        if (hasDate === true && next.hour === '' && next.minute === '') {
            next = {
                ...next,
                hour: defaultTime === 'end' ? '23' : '00',
                minute: defaultTime === 'end' ? '59' : '00',
            };
        }

        setParts(next);

        onChange(
            hasDate === true
                ? `${next.year}-${next.month}-${next.day}T${next.hour === '' ? '00' : next.hour}:${next.minute === '' ? '00' : next.minute}`
                : '',
        );
    };

    const handleDateSelect = (date: Date | undefined) => {
        if (date === undefined) {
            return;
        }

        commit({
            ...parts,
            year: String(date.getFullYear()),
            month: pad(date.getMonth() + 1),
            day: pad(date.getDate()),
        });
    };

    const clear = () => {
        setParts({ ...EMPTY_PARTS });
        onChange('');
        setOpen(false);
    };

    const selectedDate = partsToDate(parts);
    const hasValue = value !== '';
    const label =
        selectedDate !== undefined
            ? `${format(selectedDate, 'd MMM yyyy')} at ${parts.hour === '' ? '00' : parts.hour}:${parts.minute === '' ? '00' : parts.minute}`
            : 'Select date & time';

    return (
        <>
            <input type="hidden" name={name} value={value} />

            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={disabled}
                        aria-invalid={error === true ? true : undefined}
                        className={cn(
                            'w-full justify-start text-left font-normal',
                            hasValue === false && 'text-muted-foreground',
                        )}
                    >
                        <CalendarIcon className="mr-2 size-4" />
                        {label}
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        mode="single"
                        selected={selectedDate}
                        onSelect={handleDateSelect}
                        captionLayout="dropdown"
                        autoFocus
                    />
                    <div className="flex items-center gap-2 border-t p-3">
                        <span className="text-sm text-muted-foreground">
                            Time
                        </span>
                        <Select
                            value={parts.hour === '' ? undefined : parts.hour}
                            onValueChange={(hour) => commit({ ...parts, hour })}
                            disabled={selectedDate === undefined}
                        >
                            <SelectTrigger className="w-20">
                                <SelectValue placeholder="HH" />
                            </SelectTrigger>
                            <SelectContent>
                                {range(0, 23).map((hour) => (
                                    <SelectItem key={hour} value={hour}>
                                        {hour}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <span className="text-muted-foreground">:</span>
                        <Select
                            value={
                                parts.minute === '' ? undefined : parts.minute
                            }
                            onValueChange={(minute) =>
                                commit({ ...parts, minute })
                            }
                            disabled={selectedDate === undefined}
                        >
                            <SelectTrigger className="w-20">
                                <SelectValue placeholder="MM" />
                            </SelectTrigger>
                            <SelectContent>
                                {range(0, 59).map((minute) => (
                                    <SelectItem key={minute} value={minute}>
                                        {minute}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {hasValue === true && (
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={clear}
                                className="ml-auto"
                            >
                                <X className="mr-1 size-4" />
                                Clear
                            </Button>
                        )}
                    </div>
                </PopoverContent>
            </Popover>
        </>
    );
}
