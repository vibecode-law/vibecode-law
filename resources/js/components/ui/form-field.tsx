import { type ReactNode } from 'react';

import InputError from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export interface FormFieldProps {
    label: string;
    htmlFor: string;
    error?: string;
    optional?: boolean;
    required?: boolean;
    children: ReactNode;
    className?: string;
    labelClassName?: string;
    /**
     * Additional content to render in the label row (e.g., "Forgot password?" link)
     */
    labelSuffix?: ReactNode;
}

export function FormField({
    label,
    htmlFor,
    error,
    optional = false,
    required = false,
    children,
    className,
    labelClassName,
    labelSuffix,
}: FormFieldProps) {
    const hasLabelSuffix = labelSuffix !== undefined;

    return (
        <div className={cn('grid gap-2', className)}>
            {hasLabelSuffix ? (
                <div className="flex items-center">
                    <Label htmlFor={htmlFor} className={labelClassName}>
                        {label}
                        {required === true && (
                            <span className="text-destructive"> *</span>
                        )}
                        {optional === true && (
                            <span className="text-muted-foreground">
                                {' '}
                                (optional)
                            </span>
                        )}
                    </Label>
                    {labelSuffix}
                </div>
            ) : (
                <Label htmlFor={htmlFor} className={labelClassName}>
                    {label}
                    {required === true && (
                        <span className="text-destructive"> *</span>
                    )}
                    {optional === true && (
                        <span className="text-muted-foreground"> (optional)</span>
                    )}
                </Label>
            )}
            {children}
            <InputError message={error} />
        </div>
    );
}
