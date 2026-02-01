import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ExternalLink } from 'lucide-react';

interface RedirectModalProps {
    isOpen: boolean;
    onClose: () => void;
    url: string;
}

export function RedirectModal({ isOpen, onClose, url }: RedirectModalProps) {
    const handleContinue = () => {
        if (typeof window === 'undefined') return;
        window.open(url, '_blank', 'noopener');
        onClose();
    };

    // Extract domain from URL for display
    const getDomain = (urlString: string): string => {
        try {
            const urlObj = new URL(urlString);
            return urlObj.hostname;
        } catch {
            return urlString;
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <div className="mb-3 flex justify-center">
                        <div className="rounded-full border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900">
                            <ExternalLink className="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <DialogTitle className="text-center">
                        Leaving vibecode.law
                    </DialogTitle>
                    <DialogDescription className="text-center">
                        You're about to visit an external website. This will
                        open in a new tab.
                    </DialogDescription>
                </DialogHeader>

                <div className="my-4 space-y-3">
                    <div className="rounded-lg border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-neutral-800">
                        <p className="text-sm break-all text-neutral-600 dark:text-neutral-400">
                            {getDomain(url)}
                        </p>
                    </div>

                    <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-950">
                        <p className="text-xs leading-relaxed text-amber-900 dark:text-amber-200">
                            <strong className="font-semibold">
                                Important:
                            </strong>{' '}
                            vibecode.law is not responsible for external
                            websites. Projects showcased here are not endorsed
                            by vibecode.law. Please conduct your own due
                            diligence before using any application and avoid
                            sharing sensitive information.
                        </p>
                    </div>
                </div>

                <DialogFooter className="flex-col gap-2 sm:flex-row">
                    <Button
                        variant="outline"
                        onClick={onClose}
                        className="w-full sm:w-auto"
                    >
                        Cancel
                    </Button>
                    <Button
                        onClick={handleContinue}
                        className="w-full gap-2 sm:w-auto"
                    >
                        <ExternalLink className="size-4" />
                        Continue to Website
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
