import { useEffect, useState } from 'react';

interface UseExternalLinkModalReturn {
    isOpen: boolean;
    url: string;
    handleClose: () => void;
}

export function useExternalLinkModal(
    contentRef: React.RefObject<HTMLElement | null>,
): UseExternalLinkModalReturn {
    const [isOpen, setIsOpen] = useState(false);
    const [url, setUrl] = useState('');

    useEffect(() => {
        const element = contentRef.current;
        if (element === null) return;

        const handleClick = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            const link = target.closest('a');

            if (link === null) return;

            const href = link.getAttribute('href');
            if (href === null || href === '') return;

            // Check if it's an external link
            const isExternal =
                href.startsWith('http://') ||
                href.startsWith('https://') ||
                href.startsWith('//');

            if (isExternal === true) {
                e.preventDefault();
                setUrl(href);
                setIsOpen(true);
            }
        };

        element.addEventListener('click', handleClick);

        return () => {
            element.removeEventListener('click', handleClick);
        };
    }, [contentRef]);

    const handleClose = () => {
        setIsOpen(false);
        setUrl('');
    };

    return { isOpen, url, handleClose };
}
