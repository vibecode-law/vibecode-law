import { RedirectModal } from '@/components/showcase/redirect-modal';
import { useExternalLinkModal } from '@/hooks/use-external-link-modal';
import { useRef } from 'react';

interface RichTextContentProps {
    html: string;
    className?: string;
}

export function RichTextContent({ html, className }: RichTextContentProps) {
    const contentRef = useRef<HTMLDivElement>(null);
    const { isOpen, url, handleClose } = useExternalLinkModal(contentRef);

    return (
        <>
            <div
                ref={contentRef}
                className={className}
                dangerouslySetInnerHTML={{ __html: html }}
            />

            {url !== '' && (
                <RedirectModal
                    isOpen={isOpen}
                    onClose={handleClose}
                    url={url}
                />
            )}
        </>
    );
}
