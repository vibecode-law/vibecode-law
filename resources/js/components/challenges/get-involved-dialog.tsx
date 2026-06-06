import { RichTextContent } from '@/components/showcase/rich-text-content';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { login } from '@/routes';
import { Link } from '@inertiajs/react';

interface GetInvolvedDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    instructionsHtml: string | null;
    isAuthenticated: boolean;
}

export function GetInvolvedDialog({
    open,
    onOpenChange,
    instructionsHtml,
    isAuthenticated,
}: GetInvolvedDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>How to Get Involved</DialogTitle>
                    <DialogDescription>
                        {isAuthenticated === false
                            ? "Here’s how to take part. If you've already received an invite, login to submit."
                            : 'Here’s how to take part in this challenge.'}
                    </DialogDescription>
                </DialogHeader>

                {instructionsHtml && (
                    <RichTextContent
                        html={instructionsHtml}
                        className="rich-text-content"
                    />
                )}

                {isAuthenticated === false && (
                    <DialogFooter>
                        <Button asChild>
                            <Link href={login()}>Log in</Link>
                        </Button>
                    </DialogFooter>
                )}
            </DialogContent>
        </Dialog>
    );
}
