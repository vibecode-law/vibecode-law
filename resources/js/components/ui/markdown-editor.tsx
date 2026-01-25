import { cn } from '@/lib/utils';
import MDEditor, { commands } from '@uiw/react-md-editor';
import { type FocusEvent } from 'react';
import { useId, useState } from 'react';

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
}: MarkdownEditorProps) {
    const id = useId();
    const [internalValue, setInternalValue] = useState(defaultValue ?? '');

    const isControlled = controlledValue !== undefined;
    const value = isControlled ? controlledValue : internalValue;

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
                commands={[
                    commands.bold,
                    commands.italic,
                    commands.divider,
                    commands.unorderedListCommand,
                    commands.orderedListCommand,
                ]}
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
