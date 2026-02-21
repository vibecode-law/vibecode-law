import { cn } from '@/lib/utils';
import MDEditor, { type ICommand, commands } from '@uiw/react-md-editor';
import { type FocusEvent } from 'react';
import { useId, useMemo, useState } from 'react';

const basicCommands: ICommand[] = [
    commands.bold,
    commands.italic,
    commands.divider,
    commands.unorderedListCommand,
    commands.orderedListCommand,
    commands.divider,
    commands.link,
];

const fullCommands: ICommand[] = [
    commands.bold,
    commands.italic,
    commands.strikethrough,
    commands.divider,
    commands.heading1,
    commands.heading2,
    commands.heading3,
    commands.heading4,
    commands.divider,
    commands.unorderedListCommand,
    commands.orderedListCommand,
    commands.divider,
    commands.link,
    commands.quote,
    commands.code,
    commands.codeBlock,
    commands.divider,
    commands.table,
    commands.hr,
];

function buildToolbar(
    profile: 'basic' | 'full',
    exclude: string[],
): ICommand[] {
    const source = profile === 'full' ? fullCommands : basicCommands;

    if (exclude.length === 0) {
        return source;
    }

    const filtered = source.filter(
        (cmd) => cmd.name === undefined || exclude.includes(cmd.name) === false,
    );

    // Remove orphaned dividers (leading, trailing, consecutive)
    return filtered.filter((cmd, i, arr) => {
        if (cmd.name !== 'divider') {
            return true;
        }
        if (i === 0 || i === arr.length - 1) {
            return false;
        }
        return arr[i - 1].name !== 'divider';
    });
}

interface MarkdownEditorProps {
    name: string;
    defaultValue?: string | null;
    value?: string;
    onChange?: (value: string) => void;
    placeholder?: string;
    className?: string;
    height?: number;
    autoFocus?: boolean;
    onFocus?: () => void;
    onBlur?: (event: FocusEvent<HTMLTextAreaElement>) => void;
    profile?: 'basic' | 'full';
    exclude?: string[];
}

export function MarkdownEditor({
    name,
    defaultValue,
    value: controlledValue,
    onChange,
    placeholder,
    className,
    height = 200,
    autoFocus = false,
    onFocus,
    onBlur,
    profile = 'basic',
    exclude = [],
}: MarkdownEditorProps) {
    const id = useId();
    const [internalValue, setInternalValue] = useState(defaultValue ?? '');

    const isControlled = controlledValue !== undefined;
    const value = isControlled ? controlledValue : internalValue;

    const toolbar = useMemo(
        () => buildToolbar(profile, exclude),
        [profile, exclude],
    );

    const handleChange = (val: string | undefined) => {
        if (val === undefined) {
            return;
        }
        if (onChange !== undefined) {
            onChange(val);
        }
        if (isControlled === false) {
            setInternalValue(val);
        }
    };

    return (
        <div className={cn('w-full', className)} data-color-mode="light">
            <input type="hidden" name={name} value={value} />
            <MDEditor
                id={id}
                value={value}
                onChange={handleChange}
                preview="edit"
                height={height}
                commands={toolbar}
                extraCommands={[commands.codeEdit, commands.codeLive, commands.codePreview]}
                textareaProps={{
                    placeholder: placeholder,
                    autoFocus: autoFocus,
                    onFocus: onFocus,
                    onBlur: onBlur,
                }}
                className="rounded-md! border-input! border! bg-transparent! shadow-xs! dark:bg-transparent! dark:text-neutral-400!"
            />
        </div>
    );
}
