import { RichTextContent } from '@/components/showcase/rich-text-content';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface ParticipantInstructionsDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    instructionsHtml: string;
}

export function ParticipantInstructionsDialog({
    open,
    onOpenChange,
    instructionsHtml,
}: ParticipantInstructionsDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Participant instructions</DialogTitle>
                    <DialogDescription>
                        Everything you need to know to take part in this
                        challenge.
                    </DialogDescription>
                </DialogHeader>

                <RichTextContent
                    html={instructionsHtml}
                    className="rich-text-content"
                />
            </DialogContent>
        </Dialog>
    );
}
